<?php

namespace Database\Seeders;

use App\Models\PricingSetting;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    public function run(): void
    {
        PricingSetting::truncate();

        $plans = [
            [
                'currency' => 'USD',
                'plan_name' => 'أساسي',
                'monthly_price' => 35,
                'yearly_price' => 400,
                'max_invoices' => 100,
                'max_customers' => 200,
                'max_products' => 300,
                'max_users' => 3,
                'features' => ['تصدير PDF', 'إرسال واتساب', 'إدارة المخزون', '3 قوالب'],
                'is_active' => true,
            ],
            [
                'currency' => 'USD',
                'plan_name' => 'احترافي',
                'monthly_price' => 60,
                'yearly_price' => 600,
                'max_invoices' => 999999,
                'max_customers' => 999999,
                'max_products' => 999999,
                'max_users' => 10,
                'features' => ['كل ميزات الأساسي', 'تصدير Excel', 'إرسال بريد', 'قوالب غير محدودة', 'API Access', 'تقارير متقدمة'],
                'is_active' => true,
            ],
            [
                'currency' => 'USD',
                'plan_name' => 'مؤسسي',
                'monthly_price' => 150,
                'yearly_price' => 1500,
                'max_invoices' => 999999,
                'max_customers' => 999999,
                'max_products' => 999999,
                'max_users' => 999999,
                'features' => ['كل ميزات الاحترافي', 'دعم مخصص', 'تدريب وإعداد', 'نسخ احتياطي فوري', 'SLA مضمون'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            PricingSetting::create($plan);
        }
    }
}
