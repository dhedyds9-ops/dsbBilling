<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pon_ports') && ! Schema::hasColumn('pon_ports', 'port_index')) {
            Schema::table('pon_ports', function (Blueprint $table) {
                $table->integer('port_index')->nullable()->after('port_number');
            });

            // Sinkronkan nilai port_index dari port_number yang sudah ada
            try {
                \Illuminate\Support\Facades\DB::statement('UPDATE pon_ports SET port_index = port_number WHERE port_index IS NULL');
            } catch (\Throwable) {
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pon_ports') && Schema::hasColumn('pon_ports', 'port_index')) {
            Schema::table('pon_ports', function (Blueprint $table) {
                $table->dropColumn('port_index');
            });
        }
    }
};
