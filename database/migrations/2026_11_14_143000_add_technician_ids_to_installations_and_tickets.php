<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table installations: tambahkan technician_ids (JSON) & completed_by
        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                if (!Schema::hasColumn('installations', 'technician_ids')) {
                    $table->json('technician_ids')->nullable()->after('technician_id');
                }
                if (!Schema::hasColumn('installations', 'completed_by')) {
                    $table->unsignedBigInteger('completed_by')->nullable()->after('completed_at')->index();
                }
            });
        }

        // 2. Table tickets: tambahkan technician_ids (JSON) & resolved_by
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'technician_ids')) {
                    $table->json('technician_ids')->nullable()->after('assigned_to');
                }
                if (!Schema::hasColumn('tickets', 'resolved_by')) {
                    $table->unsignedBigInteger('resolved_by')->nullable()->after('resolved_at')->index();
                }
            });
        }

        // 3. Table work_orders: tambahkan technician_ids (JSON)
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('work_orders', 'technician_ids')) {
                    $table->json('technician_ids')->nullable()->after('assigned_to');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                if (Schema::hasColumn('installations', 'technician_ids')) {
                    $table->dropColumn('technician_ids');
                }
                if (Schema::hasColumn('installations', 'completed_by')) {
                    $table->dropColumn('completed_by');
                }
            });
        }

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (Schema::hasColumn('tickets', 'technician_ids')) {
                    $table->dropColumn('technician_ids');
                }
                if (Schema::hasColumn('tickets', 'resolved_by')) {
                    $table->dropColumn('resolved_by');
                }
            });
        }

        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (Schema::hasColumn('work_orders', 'technician_ids')) {
                    $table->dropColumn('technician_ids');
                }
            });
        }
    }
};
