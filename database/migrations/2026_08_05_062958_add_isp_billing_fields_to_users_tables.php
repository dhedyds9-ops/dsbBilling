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
        Schema::table('pppoe_users', function (Blueprint $table) {
            $table->string('mac_address')->nullable()->after('password');
            $table->string('static_ip')->nullable()->after('mac_address');
            $table->foreignId('odp_id')->nullable()->after('ip_allocation_id')->constrained()->nullOnDelete();
            $table->integer('port_number')->nullable()->after('odp_id');
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->string('mac_address')->nullable()->after('password');
            $table->string('static_ip')->nullable()->after('mac_address');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_tables', function (Blueprint $table) {
            //
        });
    }
};
