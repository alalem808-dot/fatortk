<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // إضافة partially_paid و returned لـ enum status في invoices
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','sent','paid','partially_paid','overdue','cancelled','returned') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
