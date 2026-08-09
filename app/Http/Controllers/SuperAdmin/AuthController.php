<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('super_admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $login = $request->login;
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $admin = \App\Models\SuperAdmin::where($field, $login)->first();

        if (!$admin || !\Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['login' => 'بيانات الدخول غير صحيحة.'])->withInput();
        }

        Auth::guard('super_admin')->login($admin, $request->boolean('remember'));
        return redirect()->route('super_admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('super_admin')->logout();
        $request->session()->invalidate();
        return redirect()->route('super_admin.login');
    }
}
