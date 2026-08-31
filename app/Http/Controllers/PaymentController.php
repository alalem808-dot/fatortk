<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        $invoice  = Invoice::findOrFail($request->invoice_id);
        $tenantId = auth()->user()->tenant_id;

        // التحقق من أن الفاتورة تخص المستأجر الحالي
        abort_if($invoice->tenant_id !== $tenantId, 403);

        // التحقق من أن الفاتورة في حالة تسمح بالدفع
        abort_if(
            in_array($invoice->status, ['draft', 'cancelled', 'returned']),
            422,
            'لا يمكن تسجيل دفعة لفاتورة بهذه الحالة.'
        );

        // التحقق من صحة طريقة الدفع — من جدول payment_methods الديناميكي
        $validMethod = PaymentMethod::where('tenant_id', $tenantId)
            ->where('code', $request->payment_method)
            ->where('is_active', true)
            ->exists();
        abort_if(!$validMethod, 422, 'طريقة الدفع غير صالحة.');

        // التحقق من عدم تجاوز المبلغ المتبقي
        $remaining = $invoice->total_amount - $invoice->paid_amount;
        if ($request->amount > $remaining + 0.001) {
            return back()->withErrors([
                'amount' => 'المبلغ أكبر من المتبقي (' . number_format($remaining, 2) . ').'
            ]);
        }

        DB::transaction(function () use ($request, $invoice, $tenantId) {
            Payment::create([
                'tenant_id'        => $tenantId,
                'invoice_id'       => $invoice->id,
                'payment_date'     => $request->payment_date,
                'amount'           => $request->amount,
                'payment_method'   => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes'            => $request->notes,
            ]);

            // استخدام lockForUpdate لتجنب Race Condition في المدفوعات المتزامنة
            $freshInvoice = Invoice::lockForUpdate()->find($invoice->id);
            $freshInvoice->increment('paid_amount', $request->amount);
            $freshInvoice->refresh();

            if ($freshInvoice->paid_amount >= $freshInvoice->total_amount - 0.001) {
                $freshInvoice->update(['status' => 'paid']);
            } elseif ($freshInvoice->paid_amount > 0) {
                $freshInvoice->update(['status' => 'partially_paid']);
            }
        });

        return back()->with('success', 'تم تسجيل الدفعة بنجاح.');
    }
}
