<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installations', function (Blueprint $table) {
            $table->string('onu_serial_number')->nullable()->after('status');
            $table->string('onu_mac_address')->nullable()->after('onu_serial_number');
            $table->string('router_model')->nullable()->after('onu_mac_address');
            $table->string('router_serial_number')->nullable()->after('router_model');
            $table->text('digital_signature')->nullable()->after('router_serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('installations', function (Blueprint $table) {
            $table->dropColumn([
                'onu_serial_number',
                'onu_mac_address',
                'router_model',
                'router_serial_number',
                'digital_signature',
            ]);
        });
    }
};
