<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-02 / BUG-03 / DESIGN-01 Fix:
 * إضافة warehouse_id إلى جدول invoices لتتبع المستودع الأصلي
 * الذي خُصم منه المخزون عند إنشاء الفاتورة.
 *
 * قبل هذا الإصلاح كانت عمليات استعادة المخزون تستخدم المستودع
 * الافتراضي للمستخدم الحالي بدلاً من المستودع الأصلي.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // nullable لأن الفواتير القديمة ليس لها مستودع مسجّل
            $table->foreignId('warehouse_id')
                  ->nullable()
                  ->after('template_id')
                  ->constrained('warehouses')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};
