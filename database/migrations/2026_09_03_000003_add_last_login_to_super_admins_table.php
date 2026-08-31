<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DESIGN-09 Fix: إضافة last_login لجدول super_admins
 * لتتبع آخر دخول للحساب الأعلى صلاحية في النظام
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            $table->timestamp('last_login')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            $table->dropColumn('last_login');
        });
    }
};
