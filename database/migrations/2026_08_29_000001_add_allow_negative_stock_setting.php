<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // إضافة إعداد allow_negative_stock لكل tenant موجود (القيمة الافتراضية: غير مسموح)
        $tenantIds = DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            $exists = DB::table('settings')
                ->where('tenant_id', $tenantId)
                ->where('key', 'allow_negative_stock')
                ->exists();

            if (!$exists) {
                DB::table('settings')->insert([
                    'tenant_id'  => $tenantId,
                    'key'        => 'allow_negative_stock',
                    'value'      => '0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'allow_negative_stock')->delete();
    }
};
