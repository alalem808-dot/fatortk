<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_invoices'    => Invoice::where('tenant_id', $tenantId)->count(),
            'paid_invoices'     => Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->count(),
            'overdue_invoices'  => Invoice::where('tenant_id', $tenantId)->where('status', 'overdue')->count(),
            'total_revenue'     => Payment::whereHas('invoice', fn($q) => $q->where('tenant_id', $tenantId))->sum('amount'),
            'total_customers'   => Customer::where('tenant_id', $tenantId)->count(),
            'low_stock_count'   => Product::where('tenant_id', $tenantId)->whereColumn('stock_quantity', '<=', 'min_stock_alert')->count(),
        ];

        $recentInvoices = Invoice::where('tenant_id', $tenantId)
            ->with('customer')
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->take(5)
            ->get();

        // بيانات الرسم البياني - آخر 6 أشهر
        $chartData = collect(range(5, 0))->map(function ($i) use ($tenantId) {
            $month = now()->subMonths($i);
            return [
                'month'   => $month->translatedFormat('M'),
                'revenue' => Payment::whereHas('invoice', fn($q) => $q->where('tenant_id', $tenantId))
                    ->whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)
                    ->sum('amount'),
            ];
        });

        return view('dashboard.index', compact('stats', 'recentInvoices', 'lowStockProducts', 'chartData'));
    }
}
