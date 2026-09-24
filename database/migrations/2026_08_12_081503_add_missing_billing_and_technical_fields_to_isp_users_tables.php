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
            $table->foreignId('router_id')->nullable()->after('ip_allocation_id')->constrained('routers')->nullOnDelete();
            $table->string('billing_cycle')->nullable()->default('monthly')->after('status');
            $table->decimal('setup_fee', 15, 2)->nullable()->after('billing_cycle');
            $table->string('payment_status')->nullable()->default('unpaid')->after('setup_fee');
            $table->foreignId('owner_id')->nullable()->after('payment_status')->constrained('users')->nullOnDelete();
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->foreignId('router_id')->nullable()->after('voucher_pool_id')->constrained('routers')->nullOnDelete();
            $table->foreignId('odp_id')->nullable()->after('static_ip')->constrained('odps')->nullOnDelete();
            $table->integer('port_number')->nullable()->after('odp_id');
            $table->integer('time_limit_hours')->nullable()->after('port_number');
            $table->decimal('quota_gb', 10, 2)->nullable()->after('time_limit_hours');
            $table->string('billing_cycle')->nullable()->default('monthly')->after('status');
            $table->decimal('setup_fee', 15, 2)->nullable()->after('billing_cycle');
            $table->string('payment_status')->nullable()->default('unpaid')->after('setup_fee');
            $table->foreignId('owner_id')->nullable()->after('payment_status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pppoe_users', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropForeign(['owner_id']);
            $table->dropColumn([
                'router_id',
                'billing_cycle',
                'setup_fee',
                'payment_status',
                'owner_id'
            ]);
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropForeign(['odp_id']);
            $table->dropForeign(['owner_id']);
            $table->dropColumn([
                'router_id',
                'odp_id',
                'port_number',
                'time_limit_hours',
                'quota_gb',
                'billing_cycle',
                'setup_fee',
                'payment_status',
                'owner_id'
            ]);
        });
    }
};
