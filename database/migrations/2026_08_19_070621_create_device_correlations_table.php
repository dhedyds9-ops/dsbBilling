<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_correlations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained('onus')->cascadeOnDelete();
            $table->string('genieacs_device_id')->nullable()->index();
            $table->foreignId('customer_service_id')->nullable()->constrained('customer_services')->nullOnDelete();
            $table->string('match_method')->nullable();
            $table->integer('confidence_score')->default(0);
            $table->string('matched_by')->default('auto');
            $table->timestamp('matched_at')->nullable();
            $table->string('status', 30)->default('candidate')->comment('auto_bind, candidate, rejected, manual_bind');
            $table->timestamps();

            // Unique index to prevent duplicate correlations for the same pair
            $table->unique(['onu_id', 'genieacs_device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_correlations');
    }
};
