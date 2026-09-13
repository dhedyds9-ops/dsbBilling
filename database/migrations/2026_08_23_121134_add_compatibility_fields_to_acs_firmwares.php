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
        Schema::table('acs_firmwares', function (Blueprint $table) {
            $table->string('product_class')->nullable()->after('model');
            $table->string('hardware_version')->nullable()->after('product_class');
            $table->string('compatible_from_version')->nullable()->after('version');
            $table->string('target_version')->nullable()->after('compatible_from_version');
            $table->boolean('is_upgrade')->default(true)->after('target_version');
            $table->boolean('is_downgrade')->default(false)->after('is_upgrade');
            $table->string('risk_level', 30)->default('LOW')->after('is_downgrade')->comment('LOW, MEDIUM, HIGH, CRITICAL');
            $table->string('upgrade_method', 50)->default('TR069')->after('risk_level')->comment('TR069, HTTP, FTP');
            $table->string('approval_policy', 30)->default('APPROVAL_REQUIRED')->after('upgrade_method')->comment('AUTO, APPROVAL_REQUIRED, ADMIN_ONLY');
            $table->string('checksum_sha256')->nullable()->after('checksum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acs_firmwares', function (Blueprint $table) {
            $table->dropColumn([
                'product_class',
                'hardware_version',
                'compatible_from_version',
                'target_version',
                'is_upgrade',
                'is_downgrade',
                'risk_level',
                'upgrade_method',
                'approval_policy',
                'checksum_sha256'
            ]);
        });
    }
};
