<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use App\Models\SubscriptionPlan;
use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $feature = null)
    {
        $tenant = auth()->user()->tenant;
        $plan   = SubscriptionPlan::where('slug', $tenant->subscription_plan)->first();

        if (!$plan) return $next($request);

        // التحقق من ميزة محددة
        if ($feature && !$plan->canDo($feature)) {
            return back()->with('error', 'هذه الميزة غير متاحة في خطتك الحالية. يرجى الترقية.');
        }

        // التحقق من حد الفواتير الشهرية عند إنشاء فاتورة
        if ($request->routeIs('invoices.store') && !$plan->isUnlimited('max_invoices_per_month')) {
            $monthCount = Invoice::where('tenant_id', $tenant->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($monthCount >= $plan->max_invoices_per_month) {
                return back()->with('error', "لقد وصلت للحد الأقصى ({$plan->max_invoices_per_month} فاتورة/شهر). يرجى الترقية.");
            }
        }

        return $next($request);
    }
}
