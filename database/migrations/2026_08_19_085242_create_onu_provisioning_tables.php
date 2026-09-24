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
        Schema::create('provisioning_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service_type', 30)->comment('PPPOE, HOTSPOT, CUSTOM');
            $table->string('wan_mode', 30)->comment('PPPOE, DHCP, STATIC, BRIDGE');
            $table->integer('vlan_id')->nullable();
            $table->string('vlan_mode', 30)->nullable()->comment('TAGGED, UNTAGGED');
            $table->integer('vlan_priority')->nullable();
            $table->boolean('nat')->default(false);
            $table->boolean('dhcp_server')->default(false);
            $table->json('lan_mapping')->nullable()->comment('Array of LAN ports e.g. ["LAN1", "LAN2"]');
            $table->json('wifi_mapping')->nullable()->comment('Array of SSIDs e.g. ["SSID1"]');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('onu_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained('onus')->cascadeOnDelete();
            $table->json('capabilities')->nullable()->comment('wifi: true, 5ghz: true, etc');
            $table->timestamp('discovered_at')->nullable();
            $table->timestamps();

            $table->unique('onu_id');
        });

        Schema::create('onu_parameter_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained('onus')->cascadeOnDelete();
            $table->string('semantic_key')->index(); // e.g. 'wifi.ssid.1'
            $table->string('actual_path'); // e.g. 'InternetGatewayDevice.LANDevice.1...'
            $table->timestamps();

            $table->unique(['onu_id', 'semantic_key']);
        });

        Schema::create('onu_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained('onus')->cascadeOnDelete();
            $table->json('desired_state')->nullable();
            $table->json('actual_state')->nullable();
            $table->string('state_hash')->nullable();
            $table->string('actual_hash')->nullable();
            $table->string('drift_status', 30)->default('UNKNOWN')->comment('IN_SYNC, DRIFT, UNKNOWN');
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->unique('onu_id');
        });

        Schema::create('onu_configuration_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained('onus')->cascadeOnDelete();
            $table->foreignId('customer_service_id')->nullable()->constrained('customer_services')->nullOnDelete();
            $table->string('status', 30)->default('PENDING')->comment('PENDING, RUNNING, WAITING_DEVICE, APPLYING, VERIFYING, SUCCESS, FAILED, DRIFT, CANCELLED');
            $table->string('type', 50)->comment('WIFI, WAN, BRIDGE, REBOOT, FACTORY_RESET, FULL_PROVISION');
            $table->json('payload')->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onu_configuration_jobs');
        Schema::dropIfExists('onu_states');
        Schema::dropIfExists('onu_parameter_mappings');
        Schema::dropIfExists('onu_capabilities');
        Schema::dropIfExists('provisioning_profiles');
    }
};
