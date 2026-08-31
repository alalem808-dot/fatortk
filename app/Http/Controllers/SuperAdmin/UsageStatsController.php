<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsageStatsController extends Controller
{
    public function index()
    {
        // أكثر 10 مشتركين نشاطاً بعدد الفواتير
        $topByInvoices = Tenant::withCount('invoices')
            ->orderByDesc('invoices_count')
            ->take(10)
            ->get();
        
        // أكثر 10 مشتركين بعدد العملاء
        $topByCustomers = Tenant::withCount('customers')
            ->orderByDesc('customers_count')
            ->take(10)
            ->get();
        
        // مقارنة الاستخدام لكل مشترك
        $usageData = Tenant::withCount(['invoices', 'customers', 'products', 'users'])
            ->orderByDesc('invoices_count')
            ->paginate(20);
        
        // إجماليات النظام
        $totals = [
            'invoices'  => \App\Models\Invoice::count(),
            'customers' => \App\Models\Customer::count(),
            'products'  => \App\Models\Product::count(),
            'users'     => User::count(),
        ];
        
        // آخر دخول لكل مشترك (من خلال آخر مستخدم سجّل دخول)
        $lastLogins = DB::table('users')
            ->select('tenant_id', DB::raw('MAX(last_login) as last_login'))
            ->whereNotNull('last_login')
            ->groupBy('tenant_id')
            ->pluck('last_login', 'tenant_id');
        
        return view('super_admin.usage_stats', compact(
            'topByInvoices', 'topByCustomers', 'usageData', 'totals', 'lastLogins'
        ));
    }
    
    public function export()
    {
        $data = Tenant::withCount(['invoices', 'customers', 'products', 'users'])->get();
        
        $csv = "\xEF\xBB\xBF";
        $csv .= "الشركة,الخطة,الحالة,الفواتير,العملاء,المنتجات,المستخدمون,تاريخ التسجيل,انتهاء الاشتراك\n";
        foreach ($data as $t) {
            $csv .= implode(',', [
                '"' . $t->company_name . '"',
                '"' . $t->subscription_plan . '"',
                '"' . $t->status . '"',
                $t->invoices_count,
                $t->customers_count,
                $t->products_count,
                $t->users_count,
                '"' . $t->created_at->format('Y-m-d') . '"',
                '"' . ($t->subscription_expires_at?->format('Y-m-d') ?? '') . '"',
            ]) . "\n";
        }
        
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="usage_stats_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
