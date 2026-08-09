<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
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
            'username'          => 'required|alpha_dash|min:3|max:30',
            'password'          => 'required|min:8|confirmed',
            'subscription_plan' => 'required|in:free,basic,pro,enterprise',
            'phone'             => 'nullable|string',
        ]);

        $tenant = \App\Models\Tenant::create([
            'company_name'      => $request->company_name,
            'subdomain'         => strtolower($request->subdomain),
            'email'             => $request->email,
            'phone'             => $request->phone,
            'status'            => $request->status ?? 'active',
            'subscription_plan' => $request->subscription_plan,
            'subscription_expires_at' => $request->subscription_expires_at,
        ]);

        \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'admin',
        ]);

        \App\Models\InvoiceTemplate::create([
            'tenant_id'  => $tenant->id,
            'name'       => 'القالب الافتراضي',
            'is_default' => true,
        ]);

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
        return view('super_admin.tenants.show', compact('tenant', 'revenue'));
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status'            => 'required|in:trial,active,suspended',
            'subscription_plan' => 'required|in:free,basic,pro,enterprise',
        ]);

        $tenant->update([
            'status'                  => $request->status,
            'subscription_plan'       => $request->subscription_plan,
            'subscription_expires_at' => $request->subscription_expires_at,
        ]);

        return back()->with('success', 'تم تحديث بيانات الشركة.');
    }

    public function deleteTenant(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('super_admin.tenants')->with('success', 'تم حذف الشركة.');
    }

    // إعدادات Super Admin
    public function settings()
    {
        $admin = auth('super_admin')->user();
        return view('super_admin.settings', compact('admin'));
    }

    public function updateSettings(Request $request)
    {
        $admin = auth('super_admin')->user();

        $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email|unique:super_admins,email,' . $admin->id,
        ]);

        $data = $request->only('name', 'email');

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);
        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
