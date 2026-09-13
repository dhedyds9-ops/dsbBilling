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
        $columns = function (Blueprint $table) {
            $table->integer('session_timeout')->nullable()->comment('Max session time in seconds');
            $table->integer('idle_timeout')->nullable()->comment('Max idle time in seconds');
            $table->integer('simultaneous_use')->nullable()->comment('Max concurrent sessions');
            $table->string('framed_pool')->nullable()->comment('Radius Framed-Pool name');
            $table->string('address_list')->nullable()->comment('MikroTik-Address-List');
            $table->timestamp('expires_at')->nullable()->comment('Absolute expiration date');
        };

        Schema::table('pppoe_users', clone $columns);
        Schema::table('hotspot_users', clone $columns);
    }

    public function down(): void
    {
        $columns = [
            'session_timeout',
            'idle_timeout',
            'simultaneous_use',
            'framed_pool',
            'address_list',
            'expires_at'
        ];

        Schema::table('pppoe_users', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });

        Schema::table('hotspot_users', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
