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
        Schema::table('customer_services', function (Blueprint $table) {
            $table->string('optical_status', 30)->default('unknown')->after('status');
            $table->string('tr069_status', 30)->default('unknown')->after('optical_status');
            $table->string('service_status', 30)->default('unknown')->after('tr069_status');
            $table->string('diagnostic_status', 50)->default('unknown')->after('service_status');
            $table->timestamp('last_seen_at')->nullable()->after('diagnostic_status');
            $table->timestamp('last_state_change_at')->nullable()->after('last_seen_at');
            $table->string('offline_reason')->nullable()->after('last_state_change_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            $table->dropColumn([
                'optical_status',
                'tr069_status',
                'service_status',
                'diagnostic_status',
                'last_seen_at',
                'last_state_change_at',
                'offline_reason'
            ]);
        });
    }
};
