<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceReturn;
use App\Models\InvoiceReturnItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceReturnController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $returns = InvoiceReturn::where('tenant_id', auth()->user()->tenant_id)
            ->with('invoice.customer')
            ->latest()
            ->paginate(15);

        return view('returns.index', compact('returns'));
    }

    public function create(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if(
            !in_array($invoice->status, ['sent', 'paid', 'partially_paid']),
            403,
            'لا يمكن إرجاع هذه الفاتورة.'
        );

        $invoice->load('items.product');
        return view('returns.create', compact('invoice'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== auth()->user()->tenant_id, 403);

        $request->validate([
            'return_date'             => 'required|date',
            'reason'                  => 'nullable|string|max:500',
            'items'                   => 'required|array|min:1',
            'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
            'items.*.quantity'        => 'required|numeric|min:0.001',
        ]);

        abort_if(
            !in_array($invoice->status, ['sent', 'paid', 'partially_paid']),
            422,
            'لا يمكن إرجاع هذه الفاتورة.'
        );

        $itemsData = [];
        $total     = 0;

        foreach ($request->items as $row) {
            if (empty($row['quantity']) || (float) $row['quantity'] <= 0) continue;

            // التحقق أن البند ينتمي لهذه الفاتورة
            $invoiceItem = $invoice->items()->find($row['invoice_item_id']);
            if (!$invoiceItem) continue;

            // الكمية المُرجعة مسبقاً لهذا البند (مقيّدة بـ invoice_item_id للدقة)
            $alreadyReturned = InvoiceReturnItem::whereHas(
                'invoiceReturn',
                fn($q) => $q->where('invoice_id', $invoice->id)
            )
                ->where('invoice_item_id', $invoiceItem->id)
                ->sum('quantity');

            $maxQty = (float) $invoiceItem->quantity - (float) $alreadyReturned;
            if ($maxQty <= 0.001) continue;

            $qty   = min((float) $row['quantity'], $maxQty);
            $total += $qty * (float) $invoiceItem->unit_price;

            $itemsData[] = [
                'invoice_item_id' => $invoiceItem->id,
                'product_id'      => $invoiceItem->product_id,
                'description'     => $invoiceItem->description,
                'quantity'        => $qty,
                'unit_price'      => (float) $invoiceItem->unit_price,
                'total'           => round($qty * (float) $invoiceItem->unit_price, 2),
            ];
        }

        if (empty($itemsData)) {
            return back()->withErrors(['items' => 'يجب اختيار صنف واحد على الأقل بكمية صالحة.']);
        }

        DB::transaction(function () use ($request, $invoice, $itemsData, $total) {
            $return = InvoiceReturn::create([
                'tenant_id'   => $invoice->tenant_id,
                'invoice_id'  => $invoice->id,
                'return_date' => $request->return_date,
                'reason'      => $request->reason,
                'total'       => round($total, 2),
                'created_by'  => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $return->items()->create($item);
            }

            // إعادة المخزون لكل منتج مُرجع
            // استخدام مخزن الفاتورة الأصلي إذا كان محدداً
            $returnWarehouseId = $request->warehouse_id
                ?? auth()->user()->getDefaultWarehouse()?->id;
            $this->stockService->restoreForReturn($invoice, $itemsData, $return->id, $returnWarehouseId);

            // ======================================================
            // تحديث المبالغ المالية للفاتورة بعد المرتجع
            // ======================================================
            $invoice->refresh();

            // مجموع كل المرتجعات لهذه الفاتورة (شامل المرتجع الحالي)
            $totalReturned = InvoiceReturn::where('invoice_id', $invoice->id)->sum('total');

            // المبلغ المستحق الجديد (لا يقل عن صفر)
            $newTotalAmount = max(0, (float) $invoice->total_amount - $total);

            // المبلغ المدفوع الجديد:
            // إذا كان المدفوع > الإجمالي الجديد → يُخفَّض للإجمالي الجديد (استُرد الزائد)
            // إذا كان المدفوع <= الإجمالي الجديد → يبقى كما هو (لم يُدفع شيء من هذا المرتجع)
            $newPaidAmount = min((float) $invoice->paid_amount, $newTotalAmount);

            // تحديد الحالة الجديدة للفاتورة
            if ($totalReturned >= (float) $invoice->total_amount - 0.001) {
                $newStatus = 'returned';
            } elseif ($newPaidAmount <= 0.001) {
                $newStatus = 'sent';
            } elseif ($newPaidAmount < $newTotalAmount - 0.001) {
                $newStatus = 'partially_paid';
            } else {
                $newStatus = 'paid';
            }

            $invoice->update([
                'total_amount' => $newTotalAmount,
                'paid_amount'  => $newPaidAmount,
                'status'       => $newStatus,
            ]);
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'تم تسجيل المرتجع وتحديث رصيد الفاتورة.');
    }
}
