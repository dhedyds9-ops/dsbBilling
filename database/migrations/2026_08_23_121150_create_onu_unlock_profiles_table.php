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
        Schema::create('onu_unlock_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('vendor');
            $table->string('model');
            $table->string('hardware_version')->nullable();
            $table->string('firmware_range')->nullable();
            $table->string('method', 50)->comment('CONFIGURATION, PARAMETER, PROFILE, FIRMWARE');
            $table->json('required_parameters')->nullable();
            $table->json('required_configuration')->nullable();
            $table->foreignId('acs_firmware_id')->nullable()->constrained('acs_firmwares')->nullOnDelete();
            $table->boolean('reboot_required')->default(false);
            $table->json('verification_rules')->nullable();
            $table->string('risk_level', 30)->default('HIGH')->comment('LOW, MEDIUM, HIGH, CRITICAL');
            $table->string('approval_policy', 30)->default('APPROVAL_REQUIRED')->comment('AUTO, APPROVAL_REQUIRED, ADMIN_ONLY');
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onu_unlock_profiles');
    }
};
