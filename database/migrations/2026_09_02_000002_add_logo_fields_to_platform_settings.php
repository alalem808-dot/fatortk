<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // إضافة مفاتيح الصور للإعدادات
        $keys = [
            ['key' => 'platform_logo',       'value' => null, 'label' => 'لوجو المنصة الرئيسي'],
            ['key' => 'platform_favicon',     'value' => null, 'label' => 'الصورة المصغرة (Favicon)'],
            ['key' => 'login_logo',           'value' => null, 'label' => 'لوجو صفحة تسجيل الدخول'],
        ];

        foreach ($keys as $row) {
            DB::table('platform_settings')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('platform_settings')
            ->whereIn('key', ['platform_logo', 'platform_favicon', 'login_logo'])
            ->delete();
    }
};
