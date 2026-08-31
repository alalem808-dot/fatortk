<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * توسيع enum role في جدول users ليشمل كل الأدوار المستخدمة في الكود
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','employee','staff','accountant','sales','purchasing','warehouse') NOT NULL DEFAULT 'employee'");
    }

    public function down(): void
    {
        // إعادة للقيم الأصلية (ملاحظة: إذا كانت هناك سجلات بالقيم الجديدة ستُحذف)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','employee') NOT NULL DEFAULT 'employee'");
    }
};
