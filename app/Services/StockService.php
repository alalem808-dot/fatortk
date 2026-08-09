<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;

class StockService
{
    public function move(Product $product, string $type, int $quantity, string $refType = null, int $refId = null, string $notes = null): StockMovement
    {
        $before = $product->stock_quantity;
        $after  = $type === 'in' ? $before + $quantity : $before - $quantity;

        if ($type === 'adjustment') {
            $after = $quantity;
        }

        $product->update(['stock_quantity' => $after]);

        return StockMovement::create([
            'tenant_id'      => $product->tenant_id,
            'product_id'     => $product->id,
            'type'           => $type,
            'quantity'       => abs($quantity),
            'quantity_before'=> $before,
            'quantity_after' => $after,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'notes'          => $notes,
            'created_by'     => auth()->id(),
        ]);
    }

    public function deductForInvoice(Invoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            if ($item->product_id) {
                $this->move($item->product, 'out', $item->quantity, 'invoice', $invoice->id);
            }
        }
    }
}
