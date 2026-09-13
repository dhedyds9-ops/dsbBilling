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
        Schema::table('router_monitoring_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('rx_bps')->default(0)->after('uptime');
            $table->unsignedBigInteger('tx_bps')->default(0)->after('rx_bps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('router_monitoring_logs', function (Blueprint $table) {
            $table->dropColumn(['rx_bps', 'tx_bps']);
        });
    }
};
