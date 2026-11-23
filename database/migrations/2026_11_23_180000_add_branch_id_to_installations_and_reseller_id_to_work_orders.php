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
        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                if (!Schema::hasColumn('installations', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('reseller_id')->index();
                    if (Schema::hasTable('branches')) {
                        $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                    }
                }
            });
        }

        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('work_orders', 'reseller_id')) {
                    $table->unsignedBigInteger('reseller_id')->nullable()->after('customer_id')->index();
                    if (Schema::hasTable('users')) {
                        $table->foreign('reseller_id')->references('id')->on('users')->nullOnDelete();
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
        if (Schema::hasTable('installations') && Schema::hasColumn('installations', 'branch_id')) {
            Schema::table('installations', function (Blueprint $table) {
                try {
                    $table->dropForeign(['branch_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasTable('work_orders') && Schema::hasColumn('work_orders', 'reseller_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                try {
                    $table->dropForeign(['reseller_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('reseller_id');
            });
        }
    }
};
