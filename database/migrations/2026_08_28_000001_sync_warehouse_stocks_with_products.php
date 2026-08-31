<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * مزامنة warehouse_stocks مع products.stock_quantity
 * للمنتجات التي لها كمية في products لكن ليس لها سجل صحيح في warehouse_stocks
 * يحدث هذا عند إضافة منتج قبل تفعيل نظام المخازن
 */
return new class extends Migration
{
    public function up(): void
    {
        // جلب المنتجات التي بها تفاوت
        $products = DB::table('products as p')
            ->join('warehouses as w', function($join) {
                $join->on('w.tenant_id', '=', 'p.tenant_id')
                     ->where('w.is_default', 1);
            })
            ->leftJoin('warehouse_stocks as ws', function($join) {
                $join->on('ws.product_id', '=', 'p.id')
                     ->on('ws.warehouse_id', '=', 'w.id');
            })
            ->where('p.stock_quantity', '>', 0)
            ->where(function($q) {
                $q->whereNull('ws.id')->orWhere('ws.quantity', '=', 0);
            })
            ->select('p.id as product_id', 'w.id as warehouse_id', 'p.stock_quantity')
            ->get();

        foreach ($products as $p) {
            DB::table('warehouse_stocks')->updateOrInsert(
                ['product_id' => $p->product_id, 'warehouse_id' => $p->warehouse_id],
                ['quantity' => $p->stock_quantity, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // لا يوجد rollback لعملية مزامنة
    }
};
