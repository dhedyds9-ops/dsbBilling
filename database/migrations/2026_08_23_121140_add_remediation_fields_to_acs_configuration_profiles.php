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
        Schema::table('acs_configuration_profiles', function (Blueprint $table) {
            $table->string('hardware_version')->nullable()->after('model');
            $table->string('firmware_range')->nullable()->after('hardware_version');
            $table->boolean('reboot_required')->default(false)->after('config');
            $table->json('verification_rules')->nullable()->after('reboot_required');
            $table->string('approval_policy', 30)->default('AUTO')->after('status')->comment('AUTO, APPROVAL_REQUIRED');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acs_configuration_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'hardware_version',
                'firmware_range',
                'reboot_required',
                'verification_rules',
                'approval_policy'
            ]);
        });
    }
};
