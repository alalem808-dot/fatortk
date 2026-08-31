<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tenantIds = DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            $exists = DB::table('settings')
                ->where('tenant_id', $tenantId)
                ->where('key', 'quick_sale_customer_id')
                ->exists();

            if ($exists) continue;

            // البحث عن عميل نقدي موجود
            $cashCustomer = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('name', 'عميل نقدي')
                ->first();

            if (!$cashCustomer) {
                $cashCustomerId = DB::table('customers')->insertGetId([
                    'tenant_id'  => $tenantId,
                    'name'       => 'عميل نقدي',
                    'notes'      => 'عميل افتراضي للمبيعات المباشرة',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $cashCustomerId = $cashCustomer->id;
            }

            DB::table('settings')->insert([
                'tenant_id'  => $tenantId,
                'key'        => 'quick_sale_customer_id',
                'value'      => $cashCustomerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // إضافة صلاحية quick_sale.access إن لم تكن موجودة
        $permExists = DB::table('permissions')
            ->where('name', 'quick_sale.access')
            ->where('guard_name', 'web')
            ->exists();

        if (!$permExists) {
            $permId = DB::table('permissions')->insertGetId([
                'name'       => 'quick_sale.access',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // منح الصلاحية لـ admin و sales roles
            $roles = DB::table('roles')
                ->whereIn('name', ['admin', 'sales'])
                ->where('guard_name', 'web')
                ->pluck('id');

            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permId,
                    'role_id'       => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'quick_sale_customer_id')->delete();
        DB::table('permissions')->where('name', 'quick_sale.access')->delete();
    }
};
