<?php

namespace App\Http\Controllers;

use App\Exports\LedgerExport;
use App\Http\Controllers\Concerns\GeneratesLedgerPdf;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class CustomerController extends Controller
{
    use GeneratesLedgerPdf;
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $customers = Customer::where('tenant_id', $tenantId)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"))
            ->withCount('invoices')
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function create() { return view('customers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Customer::create(array_merge(
            $request->only('name', 'email', 'phone', 'whatsapp_number', 'address', 'city', 'country', 'tax_number', 'notes'),
            ['tenant_id' => auth()->user()->tenant_id]
        ));
        return redirect()->route('customers.index')->with('success', 'تم إضافة العميل.');
    }

    public function show(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);

        $tenantId = auth()->user()->tenant_id;

        // ملخص مالي من DB مباشرة
        $totalAmount = $customer->invoices()->sum('total_amount');
        $totalPaid   = $customer->invoices()->sum('paid_amount');
        $totalDue    = max(0, $totalAmount - $totalPaid);

        $invoiceRows = $customer->invoices()
            ->with('payments')
            ->latest('invoice_date')
            ->paginate(20);

        // OPS-08 Fix: استدعاء buildLedger بدلاً من تكرار المنطق
        [$ledger] = $this->buildLedger($customer);

        $paymentMethods = \App\Models\PaymentMethod::where('tenant_id', $tenantId)
            ->where('is_active', true)->get();

        return view('customers.show', compact(
            'customer', 'ledger', 'totalDue', 'paymentMethods', 'invoiceRows'
        ));
    }

    public function edit(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['name' => 'required|string|max:255']);
        $customer->update($request->only('name', 'email', 'phone', 'whatsapp_number', 'address', 'city', 'country', 'tax_number', 'notes'));
        return redirect()->route('customers.index')->with('success', 'تم تحديث العميل.');
    }

    public function destroy(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'تم حذف العميل.');
    }

    private function buildLedger(Customer $customer): array
    {
        $customer->load(['invoices' => fn($q) => $q->with('payments')->latest()]);
        $ledger = collect();
        foreach ($customer->invoices as $invoice) {
            $ledger->push(['date' => $invoice->invoice_date, 'type' => 'invoice', 'description' => 'فاتورة ' . $invoice->invoice_number, 'debit' => $invoice->total_amount, 'credit' => 0, 'ref' => '']);
            foreach ($invoice->payments as $payment) {
                $ledger->push(['date' => $payment->payment_date, 'type' => 'payment', 'description' => 'دفعة - ' . $payment->payment_method, 'debit' => 0, 'credit' => $payment->amount, 'ref' => '']);
            }
        }
        $ledger = $ledger->sortBy('date')->values();
        $balance = 0;
        $ledger = $ledger->map(function ($row) use (&$balance) {
            $balance += $row['debit'] - $row['credit'];
            $row['balance'] = $balance;
            return $row;
        });
        return [$ledger, $ledger->sum('debit'), $ledger->sum('credit'), $balance];
    }

    public function exportPdf(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        [$ledger, $totalDebit, $totalCredit, $balance] = $this->buildLedger($customer);
        return $this->generateLedgerPdfResponse($ledger, $totalDebit, $totalCredit, $balance, $customer->name);
    }

    public function exportExcel(Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
        [$ledger, $totalDebit, $totalCredit, $balance] = $this->buildLedger($customer);
        return Excel::download(
            new LedgerExport($ledger, $customer->name, $totalDebit, $totalCredit, $balance),
            'كشف-حساب-' . $customer->name . '.xlsx'
        );
    }

    public function bulkPayment(Request $request, Customer $customer)
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);

        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'notes'          => 'nullable|string|max:500',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $validMethod = \App\Models\PaymentMethod::where('tenant_id', $tenantId)
            ->where('code', $request->payment_method)
            ->where('is_active', true)
            ->exists();
        abort_if(!$validMethod, 422, 'طريقة الدفع غير صالحة.');

        // الفواتير المستحقة مرتبة من الأقدم للأحدث (FIFO)
        $invoices = \App\Models\Invoice::where('customer_id', $customer->id)
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        if ($invoices->isEmpty()) {
            return back()->withErrors(['amount' => 'لا توجد فواتير مستحقة لهذا العميل.']);
        }

        $remaining = (float) $request->amount;
        $distributed = [];

        DB::transaction(function () use ($invoices, &$remaining, $request, $tenantId, &$distributed) {
            foreach ($invoices as $invoice) {
                if ($remaining <= 0.001) break;

                $freshInvoice = \App\Models\Invoice::lockForUpdate()->find($invoice->id);
                $due = $freshInvoice->total_amount - $freshInvoice->paid_amount;
                if ($due <= 0.001) continue;

                $pay = min($remaining, $due);

                \App\Models\Payment::create([
                    'tenant_id'      => $tenantId,
                    'invoice_id'     => $freshInvoice->id,
                    'payment_date'   => $request->payment_date,
                    'amount'         => $pay,
                    'payment_method' => $request->payment_method,
                    'notes'          => $request->notes ?? 'سداد على المتأخرات',
                ]);

                $freshInvoice->increment('paid_amount', $pay);
                $freshInvoice->refresh();

                if ($freshInvoice->paid_amount >= $freshInvoice->total_amount - 0.001) {
                    $freshInvoice->update(['status' => 'paid']);
                } else {
                    $freshInvoice->update(['status' => 'partially_paid']);
                }

                $distributed[] = [
                    'invoice_number' => $freshInvoice->invoice_number,
                    'paid'           => $pay,
                ];
                $remaining -= $pay;
            }
        });

        $msg = 'تم توزيع ' . number_format($request->amount - $remaining, 2) . ' على ' . count($distributed) . ' فاتورة.';
        if ($remaining > 0.001) {
            $msg .= ' (المتبقي ' . number_format($remaining, 2) . ' لم يُوزّع لعدم وجود فواتير كافية.)';
        }

        return back()->with('success', $msg);
    }
}
