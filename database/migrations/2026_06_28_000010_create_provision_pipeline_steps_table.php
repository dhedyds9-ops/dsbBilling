<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provision_pipeline_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provision_pipeline_id')->constrained()->onDelete('cascade');
            $table->string('step_name');
            $table->string('step_type')->nullable();
            $table->integer('order')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('rollback_needed')->default(false);
            $table->timestamp('rollback_completed_at')->nullable();
            $table->json('payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provision_pipeline_steps');
    }
};
