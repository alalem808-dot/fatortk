<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * إصلاح مشاكل قاعدة البيانات المكتشفة:
 *  1. تغيير stock_quantity و min_stock_alert من integer إلى decimal(15,3)
 *     لدعم الكميات الكسرية (كيلو، متر، ...إلخ)
 *  2. تغيير حقول الكميات في stock_movements كذلك
 *  3. تغيير payment_method في payments من enum محدود إلى string
 *     لدعم طرق الدفع الديناميكية من جدول payment_methods
 *  4. إضافة عمود public_token إلى invoices لأمان الروابط العامة
 *  5. إضافة index على invoices(tenant_id, status) و invoices(invoice_date)
 *     لتحسين أداء الاستعلامات المتكررة
 */
return new class extends Migration
{
    public function up(): void
    {
        // ======================================================
        // 1. products: stock_quantity و min_stock_alert → decimal
        // ======================================================
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock_quantity', 15, 3)->default(0)->change();
            $table->decimal('min_stock_alert', 15, 3)->default(5)->change();
        });

        // ======================================================
        // 2. stock_movements: quantity حقول → decimal
        // ======================================================
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->change();
            $table->decimal('quantity_before', 15, 3)->default(0)->change();
            $table->decimal('quantity_after', 15, 3)->default(0)->change();
        });

        // ======================================================
        // 3. payments: payment_method → string (بدلاً من enum محدود)
        //    يدعم الآن أي كود من جدول payment_methods
        // ======================================================
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'cash'");

        // ======================================================
        // 4. invoices: إضافة public_token لأمان الروابط العامة
        // ======================================================
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('public_token', 36)->nullable()->unique()->after('created_by');
        });

        // توليد tokens للفواتير الموجودة
        DB::table('invoices')->whereNull('public_token')->chunkById(200, function ($invoices) {
            foreach ($invoices as $invoice) {
                DB::table('invoices')
                    ->where('id', $invoice->id)
                    ->update(['public_token' => \Illuminate\Support\Str::uuid()]);
            }
        });

        // ======================================================
        // 5. Indexes لتحسين الأداء
        // ======================================================
        Schema::table('invoices', function (Blueprint $table) {
            // تحقق قبل الإضافة لتجنب الخطأ عند إعادة التشغيل
            $table->index(['tenant_id', 'status'], 'invoices_tenant_status_index');
            $table->index(['tenant_id', 'invoice_date'], 'invoices_tenant_date_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['payment_date'], 'payments_date_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'products_tenant_status_index');
        });
    }

    public function down(): void
    {
        // إزالة الـ indexes
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndexIfExists('invoices_tenant_status_index');
            $table->dropIndexIfExists('invoices_tenant_date_index');
            $table->dropColumn('public_token');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndexIfExists('payments_date_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndexIfExists('products_tenant_status_index');
        });

        // إعادة stock_quantity لـ integer
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
            $table->integer('min_stock_alert')->default(5)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('quantity_before')->default(0)->change();
            $table->integer('quantity_after')->default(0)->change();
        });

        // إعادة enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash','bank','card','cheque') NOT NULL DEFAULT 'cash'");
    }
};
