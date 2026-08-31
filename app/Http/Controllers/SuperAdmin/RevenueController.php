<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RevenueController extends Controller
{
    public function index()
    {
        // إجمالي الإيرادات
        $totalRevenue = SubscriptionPayment::sum('amount_usd');
        
        // إيرادات هذا الشهر
        $thisMonthRevenue = SubscriptionPayment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)->sum('amount_usd');
        
        // إيرادات هذه السنة
        $thisYearRevenue = SubscriptionPayment::whereYear('paid_at', now()->year)->sum('amount_usd');
        
        // عدد الاشتراكات المدفوعة
        $totalPayments = SubscriptionPayment::count();
        
        // PERF-03 Fix: استعلام واحد بـ GROUP BY بدلاً من 12 استعلام منفصل
        $startDate = now()->subMonths(11)->startOfMonth();
        $monthlyRaw = SubscriptionPayment::selectRaw(
            'YEAR(paid_at) as yr, MONTH(paid_at) as mo, SUM(amount_usd) as total'
        )
            ->where('paid_at', '>=', $startDate)
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->pluck('total', DB::raw("CONCAT(yr, '-', LPAD(mo, 2, '0'))"));

        $monthlyRevenue = collect(range(11, 0))->map(function ($i) use ($monthlyRaw) {
            $month = now()->subMonths($i);
            $key   = $month->format('Y-n');
            return [
                'month'  => $month->locale('ar')->isoFormat('MMM YY'),
                'amount' => $monthlyRaw[$key] ?? 0,
            ];
        });
        
        // الاشتراكات القادمة للتجديد (خلال 30 يوم)
        $renewingSoon = Tenant::where('status', 'active')
            ->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [now(), now()->addDays(30)])
            ->orderBy('subscription_expires_at')
            ->get();
        
        // آخر المدفوعات
        $recentPayments = SubscriptionPayment::with('tenant')
            ->latest('paid_at')
            ->take(10)
            ->get();
        
        return view('super_admin.revenue.index', compact(
            'totalRevenue', 'thisMonthRevenue', 'thisYearRevenue', 'totalPayments',
            'monthlyRevenue', 'renewingSoon', 'recentPayments'
        ));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id'  => 'required|exists:tenants,id',
            'amount_usd' => 'required|numeric|min:1',
            'period'     => 'required|in:monthly,yearly',
            'paid_at'    => 'required|date',
            'expires_at' => 'required|date|after:paid_at',
            'notes'      => 'nullable|string|max:500',
        ]);
        
        $tenant = Tenant::findOrFail($request->tenant_id);
        $plan   = \App\Models\SubscriptionPlan::where('slug', $tenant->subscription_plan)->first();
        
        SubscriptionPayment::create([
            'tenant_id'  => $tenant->id,
            'plan_slug'  => $tenant->subscription_plan,
            'plan_name'  => $plan?->name ?? $tenant->subscription_plan,
            'amount_usd' => $request->amount_usd,
            'period'     => $request->period,
            'paid_at'    => $request->paid_at,
            'expires_at' => $request->expires_at,
            'notes'      => $request->notes,
        ]);
        
        // تحديث تاريخ انتهاء الاشتراك
        $tenant->update([
            'subscription_expires_at' => $request->expires_at,
            'status' => 'active',
        ]);
        
        \App\Models\ActivityLog::log('payment_recorded', "تم تسجيل دفعة {$request->amount_usd}$ للشركة {$tenant->company_name}", $tenant);
        
        return back()->with('success', 'تم تسجيل الدفعة بنجاح وتم تحديث تاريخ الاشتراك.');
    }
    
    public function destroy(SubscriptionPayment $payment)
    {
        \App\Models\ActivityLog::log('payment_deleted', "تم حذف دفعة {$payment->amount_usd}$ للشركة {$payment->tenant->company_name}", $payment->tenant);
        $payment->delete();
        return back()->with('success', 'تم حذف الدفعة.');
    }
    
    public function export()
    {
        $payments = SubscriptionPayment::with('tenant')->orderByDesc('paid_at')->get();

        // OPS-10 Fix: إرجاع رسالة واضحة عند غياب البيانات بدلاً من CSV بترويسة فارغة
        if ($payments->isEmpty()) {
            return back()->with('info', 'لا توجد بيانات للتصدير في الوقت الحالي.');
        }

        $rows = $payments->map(fn($p) => [
            'الشركة'         => $p->tenant->company_name,
            'الخطة'          => $p->plan_name,
            'المبلغ (USD)'   => $p->amount_usd,
            'الفترة'         => $p->period === 'yearly' ? 'سنوي' : 'شهري',
            'تاريخ الدفع'    => $p->paid_at->format('Y-m-d'),
            'تاريخ الانتهاء' => $p->expires_at->format('Y-m-d'),
            'ملاحظات'        => $p->notes,
        ]);

        $csv = "\xEF\xBB\xBF"; // BOM for Excel Arabic
        $csv .= implode(',', array_keys($rows->first())) . "\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"', $row)) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="payments_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
