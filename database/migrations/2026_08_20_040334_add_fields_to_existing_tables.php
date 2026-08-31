<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // إضافة supplier_id و exchange_rate و currency لـ purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('tenant_id')->constrained('suppliers')->nullOnDelete();
            $table->string('currency', 10)->default('SDG')->after('total');
            $table->decimal('exchange_rate', 15, 6)->default(1)->after('currency');
        });

        // إضافة exchange_rate لـ invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('exchange_rate', 15, 6)->default(1)->after('currency');
        });

        // تعديل stock_movements لدعم أنواع جديدة
        // نضيف عمود type_extra بدلاً من تعديل الـ enum مباشرة لتجنب مشاكل التوافق
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('movement_type')->nullable()->after('type')
                ->comment('return_in, return_out, stocktaking - تفصيل إضافي');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'currency', 'exchange_rate']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('exchange_rate');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('movement_type');
        });
    }
};
