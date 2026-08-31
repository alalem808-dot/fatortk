<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type')->nullable();  // e.g. 'tenant'
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('action');                    // e.g. 'plan_changed', 'status_changed', 'created', 'deleted'
            $table->text('description')->nullable();
            $table->json('changes')->nullable();          // before/after JSON
            $table->string('performed_by')->nullable();  // super_admin name
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
