<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

return new class extends Migration
{
    public function up(): void
    {
        // ربط المنتجات الموجودة بالمخزن الافتراضي لكل tenant
        $tenants = DB::table('tenants')->pluck('id');

        foreach ($tenants as $tenantId) {
            // إنشاء مخزن افتراضي إن لم يوجد
            $warehouse = DB::table('warehouses')
                ->where('tenant_id', $tenantId)
                ->where('is_default', true)
                ->first();

            if (!$warehouse) {
                $warehouseId = DB::table('warehouses')->insertGetId([
                    'tenant_id'  => $tenantId,
                    'name'       => 'المخزن الرئيسي',
                    'is_default' => true,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $warehouseId = $warehouse->id;
            }

            // ربط كل منتجات هذا الـ tenant بالمخزن الافتراضي
            $products = DB::table('products')->where('tenant_id', $tenantId)->get();

            foreach ($products as $product) {
                $exists = DB::table('warehouse_stocks')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $product->id)
                    ->exists();

                if (!$exists) {
                    DB::table('warehouse_stocks')->insert([
                        'warehouse_id' => $warehouseId,
                        'product_id'   => $product->id,
                        'quantity'     => $product->stock_quantity,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void {}
};
