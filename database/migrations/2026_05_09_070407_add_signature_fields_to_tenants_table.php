<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('signer_name')->nullable()->after('tax_number');
            $table->string('signer_title')->nullable()->after('signer_name');
            $table->string('stamp_image')->nullable()->after('signer_title');
            $table->text('signature_image')->nullable()->after('stamp_image');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['signer_name', 'signer_title', 'stamp_image', 'signature_image']);
        });
    }
};
