<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-04 Fix:
 * إضافة returned_amount إلى جدول purchases
 * بدلاً من تعديل total/subtotal عند إنشاء مرتجع،
 * نتتبع قيمة المرتجعات بعمود منفصل ونحسب الصافي منه.
 *
 * الصافي = total - returned_amount
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('returned_amount', 12, 2)
                  ->default(0)
                  ->after('paid_amount')
                  ->comment('إجمالي المبالغ المُرجعة — لا يُعدَّل total الأصلي أبداً');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('returned_amount');
        });
    }
};
