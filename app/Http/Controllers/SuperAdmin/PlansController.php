<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::all();
        return view('super_admin.plans.index', compact('plans'));
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('super_admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'name'                    => 'required|string',
            'price_monthly'           => 'required|integer|min:0',
            'price_yearly'            => 'required|integer|min:0',
            'price_monthly_usd'       => 'required|numeric|min:0',
            'price_yearly_usd'        => 'required|numeric|min:0',
            'max_invoices_per_month'  => 'required|integer|min:-1',
            'max_customers'           => 'required|integer|min:-1',
            'max_products'            => 'required|integer|min:-1',
            'max_users'               => 'required|integer|min:-1',
            'max_templates'           => 'required|integer|min:-1',
        ]);

        $plan->update([
            'name'                   => $request->name,
            'price_monthly'          => $request->price_monthly,
            'price_yearly'           => $request->price_yearly,
            'price_monthly_usd'      => $request->price_monthly_usd,
            'price_yearly_usd'       => $request->price_yearly_usd,
            'max_invoices_per_month' => $request->max_invoices_per_month,
            'max_customers'          => $request->max_customers,
            'max_products'           => $request->max_products,
            'max_users'              => $request->max_users,
            'max_templates'          => $request->max_templates,
            'excel_export'           => $request->boolean('excel_export'),
            'email_send'             => $request->boolean('email_send'),
            'stock_management'       => $request->boolean('stock_management'),
            'custom_templates'       => $request->boolean('custom_templates'),
            'api_access'             => $request->boolean('api_access'),
            'is_active'              => $request->boolean('is_active'),
        ]);

        return redirect()->route('super_admin.plans')->with('success', 'تم تحديث الخطة بنجاح.');
    }
}
