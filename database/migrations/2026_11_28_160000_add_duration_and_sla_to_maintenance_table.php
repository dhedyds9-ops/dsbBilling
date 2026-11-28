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
        // 1. support_maintenances SLA & duration columns
        if (Schema::hasTable('support_maintenances')) {
            Schema::table('support_maintenances', function (Blueprint $table) {
                if (!Schema::hasColumn('support_maintenances', 'sla_due_at')) {
                    $table->timestamp('sla_due_at')->nullable()->index();
                }
                if (!Schema::hasColumn('support_maintenances', 'sla_started_at')) {
                    $table->timestamp('sla_started_at')->nullable();
                }
                if (!Schema::hasColumn('support_maintenances', 'sla_target_minutes')) {
                    $table->integer('sla_target_minutes')->nullable();
                }
                if (!Schema::hasColumn('support_maintenances', 'sla_status')) {
                    $table->string('sla_status', 30)->default('normal')->index();
                }
                if (!Schema::hasColumn('support_maintenances', 'estimated_hours')) {
                    $table->decimal('estimated_hours', 5, 2)->nullable();
                }
            });
        }

        // 2. tickets estimated_hours column
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'estimated_hours')) {
                    $table->decimal('estimated_hours', 5, 2)->nullable();
                }
            });
        }

        // 3. installations estimated_hours column
        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                if (!Schema::hasColumn('installations', 'estimated_hours')) {
                    $table->decimal('estimated_hours', 5, 2)->nullable();
                }
            });
        }

        // 4. work_orders estimated_hours column
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('work_orders', 'estimated_hours')) {
                    $table->decimal('estimated_hours', 5, 2)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('support_maintenances')) {
            Schema::table('support_maintenances', function (Blueprint $table) {
                $cols = ['sla_due_at', 'sla_started_at', 'sla_target_minutes', 'sla_status', 'estimated_hours'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('support_maintenances', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'estimated_hours')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('estimated_hours');
            });
        }

        if (Schema::hasTable('installations') && Schema::hasColumn('installations', 'estimated_hours')) {
            Schema::table('installations', function (Blueprint $table) {
                $table->dropColumn('estimated_hours');
            });
        }

        if (Schema::hasTable('work_orders') && Schema::hasColumn('work_orders', 'estimated_hours')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropColumn('estimated_hours');
            });
        }
    }
};
