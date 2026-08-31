<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $login = $request->login;
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = \App\Models\User::where($field, $login)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'بيانات الدخول غير صحيحة.'])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors(['login' => 'حسابك غير مفعّل.']);
        }

        if ($user->tenant->status === 'suspended') {
            return back()->withErrors(['login' => 'حساب شركتك موقوف، تواصل مع الدعم.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login' => now()]);

        return redirect()->route('dashboard');
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain'    => 'required|alpha_dash|unique:tenants,subdomain|min:3|max:50',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:8|confirmed',
            'name'         => 'required|string|max:255',
            'username'     => 'nullable|string|alpha_dash|min:3|max:50|unique:users,username',
        ]);

        $tenant = Tenant::create([
            'company_name' => $request->company_name,
            'subdomain'    => strtolower($request->subdomain),
            'email'        => $request->email,
            'status'       => 'trial',
        ]);

        $username = $request->username
            ?? strtolower(str_replace(' ', '_', $request->name)) . rand(100, 999);

        // التأكد من أن الـ username فريد مع حد أقصى للمحاولات
        $attempts = 0;
        while (User::where('username', $username)->exists() && $attempts < 20) {
            $username = strtolower(str_replace(' ', '_', $request->name)) . rand(100, 9999);
            $attempts++;
        }
        // fallback: استخدام UUID إذا استنفدنا المحاولات
        if (User::where('username', $username)->exists()) {
            $username = 'user_' . \Illuminate\Support\Str::random(8);
        }

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->name,
            'username'  => $username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'admin',
        ]);

        $tenant->templates()->create(['name' => 'القالب الافتراضي', 'is_default' => true]);

        \App\Models\Warehouse::create([
            'tenant_id'  => $tenant->id,
            'name'       => 'المخزن الرئيسي',
            'is_default' => true,
            'is_active'  => true,
        ]);
        \App\Models\PaymentMethod::createDefaults($tenant->id);

        // إنشاء عميل نقدي افتراضي للبيع المباشر
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

        // منح صلاحيات admin للمستخدم الجديد
        // نستخدم assignPermissionDirectly بدل syncPermissions على الـ role المشتركة
        // لتجنب التأثير على admins المستأجرين الآخرين
        $allPerms = \Spatie\Permission\Models\Permission::all();
        $user->syncPermissions($allPerms);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
