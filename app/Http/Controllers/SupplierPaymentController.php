<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id'    => 'required|exists:purchases,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        $purchase = Purchase::findOrFail($request->purchase_id);
        $tenantId = auth()->user()->tenant_id;
        abort_if($purchase->tenant_id !== $tenantId, 403);

        // التحقق من أن أمر الشراء مستلم
        abort_if($purchase->status !== 'received', 422, 'يمكن تسجيل الدفع للمشتريات المستلمة فقط.');

        // التحقق من صحة طريقة الدفع
        $validMethod = PaymentMethod::where('tenant_id', $tenantId)
            ->where('code', $request->payment_method)
            ->where('is_active', true)
            ->exists();
        abort_if(!$validMethod, 422, 'طريقة الدفع غير صالحة.');

        // التحقق من عدم تجاوز المبلغ المتبقي
        $remaining = $purchase->remaining_amount;
        if ($request->amount > $remaining + 0.001) {
            return back()->withErrors([
                'amount' => 'المبلغ أكبر من المتبقي (' . number_format($remaining, 2) . ').'
            ]);
        }

        DB::transaction(function () use ($request, $purchase, $tenantId) {
            SupplierPayment::create([
                'tenant_id'        => $tenantId,
                'purchase_id'      => $purchase->id,
                'supplier_id'      => $purchase->supplier_id,
                'payment_date'     => $request->payment_date,
                'amount'           => $request->amount,
                'payment_method'   => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes'            => $request->notes,
                'created_by'       => auth()->id(),
            ]);

            // تحديث المبلغ المدفوع بـ lock لمنع Race Condition
            $fresh = Purchase::lockForUpdate()->find($purchase->id);
            $fresh->increment('paid_amount', $request->amount);
            $fresh->refresh();

            if ($fresh->paid_amount >= $fresh->total - 0.001) {
                $fresh->update(['payment_status' => 'paid']);
            } elseif ($fresh->paid_amount > 0) {
                $fresh->update(['payment_status' => 'partial']);
            }
        });

        return back()->with('success', 'تم تسجيل الدفعة للمورد بنجاح.');
    }

    public function destroy(SupplierPayment $supplierPayment)
    {
        abort_if($supplierPayment->tenant_id !== auth()->user()->tenant_id, 403);

        DB::transaction(function () use ($supplierPayment) {
            $purchase = Purchase::lockForUpdate()->find($supplierPayment->purchase_id);

            $supplierPayment->delete();

            // إعادة حساب المبلغ المدفوع
            $totalPaid = SupplierPayment::where('purchase_id', $purchase->id)->sum('amount');
            $purchase->update([
                'paid_amount'    => $totalPaid,
                'payment_status' => $totalPaid <= 0 ? 'unpaid' : ($totalPaid >= $purchase->total - 0.001 ? 'paid' : 'partial'),
            ]);
        });

        return back()->with('success', 'تم حذف الدفعة.');
    }
}
