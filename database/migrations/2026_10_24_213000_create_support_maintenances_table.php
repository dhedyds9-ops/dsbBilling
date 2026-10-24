<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('support_maintenances')) {
            Schema::create('support_maintenances', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type', 50)->default('preventive'); // preventive, corrective, predictive, emergency
                $table->string('status', 50)->default('scheduled'); // scheduled, in_progress, completed, cancelled
                $table->string('priority', 50)->default('medium'); // low, medium, high, critical
                $table->string('device_type', 50)->nullable(); // router, olt, onu, odp, odc, pop, tower, server, other
                $table->string('device_name')->nullable();
                $table->string('location_name')->nullable();
                $table->date('scheduled_date')->nullable();
                $table->string('scheduled_time', 20)->nullable()->default('09:00');
                $table->dateTime('completed_at')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->text('notes')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_maintenances');
    }
};
