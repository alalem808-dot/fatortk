<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $tenant = Auth::user()->tenant;

            // حساب موقوف
            if (!$tenant || $tenant->status === 'suspended') {
                if (!$request->routeIs('logout')) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')
                        ->withErrors(['error' => 'حسابك موقوف، تواصل مع الدعم.']);
                }
            }

            // انتهاء الاشتراك — نُبلغ بدون logout قسري
            if ($tenant && $tenant->subscription_expires_at && $tenant->subscription_expires_at->isPast()) {
                $allowedRoutes = ['subscription.expired', 'logout', 'invoices.public'];
                if (!$request->routeIs($allowedRoutes)) {
                    return redirect()->route('subscription.expired');
                }
            }

            app()->instance('tenant', $tenant);
            view()->share('currentTenant', $tenant);

            // مشاركة مخازن المستخدم مع كل الـ views
            $user = Auth::user();
            if ($user->isAdmin()) {
                $userWarehouses = \App\Models\Warehouse::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->get();
            } else {
                $assignedIds = $user->warehouses()->pluck('warehouses.id')->toArray();
                if (empty($assignedIds)) {
                    // لم يُحدد مخازن = يصل لكل المخازن النشطة
                    $userWarehouses = \App\Models\Warehouse::where('tenant_id', $tenant->id)
                        ->where('is_active', true)
                        ->get();
                } else {
                    $userWarehouses = \App\Models\Warehouse::where('tenant_id', $tenant->id)
                        ->whereIn('id', $assignedIds)
                        ->where('is_active', true)
                        ->get();
                }
            }
            view()->share('userWarehouses', $userWarehouses);
        }

        return $next($request);
    }
}
