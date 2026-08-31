<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة invoice_item_id إلى invoice_return_items
 * لتتبع المرتجع بالبند الأصلي بدقة بدلاً من الاعتماد على product_id فقط
 * (منتج واحد قد يظهر أكثر من مرة في نفس الفاتورة بأسعار مختلفة)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_return_items', function (Blueprint $table) {
            $table->foreignId('invoice_item_id')
                ->nullable()
                ->after('return_id')
                ->constrained('invoice_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_return_items', function (Blueprint $table) {
            $table->dropForeign(['invoice_item_id']);
            $table->dropColumn('invoice_item_id');
        });
    }
};
