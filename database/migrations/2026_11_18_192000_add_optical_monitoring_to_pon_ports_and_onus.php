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
        if (Schema::hasTable('pon_ports')) {
            Schema::table('pon_ports', function (Blueprint $table) {
                if (!Schema::hasColumn('pon_ports', 'tx_power_dbm')) {
                    $table->decimal('tx_power_dbm', 5, 2)->nullable()->after('type');
                }
                if (!Schema::hasColumn('pon_ports', 'rx_power_dbm')) {
                    $table->decimal('rx_power_dbm', 5, 2)->nullable()->after('tx_power_dbm');
                }
                if (!Schema::hasColumn('pon_ports', 'sfp_class')) {
                    $table->string('sfp_class', 20)->nullable()->default('Class C+')->after('rx_power_dbm');
                }
            });
        }

        if (Schema::hasTable('onus')) {
            Schema::table('onus', function (Blueprint $table) {
                if (!Schema::hasColumn('onus', 'distance_meters')) {
                    $table->integer('distance_meters')->nullable()->after('temperature');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pon_ports')) {
            Schema::table('pon_ports', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('pon_ports', 'tx_power_dbm')) $cols[] = 'tx_power_dbm';
                if (Schema::hasColumn('pon_ports', 'rx_power_dbm')) $cols[] = 'rx_power_dbm';
                if (Schema::hasColumn('pon_ports', 'sfp_class')) $cols[] = 'sfp_class';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('onus')) {
            Schema::table('onus', function (Blueprint $table) {
                if (Schema::hasColumn('onus', 'distance_meters')) {
                    $table->dropColumn('distance_meters');
                }
            });
        }
    }
};
