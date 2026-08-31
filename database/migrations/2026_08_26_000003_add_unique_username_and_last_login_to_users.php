<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * إصلاح مشاكل جدول users:
 *  1. إضافة unique constraint على username (على مستوى كل النظام وليس tenant فقط)
 *     لأن الـ login يبحث بـ username بدون tenant_id
 *  2. التأكد من عدم وجود usernames مكررة قبل إضافة الـ constraint
 *  3. إضافة عمود last_login إن لم يكن موجوداً
 */
return new class extends Migration
{
    public function up(): void
    {
        // إصلاح التكرار في usernames قبل إضافة الـ constraint
        $duplicates = DB::table('users')
            ->select('username', DB::raw('COUNT(*) as count'))
            ->whereNotNull('username')
            ->groupBy('username')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $users = DB::table('users')
                ->where('username', $dup->username)
                ->orderBy('id')
                ->get();

            // الأول يحتفظ باسمه، الباقون يحصلون على suffix
            foreach ($users->skip(1) as $index => $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $user->username . '_' . ($index + 2)]);
            }
        }

        // إضافة unique index على username
        Schema::table('users', function (Blueprint $table) {
            // تأكد من أن العمود موجود ومن ثم أضف الـ index
            $table->string('username')->nullable()->unique()->change();
        });

        // إضافة last_login إن لم تكن موجودة
        if (!Schema::hasColumn('users', 'last_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login')->nullable()->after('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
        });
    }
};
