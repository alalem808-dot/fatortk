<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user          = auth()->user();
        $tenantId      = $user->tenant_id;
        $canViewProfit = $user->can('reports.view_profit');
        $canViewSales  = $user->can('reports.view_sales');
        // الأدمن والمحاسب يرون كل الفواتير، غيرهم يرون فواتيرهم فقط
        $isFinancial   = $canViewProfit || $user->hasRole('admin');

        $invoiceQuery = Invoice::where('tenant_id', $tenantId)
            ->when(!$isFinancial, fn($q) => $q->where('created_by', $user->id));

        $stats = [
            'total_invoices'   => (clone $invoiceQuery)->count(),
            'paid_invoices'    => (clone $invoiceQuery)->where('status', 'paid')->count(),
            'overdue_invoices' => (clone $invoiceQuery)->where('status', 'overdue')->count(),
            'unpaid_amount'    => (clone $invoiceQuery)->whereIn('status', ['sent','overdue','partially_paid'])
                ->sum(DB::raw('total_amount - paid_amount')),
            'total_customers'  => $isFinancial
                ? Customer::where('tenant_id', $tenantId)->count()
                : Customer::where('tenant_id', $tenantId)
                    ->whereHas('invoices', fn($q) => $q->where('created_by', $user->id))
                    ->count(),
            'low_stock_count'  => Product::where('tenant_id', $tenantId)->whereColumn('stock_quantity', '<=', 'min_stock_alert')->count(),
        ];

        // الإيرادات: من يملك reports.view_sales يرى كل الإيرادات، غيره يرى إيرادات فواتيره فقط
        $stats['total_revenue'] = Payment::whereHas('invoice', function ($q) use ($tenantId, $isFinancial, $user) {
                $q->where('tenant_id', $tenantId);
                if (!$isFinancial) $q->where('created_by', $user->id);
            })
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        if ($canViewProfit) {
            $stats['total_expenses'] = Expense::where('tenant_id', $tenantId)
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum(DB::raw('amount * exchange_rate'));

            $stats['cogs'] = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('products', 'invoice_items.product_id', '=', 'products.id')
                ->where('invoices.tenant_id', $tenantId)
                ->whereIn('invoices.status', ['sent', 'paid', 'partially_paid'])
                ->whereMonth('invoices.invoice_date', now()->month)
                ->whereYear('invoices.invoice_date', now()->year)
                ->sum(DB::raw('invoice_items.quantity * products.cost_price'));

            $stats['net_profit'] = $stats['total_revenue'] - $stats['cogs'] - $stats['total_expenses'];

            $stats['purchases_month'] = DB::table('purchases')
                ->where('tenant_id', $tenantId)
                ->whereMonth('purchase_date', now()->month)
                ->whereYear('purchase_date', now()->year)
                ->sum(DB::raw('total * exchange_rate'));

            $topCustomers = Customer::where('tenant_id', $tenantId)
                ->withSum(['invoices' => fn($q) => $q->whereIn('status', ['paid','partially_paid'])
                    ->whereMonth('invoice_date', now()->month)
                    ->whereYear('invoice_date', now()->year)], 'paid_amount')
                ->orderByDesc('invoices_sum_paid_amount')
                ->take(5)
                ->get();
        } else {
            $topCustomers = collect();
        }
        $recentInvoices = (clone $invoiceQuery)
            ->with('customer')
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->take(5)
            ->get();

        $chartData = collect();
        if ($canViewSales) {
            $sixMonthsAgo   = now()->subMonths(5)->startOfMonth();
            $revenueByMonth = Payment::whereHas('invoice', function ($q) use ($tenantId, $isFinancial, $user) {
                $q->where('tenant_id', $tenantId);
                if (!$isFinancial) $q->where('created_by', $user->id);
            })
                ->where('payment_date', '>=', $sixMonthsAgo)
                ->selectRaw('YEAR(payment_date) as yr, MONTH(payment_date) as mn, SUM(amount) as total')
                ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
                ->get()
                ->mapWithKeys(fn($row) => [sprintf('%04d-%02d', $row->yr, $row->mn) => $row->total]);

            $chartData = collect(range(5, 0))->map(function ($i) use ($revenueByMonth) {
                $month = now()->subMonths($i);
                return ['month' => $month->translatedFormat('M'), 'revenue' => $revenueByMonth[$month->format('Y-m')] ?? 0];
            });
        }

        return view('dashboard.index', compact('stats', 'recentInvoices', 'lowStockProducts', 'chartData', 'canViewSales', 'canViewProfit', 'isFinancial', 'topCustomers'));
    }
}
