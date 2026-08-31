<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\InvoiceTemplate;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants'   => Tenant::count(),
            'active_tenants'  => Tenant::where('status', 'active')->count(),
            'trial_tenants'   => Tenant::where('status', 'trial')->count(),
            'suspended'       => Tenant::where('status', 'suspended')->count(),
            'total_invoices'  => Invoice::count(),
            'total_users'     => User::count(),
            'new_this_month'  => Tenant::whereMonth('created_at', now()->month)->count(),
        ];

        $recentTenants = Tenant::latest()->take(8)->get();

        $planStats = Tenant::selectRaw('subscription_plan, count(*) as count')
            ->groupBy('subscription_plan')
            ->pluck('count', 'subscription_plan');

        // نمو الحسابات آخر 6 أشهر
        $growthData = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            return [
                'month' => $month->format('M Y'),
                'count' => Tenant::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->count(),
            ];
        });

        return view('super_admin.dashboard', compact('stats', 'recentTenants', 'planStats', 'growthData'));
    }

    // إنشاء حساب جديد
    public function createTenant()
    {
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->get();
        return view('super_admin.tenants.create', compact('plans'));
    }

    public function storeTenant(Request $request)
    {
        $request->validate([
            'company_name'      => 'required|string|max:255',
            'subdomain'         => 'required|alpha_dash|unique:tenants,subdomain|min:3|max:50',
            'email'             => 'required|email|unique:users,email',
            'name'              => 'required|string|max:255',
            'username'          => 'required|alpha_dash|min:3|max:30|unique:users,username',
            'password'          => 'required|min:8|confirmed',
            'subscription_plan' => 'required|exists:subscription_plans,slug',
            'phone'             => 'nullable|string',
        ]);

        $tenant = \App\Models\Tenant::create([
            'company_name'            => $request->company_name,
            'subdomain'               => strtolower($request->subdomain),
            'email'                   => $request->email,
            'phone'                   => $request->phone,
            'status'                  => $request->status ?? 'active',
            'subscription_plan'       => $request->subscription_plan,
            'subscription_expires_at' => $request->subscription_expires_at,
        ]);

        $adminUser = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'admin',
        ]);

        // القالب الافتراضي
        \App\Models\InvoiceTemplate::create([
            'tenant_id'  => $tenant->id,
            'name'       => 'القالب الافتراضي',
            'is_default' => true,
        ]);

        // طرق الدفع الافتراضية
        \App\Models\PaymentMethod::createDefaults($tenant->id);

        // المخزن الرئيسي الافتراضي (ضروري لعمل المخزون)
        \App\Models\Warehouse::create([
            'tenant_id'  => $tenant->id,
            'name'       => 'المخزن الرئيسي',
            'is_default' => true,
            'is_active'  => true,
        ]);

        // عميل نقدي افتراضي للبيع المباشر
        $cashCustomer = \App\Models\Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => 'عميل نقدي',
            'notes'     => 'عميل افتراضي للمبيعات المباشرة',
        ]);
        \App\Models\Setting::create([
            'tenant_id' => $tenant->id,
            'key'       => 'quick_sale_customer_id',
            'value'     => $cashCustomer->id,
        ]);

        // منح كل الصلاحيات لمستخدم Admin الجديد
        $allPerms = \Spatie\Permission\Models\Permission::all();
        $adminUser->syncPermissions($allPerms);

        \App\Models\ActivityLog::log('created', "تم إنشاء حساب جديد: {$tenant->company_name}", $tenant);

        return redirect()->route('super_admin.tenants')->with('success', 'تم إنشاء الحساب بنجاح.');
    }

    // إدارة الشركات
    public function tenants(Request $request)
    {
        $tenants = Tenant::withCount(['users', 'invoices'])
            ->when($request->search, fn($q) => $q->where('company_name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->plan, fn($q) => $q->where('subscription_plan', $request->plan))
            ->latest()
            ->paginate(15);

        return view('super_admin.tenants.index', compact('tenants'));
    }

    public function showTenant(Tenant $tenant)
    {
        $tenant->loadCount(['users', 'invoices', 'products', 'customers']);
        $tenant->load(['users']);
        $revenue = Payment::whereHas('invoice', fn($q) => $q->where('tenant_id', $tenant->id))->sum('amount');
        $plans   = SubscriptionPlan::orderBy('price_yearly_usd')->get();
        return view('super_admin.tenants.show', compact('tenant', 'revenue', 'plans'));
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status'            => 'required|in:trial,active,suspended',
            'subscription_plan' => 'required|exists:subscription_plans,slug',
        ]);

        $changes = [];
        if ($tenant->status !== $request->status) {
            $changes['status'] = ['from' => $tenant->status, 'to' => $request->status];
        }
        if ($tenant->subscription_plan !== $request->subscription_plan) {
            $changes['plan'] = ['from' => $tenant->subscription_plan, 'to' => $request->subscription_plan];
        }

        $tenant->update([
            'status'                  => $request->status,
            'subscription_plan'       => $request->subscription_plan,
            'subscription_expires_at' => $request->subscription_expires_at,
        ]);

        if ($changes) {
            \App\Models\ActivityLog::log(
                empty($changes['plan']) ? 'status_changed' : 'plan_changed',
                "تم تحديث بيانات {$tenant->company_name}",
                $tenant,
                $changes
            );
        }

        return back()->with('success', 'تم تحديث بيانات الشركة.');
    }

    public function deleteTenant(Tenant $tenant)
    {
        \App\Models\ActivityLog::log('deleted', "تم حذف شركة {$tenant->company_name}", null, ['company_name' => $tenant->company_name]);
        $tenant->delete();
        return redirect()->route('super_admin.tenants')->with('success', 'تم حذف الشركة.');
    }

    public function toggleCurrencies(Tenant $tenant)
    {
        $tenant->update(['currencies_enabled' => !$tenant->currencies_enabled]);
        $msg = $tenant->currencies_enabled ? 'تم تفعيل محور العملات وأسعار الصرف.' : 'تم إلغاء تفعيل محور العملات وأسعار الصرف.';
        return back()->with('success', $msg);
    }

    // ===== إدارة العملات العامة =====
    public function currencies()
    {
        $currencies = \App\Models\GlobalCurrency::orderBy('sort_order')->get();
        return view('super_admin.currencies', compact('currencies'));
    }

    public function storeCurrency(Request $request)
    {
        $request->validate([
            'code'   => 'required|string|max:10|unique:global_currencies,code',
            'name'   => 'required|string|max:100',
            'symbol' => 'nullable|string|max:10',
        ]);

        $max = \App\Models\GlobalCurrency::max('sort_order') ?? 0;
        \App\Models\GlobalCurrency::create([
            'code'       => strtoupper($request->code),
            'name'       => $request->name,
            'symbol'     => $request->symbol,
            'is_active'  => true,
            'sort_order' => $max + 1,
        ]);

        return back()->with('success', 'تم إضافة العملة.');
    }

    public function updateCurrency(Request $request, \App\Models\GlobalCurrency $globalCurrency)
    {
        $request->validate([
            'name'   => 'required|string|max:100',
            'symbol' => 'nullable|string|max:10',
        ]);
        $globalCurrency->update($request->only('name', 'symbol'));
        return back()->with('success', 'تم تحديث العملة.');
    }

    public function toggleCurrency(\App\Models\GlobalCurrency $globalCurrency)
    {
        $globalCurrency->update(['is_active' => !$globalCurrency->is_active]);
        return back()->with('success', 'تم تغيير حالة العملة.');
    }

    public function destroyCurrency(\App\Models\GlobalCurrency $globalCurrency)
    {
        $globalCurrency->delete();
        return back()->with('success', 'تم حذف العملة.');
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $request->validate(['password' => 'required|min:8|confirmed']);
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'تم إعادة تعيين كلمة مرور ' . $user->name . '.');
    }

    // إعدادات Super Admin
    public function settings()
    {
        $admin            = auth('super_admin')->user();
        $platformSettings = \App\Models\PlatformSetting::orderBy('key')->get();
        return view('super_admin.settings', compact('admin', 'platformSettings'));
    }

    public function updateSettings(Request $request)
    {
        $admin = auth('super_admin')->user();

        $request->validate([
            'name'         => 'required|string',
            'email'        => 'required|email|unique:super_admins,email,' . $admin->id,
            'platform_logo'    => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'platform_favicon' => 'nullable|image|mimes:png,jpg,ico,svg|max:512',
            'login_logo'       => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $data = $request->only('name', 'email');

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        // حفظ إعدادات المنصة النصية
        // الحقول في الـ form تأتي بصيغة: platform_{key}
        // والـ key الفعلي في قاعدة البيانات هو: {key} (بدون platform_)
        $platformKeys = [
            'name',
            'support_email',
            'whatsapp_number',
            'whatsapp_subscribe_msg',
            'whatsapp_renew_msg',
            'whatsapp_register_msg',
        ];
        foreach ($platformKeys as $key) {
            if ($request->has("platform_{$key}")) {
                \App\Models\PlatformSetting::set($key, $request->input("platform_{$key}"));
            } elseif ($request->has($key)) {
                // دعم الإرسال المباشر بدون بادئة
                \App\Models\PlatformSetting::set($key, $request->input($key));
            }
        }

        // رفع الصور
        $imageKeys = ['platform_logo', 'platform_favicon', 'login_logo'];
        foreach ($imageKeys as $key) {
            if ($request->hasFile($key)) {
                // حذف الصورة القديمة إن وجدت
                $old = \App\Models\PlatformSetting::get($key);
                if ($old && \Illuminate\Support\Facades\Storage::disk('public')->exists($old)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
                }
                $path = $request->file($key)->store('platform', 'public');
                \App\Models\PlatformSetting::set($key, $path);
            }
        }

        \App\Models\ActivityLog::log('settings_updated', 'تم تحديث إعدادات المنصة');

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
