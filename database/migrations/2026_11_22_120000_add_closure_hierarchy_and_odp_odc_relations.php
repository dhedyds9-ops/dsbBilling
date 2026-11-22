<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Joint Closures: Add OLT Uplink and Parent Closure Cascade
        if (Schema::hasTable('joint_closures')) {
            Schema::table('joint_closures', function (Blueprint $table) {
                if (!Schema::hasColumn('joint_closures', 'olt_id')) {
                    $table->unsignedBigInteger('olt_id')->nullable()->after('odc_id')->index();
                }
                if (!Schema::hasColumn('joint_closures', 'parent_closure_id')) {
                    $table->unsignedBigInteger('parent_closure_id')->nullable()->after('olt_id')->index();
                }
                if (!Schema::hasColumn('joint_closures', 'pon_port')) {
                    $table->string('pon_port')->nullable()->after('parent_closure_id');
                }
            });
        }

        // 2. ODPs: Add Joint Closure connection (ODC -> Closure -> ODP)
        if (Schema::hasTable('odps')) {
            Schema::table('odps', function (Blueprint $table) {
                if (!Schema::hasColumn('odps', 'joint_closure_id')) {
                    $table->unsignedBigInteger('joint_closure_id')->nullable()->after('odc_id')->index();
                }
            });
        }

        // 3. ODCs: Add Joint Closure connection (OLT -> Closure -> ODC)
        if (Schema::hasTable('odcs')) {
            Schema::table('odcs', function (Blueprint $table) {
                if (!Schema::hasColumn('odcs', 'joint_closure_id')) {
                    $table->unsignedBigInteger('joint_closure_id')->nullable()->after('parent_odc_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('joint_closures')) {
            Schema::table('joint_closures', function (Blueprint $table) {
                if (Schema::hasColumn('joint_closures', 'pon_port')) {
                    $table->dropColumn('pon_port');
                }
                if (Schema::hasColumn('joint_closures', 'parent_closure_id')) {
                    $table->dropIndex(['parent_closure_id']);
                    $table->dropColumn('parent_closure_id');
                }
                if (Schema::hasColumn('joint_closures', 'olt_id')) {
                    $table->dropIndex(['olt_id']);
                    $table->dropColumn('olt_id');
                }
            });
        }

        if (Schema::hasTable('odps')) {
            Schema::table('odps', function (Blueprint $table) {
                if (Schema::hasColumn('odps', 'joint_closure_id')) {
                    $table->dropIndex(['joint_closure_id']);
                    $table->dropColumn('joint_closure_id');
                }
            });
        }

        if (Schema::hasTable('odcs')) {
            Schema::table('odcs', function (Blueprint $table) {
                if (Schema::hasColumn('odcs', 'joint_closure_id')) {
                    $table->dropIndex(['joint_closure_id']);
                    $table->dropColumn('joint_closure_id');
                }
            });
        }
    }
};
