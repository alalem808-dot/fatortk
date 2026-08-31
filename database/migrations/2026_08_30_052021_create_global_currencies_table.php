<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('global_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('symbol', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // إدراج عملات شائعة مبدئية
        DB::table('global_currencies')->insert([
            ['code' => 'SDG', 'name' => 'جنيه سوداني',     'symbol' => 'ج.س', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'USD', 'name' => 'دولار أمريكي',    'symbol' => '$',   'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SAR', 'name' => 'ريال سعودي',      'symbol' => 'ر.س', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'AED', 'name' => 'درهم إماراتي',    'symbol' => 'د.إ', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EGP', 'name' => 'جنيه مصري',       'symbol' => 'ج.م', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EUR', 'name' => 'يورو',             'symbol' => '€',   'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('global_currencies');
    }
};
