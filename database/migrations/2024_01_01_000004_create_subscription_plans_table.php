<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->integer('price_monthly')->default(0);
            $table->integer('price_yearly')->default(0);
            $table->integer('max_invoices_per_month')->default(10);
            $table->integer('max_customers')->default(20);
            $table->integer('max_products')->default(30);
            $table->integer('max_users')->default(1);
            $table->boolean('excel_export')->default(false);
            $table->boolean('email_send')->default(false);
            $table->boolean('stock_management')->default(false);
            $table->boolean('custom_templates')->default(false);
            $table->integer('max_templates')->default(1);
            $table->boolean('api_access')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // إدراج الخطط
        DB::table('subscription_plans')->insert([
            [
                'slug' => 'free', 'name' => 'مجاني',
                'price_monthly' => 0, 'price_yearly' => 0,
                'max_invoices_per_month' => 10, 'max_customers' => 20, 'max_products' => 30,
                'max_users' => 1, 'excel_export' => 0, 'email_send' => 0,
                'stock_management' => 0, 'custom_templates' => 0, 'max_templates' => 1,
                'api_access' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'basic', 'name' => 'أساسي',
                'price_monthly' => 2500, 'price_yearly' => 25000,
                'max_invoices_per_month' => 100, 'max_customers' => 200, 'max_products' => 300,
                'max_users' => 3, 'excel_export' => 1, 'email_send' => 1,
                'stock_management' => 1, 'custom_templates' => 1, 'max_templates' => 3,
                'api_access' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'pro', 'name' => 'احترافي',
                'price_monthly' => 6000, 'price_yearly' => 60000,
                'max_invoices_per_month' => -1, 'max_customers' => -1, 'max_products' => -1,
                'max_users' => 10, 'excel_export' => 1, 'email_send' => 1,
                'stock_management' => 1, 'custom_templates' => 1, 'max_templates' => -1,
                'api_access' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'enterprise', 'name' => 'مؤسسي',
                'price_monthly' => 15000, 'price_yearly' => 150000,
                'max_invoices_per_month' => -1, 'max_customers' => -1, 'max_products' => -1,
                'max_users' => -1, 'excel_export' => 1, 'email_send' => 1,
                'stock_management' => 1, 'custom_templates' => 1, 'max_templates' => -1,
                'api_access' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
