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
        ]);

        $tenant = Tenant::create([
            'company_name' => $request->company_name,
            'subdomain'    => strtolower($request->subdomain),
            'email'        => $request->email,
            'status'       => 'trial',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->name,
            'username'  => $request->username ?? strtolower(str_replace(' ', '_', $request->name)) . rand(100,999),
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'admin',
        ]);

        $tenant->templates()->create(['name' => 'القالب الافتراضي', 'is_default' => true]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        return redirect()->route('login');
    }
}
