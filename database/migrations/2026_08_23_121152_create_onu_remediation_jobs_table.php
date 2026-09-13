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
        Schema::create('onu_remediation_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('onu_id')->constrained('onus')->cascadeOnDelete();
            $table->string('type', 50)->comment('CONFIG, UNLOCK, FIRMWARE_UPGRADE, FIRMWARE_DOWNGRADE, FACTORY_RESET');
            $table->string('status', 50)->default('PENDING')->comment('PENDING, VALIDATING, WAITING_APPROVAL, RUNNING, REBOOTING, REDISCOVERING, VERIFYING, COMPLETED, FAILED, BLOCKED, CANCELLED');
            $table->string('target_capability')->nullable()->comment('The capability being fixed, e.g. BRIDGE, WAN');
            $table->foreignId('acs_configuration_profile_id')->nullable()->constrained('acs_configuration_profiles')->nullOnDelete();
            $table->foreignId('onu_unlock_profile_id')->nullable()->constrained('onu_unlock_profiles')->nullOnDelete();
            $table->foreignId('acs_firmware_id')->nullable()->constrained('acs_firmwares')->nullOnDelete();
            $table->json('dry_run_results')->nullable();
            $table->json('validation_results')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onu_remediation_jobs');
    }
};
