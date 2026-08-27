<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('onus', function (Blueprint $table) {
            $table->unsignedBigInteger('odp_id')->nullable()->after('splitter_id');
            $table->unsignedInteger('onu_id_on_olt')->nullable()->after('pon_port');
            $table->string('profile_name', 100)->nullable()->after('onu_id_on_olt');
            $table->string('wifi_ssid', 64)->nullable()->after('profile_name');
            $table->string('wifi_password', 128)->nullable()->after('wifi_ssid');
            $table->string('admin_password', 128)->nullable()->after('wifi_password');
            $table->decimal('rx_power_dbm', 6, 2)->nullable()->after('admin_password');
            $table->decimal('tx_power_dbm', 6, 2)->nullable()->after('rx_power_dbm');
            $table->decimal('snr_db', 5, 2)->nullable()->after('tx_power_dbm');
            $table->decimal('temperature', 6, 2)->nullable()->after('snr_db');
            $table->string('firmware_version', 100)->nullable()->after('temperature');
            $table->string('hardware_version', 100)->nullable()->after('firmware_version');
            $table->timestamp('last_seen_at')->nullable()->after('hardware_version');
            $table->timestamp('provisioned_at')->nullable()->after('last_seen_at');
            $table->string('provision_status', 50)->nullable()->default('pending')->after('status');
            $table->json('attributes')->nullable()->after('provision_status');

            $table->index('odp_id');
            $table->index('provision_status');
        });
    }

    public function down(): void
    {
        Schema::table('onus', function (Blueprint $table) {
            $cols = ['odp_id','onu_id_on_olt','profile_name','wifi_ssid','wifi_password','admin_password','rx_power_dbm','tx_power_dbm','snr_db','temperature','firmware_version','hardware_version','last_seen_at','provisioned_at','provision_status','attributes'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('onus', $c)) {
                    $table->dropColumn($c);
                }
            }
            $table->dropIndex(['odp_id']);
            $table->dropIndex(['provision_status']);
        });
    }
};
