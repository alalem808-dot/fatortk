<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,card,cheque',
            'payment_date'   => 'required|date',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        abort_if($invoice->tenant_id !== auth()->user()->tenant_id, 403);

        Payment::create([
            'tenant_id'        => auth()->user()->tenant_id,
            'invoice_id'       => $invoice->id,
            'payment_date'     => $request->payment_date,
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
            'reference_number' => $request->reference_number,
            'notes'            => $request->notes,
        ]);

        $invoice->increment('paid_amount', $request->amount);

        if ($invoice->paid_amount >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        }

        return back()->with('success', 'تم تسجيل الدفعة بنجاح.');
    }
}
