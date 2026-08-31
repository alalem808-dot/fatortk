<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إزالة عمود movement_type الزائد من stock_movements
 * العمود "type" يؤدي نفس الغرض (in/out/adjustment) وهو المستخدم في الكود.
 * movement_type موجود لكنه لم يُستخدم أبداً في أي query أو model.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('stock_movements', 'movement_type')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('movement_type');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('stock_movements', 'movement_type')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('movement_type')->nullable()->after('type');
            });
        }
    }
};
