<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('price_monthly_usd', 8, 2)->default(0)->after('price_yearly');
            $table->decimal('price_yearly_usd',  8, 2)->default(0)->after('price_monthly_usd');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['price_monthly_usd', 'price_yearly_usd']);
        });
    }
};
