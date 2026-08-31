<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // إضافة صلاحية pos.access إن لم تكن موجودة
        $exists = DB::table('permissions')
            ->where('name', 'pos.access')
            ->where('guard_name', 'web')
            ->exists();

        if (!$exists) {
            $permId = DB::table('permissions')->insertGetId([
                'name'       => 'pos.access',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // منح الصلاحية لكل المستخدمين من نوع admin
            $admins = DB::table('users')->where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $hasIt = DB::table('model_has_permissions')
                    ->where('permission_id', $permId)
                    ->where('model_id', $admin->id)
                    ->where('model_type', 'App\\Models\\User')
                    ->exists();

                if (!$hasIt) {
                    DB::table('model_has_permissions')->insert([
                        'permission_id' => $permId,
                        'model_type'    => 'App\\Models\\User',
                        'model_id'      => $admin->id,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $permId = DB::table('permissions')
            ->where('name', 'pos.access')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permId) {
            DB::table('model_has_permissions')->where('permission_id', $permId)->delete();
            DB::table('permissions')->where('id', $permId)->delete();
        }
    }
};
