<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->string('enable_secret', 100)->nullable()->after('password');
            $table->unsignedSmallInteger('pon_port_count')->nullable()->after('port_count');
            $table->unsignedInteger('onu_capacity')->nullable()->after('active_port_count');
            $table->unsignedInteger('onu_active_count')->nullable()->after('onu_capacity');
            $table->string('snmp_version', 10)->nullable()->after('onu_active_count');
            $table->string('snmp_community_read', 100)->nullable()->after('snmp_version');
            $table->string('snmp_community_write', 100)->nullable()->after('snmp_community_read');
            $table->unsignedSmallInteger('cli_port')->nullable()->after('snmp_community_write');
            $table->string('cli_mode', 10)->nullable()->default('telnet')->after('cli_port');
            $table->timestamp('last_polled_at')->nullable()->after('status');
            $table->string('uptime_text', 100)->nullable()->after('last_polled_at');
            $table->decimal('temperature', 6, 2)->nullable()->after('uptime_text');
            $table->string('firmware_version', 100)->nullable()->after('temperature');
        });
    }

    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $cols = ['enable_secret','pon_port_count','onu_capacity','onu_active_count','snmp_version','snmp_community_read','snmp_community_write','cli_port','cli_mode','last_polled_at','uptime_text','temperature','firmware_version'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('olts', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
