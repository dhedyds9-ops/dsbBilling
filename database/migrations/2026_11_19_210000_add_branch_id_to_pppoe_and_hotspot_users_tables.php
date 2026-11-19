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
        if (Schema::hasTable('pppoe_users')) {
            Schema::table('pppoe_users', function (Blueprint $table) {
                if (!Schema::hasColumn('pppoe_users', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('reseller_id')->index();
                    if (Schema::hasTable('branches')) {
                        $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                    }
                }
            });
        }

        if (Schema::hasTable('hotspot_users')) {
            Schema::table('hotspot_users', function (Blueprint $table) {
                if (!Schema::hasColumn('hotspot_users', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('reseller_id')->index();
                    if (Schema::hasTable('branches')) {
                        $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pppoe_users') && Schema::hasColumn('pppoe_users', 'branch_id')) {
            Schema::table('pppoe_users', function (Blueprint $table) {
                try {
                    $table->dropForeign(['branch_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('hotspot_users') && Schema::hasColumn('hotspot_users', 'branch_id')) {
            Schema::table('hotspot_users', function (Blueprint $table) {
                try {
                    $table->dropForeign(['branch_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('branch_id');
            });
        }
    }
};
