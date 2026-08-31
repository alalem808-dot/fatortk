<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|alpha_dash',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // تحقق من عدم تكرار الكود لنفس الـ tenant
        $exists = PaymentMethod::where('tenant_id', $tenantId)
            ->where('code', $request->code)->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'هذا الرمز مستخدم بالفعل.']);
        }

        $max = PaymentMethod::where('tenant_id', $tenantId)->max('sort_order') ?? 0;
        PaymentMethod::create([
            'tenant_id'  => $tenantId,
            'name'       => $request->name,
            'code'       => strtolower($request->code),
            'is_active'  => true,
            'sort_order' => $max + 1,
        ]);

        return back()->with('success', 'تم إضافة طريقة الدفع.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        abort_if($paymentMethod->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['name' => 'required|string|max:100']);
        $paymentMethod->update(['name' => $request->name]);
        return back()->with('success', 'تم تحديث طريقة الدفع.');
    }

    public function toggle(PaymentMethod $paymentMethod)
    {
        abort_if($paymentMethod->tenant_id !== auth()->user()->tenant_id, 403);
        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);
        return back()->with('success', 'تم تحديث الحالة.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        abort_if($paymentMethod->tenant_id !== auth()->user()->tenant_id, 403);
        $paymentMethod->delete();
        return back()->with('success', 'تم حذف طريقة الدفع.');
    }
}
