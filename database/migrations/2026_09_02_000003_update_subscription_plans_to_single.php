<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // تعطيل الخطط القديمة (free, basic, pro, enterprise)
        DB::table('subscription_plans')
            ->whereIn('slug', ['free', 'basic', 'enterprise'])
            ->update(['is_active' => false]);

        // تحديث خطة pro لتصبح الخطة الموحدة $600/year
        DB::table('subscription_plans')
            ->where('slug', 'pro')
            ->update([
                'name'                   => 'الاشتراك السنوي',
                'price_monthly'          => 0,
                'price_yearly'           => 0,
                'price_monthly_usd'      => 0,
                'price_yearly_usd'       => 600,
                'max_invoices_per_month' => -1,
                'max_customers'          => -1,
                'max_products'           => -1,
                'max_users'              => -1,
                'max_templates'          => -1,
                'excel_export'           => true,
                'email_send'             => true,
                'stock_management'       => true,
                'custom_templates'       => true,
                'api_access'             => true,
                'is_active'              => true,
                'updated_at'             => now(),
            ]);

        // تحديث جميع المشتركين ليكونوا على خطة pro (الموحدة)
        DB::table('tenants')
            ->whereIn('subscription_plan', ['free', 'basic', 'enterprise'])
            ->update(['subscription_plan' => 'pro']);
    }

    public function down(): void
    {
        // إعادة تفعيل الخطط القديمة
        DB::table('subscription_plans')
            ->whereIn('slug', ['free', 'basic', 'enterprise'])
            ->update(['is_active' => true]);
    }
};
