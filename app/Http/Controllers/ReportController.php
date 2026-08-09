<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$from, $to])
            ->with('customer')
            ->get();

        $summary = [
            'total'    => $invoices->sum('total_amount'),
            'paid'     => $invoices->sum('paid_amount'),
            'pending'  => $invoices->sum(fn($i) => $i->total_amount - $i->paid_amount),
            'tax'      => $invoices->sum('tax_amount'),
            'count'    => $invoices->count(),
        ];

        if ($request->export === 'excel') {
            return Excel::download(new InvoicesExport($invoices), 'sales-report.xlsx');
        }

        return view('reports.sales', compact('invoices', 'summary', 'from', 'to'));
    }

    public function stock(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $products = Product::where('tenant_id', $tenantId)->with('category')->get();

        $summary = [
            'total_products'  => $products->count(),
            'low_stock'       => $products->filter(fn($p) => $p->isLowStock())->count(),
            'total_value'     => $products->sum(fn($p) => $p->stock_quantity * $p->cost_price),
        ];

        return view('reports.stock', compact('products', 'summary'));
    }

    public function customers(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $customers = Customer::where('tenant_id', $tenantId)
            ->withSum('invoices', 'total_amount')
            ->withSum('invoices', 'paid_amount')
            ->withCount('invoices')
            ->get();

        return view('reports.customers', compact('customers'));
    }
}
