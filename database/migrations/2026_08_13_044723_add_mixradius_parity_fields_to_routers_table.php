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
        Schema::table('routers', function (Blueprint $table) {
            $table->string('vpn_ip')->nullable()->after('ip_address')->comment('Fallback IP for Auto-Recovery');
            $table->boolean('has_config_drift')->default(false)->after('status')->comment('True if Mikrotik config diverges from DB');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['vpn_ip', 'has_config_drift']);
        });
    }
};
