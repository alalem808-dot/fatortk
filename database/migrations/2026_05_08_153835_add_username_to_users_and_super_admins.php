<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        Schema::table('super_admins', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        // تعيين username افتراضي للمستخدمين الحاليين
        DB::statement("UPDATE users SET username = CONCAT('user', id) WHERE username IS NULL");
        DB::statement("UPDATE super_admins SET username = 'superadmin' WHERE username IS NULL");
    }

    public function down(): void
    {
        Schema::table('users', fn($t) => $t->dropColumn('username'));
        Schema::table('super_admins', fn($t) => $t->dropColumn('username'));
    }
};
