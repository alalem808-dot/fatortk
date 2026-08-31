<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('plan_slug');           // e.g. 'pro'
            $table->string('plan_name');           // e.g. 'احترافي'
            $table->decimal('amount_usd', 10, 2); // $600
            $table->string('period', 20)->default('yearly'); // yearly|monthly
            $table->date('paid_at');
            $table->date('expires_at');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
