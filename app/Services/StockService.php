<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * تحريك المخزون — يدعم المخازن المتعددة مع الحفاظ على stock_quantity الكلي
     *
     * @param bool|null $allowNegative تمريرها مباشرة لتجنب N+1 عند الاستدعاء في حلقة
     * @throws \RuntimeException إذا أدى الخصم لكمية سالبة ولم يُسمح بالسالب
     */
    public function move(
        Product $product,
        string $type,
        float $quantity,
        ?string $refType = null,
        ?int $refId = null,
        ?string $notes = null,
        ?int $warehouseId = null,
        ?bool $allowNegative = null
    ): StockMovement {
        // PERF-02 Fix: نُجري lockForUpdate فقط على المنتج بدون eager-load tenant.settings
        // قيمة allowNegative تُمرَّر من الخارج عند الاستدعاء في حلقة لتجنب N+1
        $product = Product::lockForUpdate()->find($product->id);

        $before = (float) $product->stock_quantity;
        $after  = $before;

        // نقرأ allowNegative من DB فقط إذا لم تُمرَّر
        if ($allowNegative === null) {
            $allowNegative = $product->tenant_id
                ? filter_var(
                    \App\Models\Setting::where('tenant_id', $product->tenant_id)
                        ->where('key', 'allow_negative_stock')
                        ->value('value') ?? '0',
                    FILTER_VALIDATE_BOOLEAN
                )
                : false;
        }

        if ($warehouseId) {
            // ===== مع مخزن محدد =====
            $this->updateWarehouseStock($warehouseId, $product->id, $type, $quantity);

            // stock_quantity الكلي = مجموع كل المخازن
            $after = (float) WarehouseStock::where('product_id', $product->id)->sum('quantity');

            // التحقق من السالب عند الخصم
            if ($type === 'out' && $after < -0.001) {
                if (!$allowNegative) {
                    // تراجع عن تحديث warehouse_stock
                    $this->updateWarehouseStock($warehouseId, $product->id, 'in', $quantity);
                    throw new \RuntimeException(
                        "الكمية المطلوبة ({$quantity}) أكبر من المتاح ({$before}) للمنتج: {$product->name}"
                    );
                }

                Log::warning('stock_negative', [
                    'product_id' => $product->id,
                    'before'     => $before,
                    'requested'  => $quantity,
                    'after'      => $after,
                ]);
            }
        } else {
            // ===== بدون مخزن محدد =====
            if ($type === 'adjustment') {
                $after = $quantity;
            } elseif ($type === 'in') {
                $after = $before + $quantity;
            } else {
                $after = $before - $quantity;
                if ($after < -0.001 && !$allowNegative) {
                    throw new \RuntimeException(
                        "الكمية المطلوبة ({$quantity}) أكبر من المتاح ({$before}) للمنتج: {$product->name}"
                    );
                }
            }
        }

        $product->update(['stock_quantity' => $after]);

        return StockMovement::create([
            'tenant_id'       => $product->tenant_id,
            'product_id'      => $product->id,
            'warehouse_id'    => $warehouseId,
            'type'            => $type,
            'quantity'        => abs($quantity),
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'reference_type'  => $refType,
            'reference_id'    => $refId,
            'notes'           => $notes,
            'created_by'      => auth()->id(),
        ]);
    }

    /**
     * خصم المخزون عند إصدار فاتورة
     */
    public function deductForInvoice(Invoice $invoice, ?int $warehouseId = null): void
    {
        if (!$warehouseId) {
            $warehouse   = Warehouse::getDefault($invoice->tenant_id);
            $warehouseId = $warehouse?->id;
        }

        if (!$invoice->relationLoaded('items') || !$invoice->items->first()?->relationLoaded('product')) {
            $invoice->load('items.product');
        }

        // PERF-02 Fix: نقرأ allowNegative مرة واحدة قبل الحلقة
        $allowNegative = filter_var(
            \App\Models\Setting::where('tenant_id', $invoice->tenant_id)
                ->where('key', 'allow_negative_stock')
                ->value('value') ?? '0',
            FILTER_VALIDATE_BOOLEAN
        );

        foreach ($invoice->items as $item) {
            if ($item->product_id && $item->product) {
                $this->move(
                    $item->product,
                    'out',
                    (float) $item->quantity,
                    'invoice',
                    $invoice->id,
                    null,
                    $warehouseId,
                    $allowNegative
                );
            }
        }
    }

    /**
     * إعادة المخزون عند مرتجع فاتورة
     */
    public function restoreForReturn(Invoice $invoice, array $itemsData, int $returnId, ?int $warehouseId = null): void
    {
        if (!$warehouseId) {
            $warehouse   = Warehouse::getDefault($invoice->tenant_id);
            $warehouseId = $warehouse?->id;
        }

        foreach ($itemsData as $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $this->move(
                        $product,
                        'in',
                        (float) $item['quantity'],
                        'return',
                        $returnId,
                        'مرتجع فاتورة: ' . $invoice->invoice_number,
                        $warehouseId
                    );
                }
            }
        }
    }

    /**
     * نقل مخزون بين مخزنين — لا يغير stock_quantity الكلي
     */
    public function transfer(
        Product $product,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($product, $fromWarehouseId, $toWarehouseId, $quantity, $notes) {
            $currentQty = (float) $product->stock_quantity;

            // التحقق من الكمية المتاحة في المخزن المصدر
            $available = (float) WarehouseStock::where('warehouse_id', $fromWarehouseId)
                ->where('product_id', $product->id)
                ->value('quantity') ?? 0;

            $allowNegative = $product->tenant
                ? filter_var($product->tenant->getSetting('allow_negative_stock', '0'), FILTER_VALIDATE_BOOLEAN)
                : false;

            if ($quantity > $available + 0.001 && !$allowNegative) {
                throw new \RuntimeException(
                    "الكمية المطلوبة ({$quantity}) أكبر من المتاح ({$available}) في المخزن المصدر للمنتج: {$product->name}"
                );
            }

            $this->updateWarehouseStock($fromWarehouseId, $product->id, 'out', $quantity);
            $this->updateWarehouseStock($toWarehouseId, $product->id, 'in', $quantity);
            // stock_quantity الكلي لا يتغير عند النقل

            StockMovement::create([
                'tenant_id'       => $product->tenant_id,
                'product_id'      => $product->id,
                'warehouse_id'    => $fromWarehouseId,
                'type'            => 'out',
                'quantity'        => $quantity,
                'quantity_before' => $currentQty,
                'quantity_after'  => $currentQty,
                'reference_type'  => 'transfer',
                'reference_id'    => $toWarehouseId,
                'notes'           => $notes ?? ('نقل مخزون إلى مخزن #' . $toWarehouseId),
                'created_by'      => auth()->id(),
            ]);

            StockMovement::create([
                'tenant_id'       => $product->tenant_id,
                'product_id'      => $product->id,
                'warehouse_id'    => $toWarehouseId,
                'type'            => 'in',
                'quantity'        => $quantity,
                'quantity_before' => $currentQty,
                'quantity_after'  => $currentQty,
                'reference_type'  => 'transfer',
                'reference_id'    => $fromWarehouseId,
                'notes'           => $notes ?? ('نقل مخزون من مخزن #' . $fromWarehouseId),
                'created_by'      => auth()->id(),
            ]);
        });
    }

    /**
     * تحديث مخزون مخزن محدد
     */
    private function updateWarehouseStock(int $warehouseId, int $productId, string $type, float $quantity): void
    {
        $stock = WarehouseStock::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['quantity' => 0]
        );

        if ($type === 'adjustment') {
            $stock->update(['quantity' => $quantity]);
        } elseif ($type === 'in') {
            $stock->increment('quantity', $quantity);
        } else {
            $stock->decrement('quantity', $quantity);
        }
    }
}
