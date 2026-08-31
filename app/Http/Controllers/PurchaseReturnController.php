<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $returns = PurchaseReturn::where('tenant_id', auth()->user()->tenant_id)
            ->with('purchase')
            ->latest()
            ->paginate(15);

        return view('purchase-returns.index', compact('returns'));
    }

    public function create(Purchase $purchase)
    {
        abort_if($purchase->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($purchase->status !== 'received', 403, 'يمكن إرجاع المشتريات المستلمة فقط.');

        $purchase->load('items.product');

        // المخازن المتاحة للمستخدم (محقونة من TenantMiddleware)
        $warehouses       = view()->shared('userWarehouses') ?? collect();
        $defaultWarehouse = $purchase->warehouse
            ?? auth()->user()->getDefaultWarehouse();

        return view('purchase-returns.create', compact('purchase', 'warehouses', 'defaultWarehouse'));
    }

    public function store(Request $request, Purchase $purchase)
    {
        abort_if($purchase->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($purchase->status !== 'received', 403);

        $request->validate([
            'return_date'              => 'required|date',
            'reason'                   => 'nullable|string|max:500',
            'warehouse_id'             => 'nullable|exists:warehouses,id',
            'items'                    => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.quantity'         => 'required|numeric|min:0.001',
        ]);

        $total     = 0;
        $itemsData = [];

        foreach ($request->items as $row) {
            if (empty($row['quantity']) || (float) $row['quantity'] <= 0) continue;

            $purchaseItem = $purchase->items()->find($row['purchase_item_id']);
            if (!$purchaseItem) continue;

            // الكمية المُرجعة مسبقاً لهذا البند
            $alreadyReturned = PurchaseReturnItem::whereHas(
                'purchaseReturn',
                fn($q) => $q->where('purchase_id', $purchase->id)
            )
                ->where('purchase_item_id', $purchaseItem->id)
                ->sum('quantity');

            $maxQty = (float) $purchaseItem->quantity - (float) $alreadyReturned;
            if ($maxQty <= 0.001) continue;

            $qty    = min((float) $row['quantity'], $maxQty);
            $total += $qty * (float) $purchaseItem->unit_cost;

            $itemsData[] = [
                'purchase_item_id' => $purchaseItem->id,
                'product_id'       => $purchaseItem->product_id,
                'product_name'     => $purchaseItem->product_name,
                'quantity'         => $qty,
                'unit_cost'        => (float) $purchaseItem->unit_cost,
                'total'            => round($qty * (float) $purchaseItem->unit_cost, 2),
            ];
        }

        if (empty($itemsData)) {
            return back()->withErrors(['items' => 'يجب اختيار صنف واحد على الأقل بكمية صالحة.']);
        }

        DB::transaction(function () use ($request, $purchase, $itemsData, $total) {
            $return = PurchaseReturn::create([
                'tenant_id'   => $purchase->tenant_id,
                'purchase_id' => $purchase->id,
                'reference'   => 'PR-' . date('YmdHis'),
                'return_date' => $request->return_date,
                'reason'      => $request->reason,
                'total'       => round($total, 2),
                'created_by'  => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $return->items()->create($item);
            }

            // خصم المخزون من المخزن المحدد
            // الأولوية: المخزن المختار يدوياً ← مخزن أمر الشراء الأصلي ← المخزن الافتراضي
            $warehouseId = $request->warehouse_id
                ?? $purchase->warehouse_id
                ?? Warehouse::getDefault($purchase->tenant_id)?->id;

            foreach ($itemsData as $item) {
                if ($item['product_id']) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $this->stockService->move(
                            $product,
                            'out',
                            $item['quantity'],
                            'purchase_return',
                            $return->id,
                            'مرتجع مشتريات: ' . $purchase->reference,
                            $warehouseId
                        );
                    }
                }
            }

            // BUG-04 Fix: لا نُعدّل total/subtotal الأصلي لأمر الشراء أبداً
            // بدلاً من ذلك نُضيف قيمة المرتجع على returned_amount
            // الصافي = total - returned_amount ويُحسب عبر $purchase->net_total
            $purchase->refresh();
            $purchase->increment('returned_amount', round($total, 2));

            // إعادة حساب payment_status بناءً على الصافي الجديد
            $netTotal   = $purchase->fresh()->net_total;
            $paidAmount = (float) $purchase->paid_amount;

            if ($netTotal <= 0.001) {
                $newPaymentStatus = 'paid';
            } elseif ($paidAmount >= $netTotal - 0.001) {
                $newPaymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $newPaymentStatus = 'partial';
            } else {
                $newPaymentStatus = 'unpaid';
            }

            $purchase->update(['payment_status' => $newPaymentStatus]);
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'تم تسجيل مرتجع المشتريات وتحديث إجمالي أمر الشراء.');
    }
}
