<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة purchase_item_id إلى purchase_return_items
 * لتتبع المرتجع بالبند الأصلي بدقة (نفس منطق invoice_return_items)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->foreignId('purchase_item_id')
                ->nullable()
                ->after('purchase_return_id')
                ->constrained('purchase_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_item_id']);
            $table->dropColumn('purchase_item_id');
        });
    }
};
