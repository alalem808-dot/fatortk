<?php
namespace App\Http\Controllers;

use App\Exports\LedgerExport;
use App\Http\Controllers\Concerns\GeneratesLedgerPdf;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class SupplierController extends Controller
{
    use GeneratesLedgerPdf;
    public function index()
    {
        $suppliers = Supplier::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('purchases')
            ->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Supplier::create(array_merge($request->only('name','phone','email','address','tax_number','notes'), [
            'tenant_id' => auth()->user()->tenant_id,
        ]));
        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد.');
    }

    public function show(Supplier $supplier)
    {
        abort_if($supplier->tenant_id !== auth()->user()->tenant_id, 403);
        $supplier->load([
            'purchases' => fn($q) => $q->with('returns', 'payments')->latest(),
        ]);

        $totalPurchases = $supplier->purchases->where('status', 'received')->sum('total');
        $totalPaid      = $supplier->purchases->flatMap->payments->sum('amount');
        $totalReturned  = $supplier->purchases->flatMap->returns->sum('total');

        $stats = [
            'total'    => $totalPurchases,
            'paid'     => $totalPaid,
            'returned' => $totalReturned,
            'balance'  => $totalPurchases - $totalPaid - $totalReturned,
            'count'    => $supplier->purchases->count(),
            'pending'  => $supplier->purchases->where('status', 'pending')->count(),
        ];

        // OPS-08 Fix: استدعاء buildSupplierLedger بدلاً من تكرار المنطق
        [$ledger] = $this->buildSupplierLedger($supplier);

        $paymentMethods = \App\Models\PaymentMethod::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)->get();

        return view('suppliers.show', compact('supplier', 'stats', 'ledger', 'paymentMethods'));
    }

    private function buildSupplierLedger(Supplier $supplier): array
    {
        $supplier->load(['purchases' => fn($q) => $q->with('returns', 'payments')->latest()]);
        $ledger = collect();
        foreach ($supplier->purchases as $purchase) {
            if ($purchase->status === 'received') {
                $ledger->push(['date' => $purchase->purchase_date, 'type' => 'purchase', 'description' => 'مشتريات ' . $purchase->reference, 'debit' => $purchase->total, 'credit' => 0, 'ref' => '']);
            }
            foreach ($purchase->returns as $ret) {
                $ledger->push(['date' => $ret->return_date, 'type' => 'return', 'description' => 'مرتجع ' . $ret->reference, 'debit' => 0, 'credit' => $ret->total, 'ref' => '']);
            }
            foreach ($purchase->payments as $payment) {
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

    public function exportPdf(Supplier $supplier)
    {
        abort_if($supplier->tenant_id !== auth()->user()->tenant_id, 403);
        [$ledger, $totalDebit, $totalCredit, $balance] = $this->buildSupplierLedger($supplier);
        return $this->generateLedgerPdfResponse($ledger, $totalDebit, $totalCredit, $balance, $supplier->name);
    }

    public function exportExcel(Supplier $supplier)
    {
        abort_if($supplier->tenant_id !== auth()->user()->tenant_id, 403);
        [$ledger, $totalDebit, $totalCredit, $balance] = $this->buildSupplierLedger($supplier);
        return Excel::download(
            new LedgerExport($ledger, $supplier->name, $totalDebit, $totalCredit, $balance),
            'كشف-حساب-' . $supplier->name . '.xlsx'
        );
    }

    public function edit(Supplier $supplier)
    {
        abort_if($supplier->tenant_id !== auth()->user()->tenant_id, 403);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        abort_if($supplier->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['name' => 'required|string|max:255']);
        $supplier->update($request->only('name','phone','email','address','tax_number','notes'));
        return redirect()->route('suppliers.index')->with('success', 'تم تحديث المورد.');
    }

    public function destroy(Supplier $supplier)
    {
        abort_if($supplier->tenant_id !== auth()->user()->tenant_id, 403);
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد.');
    }
}
