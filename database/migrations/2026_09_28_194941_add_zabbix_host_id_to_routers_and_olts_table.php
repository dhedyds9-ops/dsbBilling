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
            $table->string('zabbix_host_id')->nullable()->after('password')->comment('Mapped Zabbix Host ID for monitoring');
        });

        Schema::table('olts', function (Blueprint $table) {
            $table->string('zabbix_host_id')->nullable()->after('password')->comment('Mapped Zabbix Host ID for monitoring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn('zabbix_host_id');
        });

        Schema::table('olts', function (Blueprint $table) {
            $table->dropColumn('zabbix_host_id');
        });
    }
};
