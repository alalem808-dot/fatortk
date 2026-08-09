<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $tenant = Auth::user()->tenant;

            if (!$tenant || $tenant->status === 'suspended') {
                Auth::logout();
                return redirect()->route('login')->withErrors(['حسابك موقوف، تواصل مع الدعم.']);
            }

            app()->instance('tenant', $tenant);
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}
