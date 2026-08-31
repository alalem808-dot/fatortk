<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();   // وصف للحقل في لوحة التحكم
            $table->timestamps();
        });

        // القيم الافتراضية
        DB::table('platform_settings')->insert([
            ['key' => 'whatsapp_number',      'value' => '2499100868681', 'label' => 'رقم واتساب الدعم',           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'whatsapp_subscribe_msg','value' => 'مرحباً، أريد الاشتراك في فاتورتك',  'label' => 'رسالة واتساب الاشتراك',     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'whatsapp_renew_msg',   'value' => 'مرحباً، أريد تجديد اشتراكي في فاتورتك', 'label' => 'رسالة واتساب التجديد',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'whatsapp_register_msg','value' => 'مرحباً، أريد إنشاء حساب في فاتورتك', 'label' => 'رسالة واتساب إنشاء حساب', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'platform_name',        'value' => 'فاتورتك',      'label' => 'اسم المنصة',                 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_email',        'value' => 'support@fatortk.com', 'label' => 'البريد الإلكتروني للدعم', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
