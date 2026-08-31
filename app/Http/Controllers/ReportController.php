<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;

class ReportController extends Controller
{
    public function purchases(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $query = Purchase::where('tenant_id', $tenantId)
            ->whereBetween('purchase_date', [$from, $to])
            ->with('items.product');

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest()->get();
        $suppliers = \App\Models\Supplier::where('tenant_id', $tenantId)->get();

        $summary = [
            'total'    => $purchases->sum('total'),
            'received' => $purchases->where('status', 'received')->sum('total'),
            'pending'  => $purchases->where('status', 'pending')->sum('total'),
            'count'    => $purchases->count(),
        ];

        return view('reports.purchases', compact('purchases', 'summary', 'suppliers', 'from', 'to'));
    }

    public function sales(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$from, $to])
            ->with(['customer', 'creator', 'payments'])
            ->when($request->created_by, fn($q) => $q->where('created_by', $request->created_by))
            ->get();

        $invoiceIds = $invoices->pluck('id');

        $summary = [
            'total'    => $invoices->sum('total_amount'),
            // BUG-10 Fix: نستخدم مدفوعات نفس الفواتير بدون فلتر التاريخ
            // لتتوافق مع paid_amount المحسوب على الفاتورة نفسها
            'paid'     => $invoices->sum('paid_amount'),
            'pending'  => $invoices->sum(fn($i) => $i->total_amount - $i->paid_amount),
            'tax'      => $invoices->sum('tax_amount'),
            'count'    => $invoices->count(),
        ];

        // إيرادات كل موظف
        $byEmployee = $invoices->groupBy('created_by')->map(fn($group) => [
            'name'  => $group->first()->creator->name ?? 'غير معروف',
            'count' => $group->count(),
            'total' => $group->sum('total_amount'),
            'paid'  => $group->sum('paid_amount'),
        ])->values();

        // BUG-10 Fix: طرق الدفع بدون فلتر التاريخ — تتطابق مع paid_amount في الملخص
        $allPayments = Payment::whereIn('invoice_id', $invoiceIds)->get();

        $paymentMethods = \App\Models\PaymentMethod::where('tenant_id', $tenantId)->get()->keyBy('code');
        $byPaymentMethod = $allPayments->groupBy('payment_method')->map(fn($group, $code) => [
            'name'  => $paymentMethods[$code]->name ?? $code,
            'count' => $group->count(),
            'total' => $group->sum('amount'),
        ])->sortByDesc('total')->values();

        $employees = \App\Models\User::where('tenant_id', $tenantId)->get();

        // أكثر المنتجات مبيعاً مفلترة بنفس الفواتير
        $topProducts = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->whereIn('invoices.status', ['sent', 'paid', 'partially_paid'])
            ->whereBetween('invoices.invoice_date', [$from, $to])
            ->when($request->created_by, fn($q) => $q->where('invoices.created_by', $request->created_by))
            ->selectRaw('products.name, SUM(invoice_items.quantity) as total_qty, SUM(invoice_items.quantity * invoice_items.unit_price) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // العملاء المتأخرون مفلترين بنفس الفواتير
        $overdueCustomers = Customer::where('tenant_id', $tenantId)
            ->whereHas('invoices', fn($q) => $q->whereIn('status', ['sent', 'overdue', 'partially_paid'])
                ->whereColumn('paid_amount', '<', 'total_amount')
                ->when($request->created_by, fn($q2) => $q2->where('created_by', $request->created_by)))
            ->withSum(['invoices as total_due' => fn($q) => $q->whereIn('status', ['sent', 'overdue', 'partially_paid'])
                ->when($request->created_by, fn($q2) => $q2->where('created_by', $request->created_by))],
                DB::raw('total_amount - paid_amount'))
            ->orderByDesc('total_due')
            ->limit(5)
            ->get();

        if ($request->export === 'excel') {
            return Excel::download(new InvoicesExport($invoices), 'sales-report.xlsx');
        }

        return view('reports.sales', compact(
            'invoices', 'summary', 'byEmployee', 'byPaymentMethod',
            'employees', 'topProducts', 'overdueCustomers', 'from', 'to'
        ));
    }

    public function stock(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // PERF-05 Fix: pagination بدلاً من تحميل كل المنتجات دفعة واحدة
        $products = Product::where('tenant_id', $tenantId)
            ->with('category')
            ->when($request->warehouse_id, fn($q) =>
                $q->whereHas('warehouseStocks', fn($ws) =>
                    $ws->where('warehouse_id', $request->warehouse_id)->where('quantity', '>', 0)
                )
            )
            ->when($request->low_stock, fn($q) =>
                $q->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                  ->where('min_stock_alert', '>', 0)
            )
            ->paginate(50);

        $warehouses = \App\Models\Warehouse::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->withSum('stocks', 'quantity')
            ->get();

        $warehouseStocks = \App\Models\WarehouseStock::whereHas(
            'warehouse', fn($q) => $q->where('tenant_id', $tenantId)
        )
            ->with(['product.category', 'warehouse'])
            ->where('quantity', '>', 0)
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->get()
            ->groupBy('warehouse_id');

        $selectedWarehouseId = $request->warehouse_id;

        // معدل دوران المخزون (30 يوم)
        $soldQtys = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.tenant_id', $tenantId)
            ->whereIn('invoices.status', ['sent', 'paid', 'partially_paid'])
            ->where('invoices.invoice_date', '>=', now()->subDays(30))
            ->selectRaw('invoice_items.product_id, SUM(invoice_items.quantity) as sold_qty')
            ->groupBy('invoice_items.product_id')
            ->pluck('sold_qty', 'product_id');

        // آخر حركة بيع لكل منتج
        $lastSoldDates = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.tenant_id', $tenantId)
            ->whereIn('invoices.status', ['sent', 'paid', 'partially_paid'])
            ->selectRaw('invoice_items.product_id, MAX(invoices.invoice_date) as last_sold')
            ->groupBy('invoice_items.product_id')
            ->pluck('last_sold', 'product_id');

        // ملخص إجمالي من DB مباشرة (بدون تحميل كل المنتجات)
        $summary = [
            'total_products'   => Product::where('tenant_id', $tenantId)->count(),
            'low_stock'        => Product::where('tenant_id', $tenantId)
                                    ->where('min_stock_alert', '>', 0)
                                    ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                                    ->count(),
            'out_of_stock'     => Product::where('tenant_id', $tenantId)
                                    ->where('stock_quantity', '<=', 0)
                                    ->count(),
            'total_value'      => Product::where('tenant_id', $tenantId)
                                    ->selectRaw('SUM(stock_quantity * cost_price) as val')
                                    ->value('val') ?? 0,
            'total_warehouses' => $warehouses->count(),
        ];

        return view('reports.stock', compact(
            'products', 'warehouses', 'warehouseStocks', 'summary',
            'selectedWarehouseId', 'soldQtys', 'lastSoldDates'
        ));
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

    public function profit(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        // BUG-11 Fix: توحيد المنهجية على أساس تاريخ الفاتورة (Accrual Basis)
        // الإيرادات = إجمالي الفواتير المُرسلة/المدفوعة في الفترة (وليس المدفوعات)
        $revenue = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['sent', 'paid', 'partially_paid'])
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('total_amount');

        $cogs = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->whereIn('invoices.status', ['sent', 'paid', 'partially_paid'])
            ->whereBetween('invoices.invoice_date', [$from, $to])
            ->sum(DB::raw('invoice_items.quantity * products.cost_price'));

        $expenses = \App\Models\Expense::where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$from, $to])
            ->sum(DB::raw('amount * exchange_rate'));

        $grossProfit = $revenue - $cogs;
        $netProfit   = $grossProfit - $expenses;

        $expensesByCategory = \App\Models\Expense::where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$from, $to])
            ->with('category')
            ->get()
            ->groupBy(fn($e) => $e->category->name ?? 'غير مصنف')
            ->map(fn($g) => $g->sum('amount'));

        // هامش الربح الإجمالي لكل منتج
        $productMargins = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->whereIn('invoices.status', ['sent', 'paid', 'partially_paid'])
            ->whereBetween('invoices.invoice_date', [$from, $to])
            ->selectRaw('
                products.name,
                SUM(invoice_items.quantity) as total_qty,
                SUM(invoice_items.quantity * invoice_items.unit_price) as revenue,
                SUM(invoice_items.quantity * products.cost_price) as cost,
                SUM(invoice_items.quantity * (invoice_items.unit_price - products.cost_price)) as profit
            ')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('profit')
            ->limit(10)
            ->get();

        // مقارنة بالفترة السابقة — بنفس منهجية الاستحقاق
        $daysDiff    = \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to)) + 1;
        $prevFrom    = \Carbon\Carbon::parse($from)->subDays($daysDiff)->toDateString();
        $prevTo      = \Carbon\Carbon::parse($from)->subDay()->toDateString();
        $prevRevenue = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['sent', 'paid', 'partially_paid'])
            ->whereBetween('invoice_date', [$prevFrom, $prevTo])
            ->sum('total_amount');
        $revenueGrowth = $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) : null;

        return view('reports.profit', compact(
            'revenue', 'cogs', 'expenses', 'grossProfit', 'netProfit',
            'expensesByCategory', 'productMargins', 'revenueGrowth', 'prevRevenue',
            'from', 'to'
        ));
    }
}
