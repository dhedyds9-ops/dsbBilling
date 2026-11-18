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
        // 1. Joint Closures (Closure FO)
        if (Schema::hasTable('joint_closures')) {
            Schema::table('joint_closures', function (Blueprint $table) {
                if (!Schema::hasColumn('joint_closures', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('joint_closures', 'reseller_id')) {
                    $table->unsignedBigInteger('reseller_id')->nullable()->index()->after('branch_id');
                }
                if (!Schema::hasColumn('joint_closures', 'photos')) {
                    $table->json('photos')->nullable()->after('status');
                }
            });
        }

        // 2. ODPs (Optical Distribution Points)
        if (Schema::hasTable('odps')) {
            Schema::table('odps', function (Blueprint $table) {
                if (!Schema::hasColumn('odps', 'reseller_id')) {
                    $table->unsignedBigInteger('reseller_id')->nullable()->index()->after('branch_id');
                }
                if (!Schema::hasColumn('odps', 'photos')) {
                    $table->json('photos')->nullable()->after('status');
                }
            });
        }

        // 3. ODCs (Optical Distribution Cabinets)
        if (Schema::hasTable('odcs')) {
            Schema::table('odcs', function (Blueprint $table) {
                if (!Schema::hasColumn('odcs', 'reseller_id')) {
                    $table->unsignedBigInteger('reseller_id')->nullable()->index()->after('branch_id');
                }
                if (!Schema::hasColumn('odcs', 'photos')) {
                    $table->json('photos')->nullable()->after('status');
                }
            });
        }

        // 4. OLTs
        if (Schema::hasTable('olts')) {
            Schema::table('olts', function (Blueprint $table) {
                if (!Schema::hasColumn('olts', 'photos')) {
                    $table->json('photos')->nullable()->after('status');
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
                $cols = array_filter(['branch_id', 'reseller_id', 'photos'], fn($c) => Schema::hasColumn('joint_closures', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('odps')) {
            Schema::table('odps', function (Blueprint $table) {
                $cols = array_filter(['reseller_id', 'photos'], fn($c) => Schema::hasColumn('odps', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('odcs')) {
            Schema::table('odcs', function (Blueprint $table) {
                $cols = array_filter(['reseller_id', 'photos'], fn($c) => Schema::hasColumn('odcs', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('olts')) {
            Schema::table('olts', function (Blueprint $table) {
                if (Schema::hasColumn('olts', 'photos')) {
                    $table->dropColumn('photos');
                }
            });
        }
    }
};
