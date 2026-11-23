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
        // 1. work_orders SLA columns
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('work_orders', 'sla_due_at')) {
                    $table->timestamp('sla_due_at')->nullable()->index();
                }
                if (!Schema::hasColumn('work_orders', 'sla_started_at')) {
                    $table->timestamp('sla_started_at')->nullable();
                }
                if (!Schema::hasColumn('work_orders', 'sla_target_minutes')) {
                    $table->integer('sla_target_minutes')->nullable();
                }
                if (!Schema::hasColumn('work_orders', 'sla_status')) {
                    $table->string('sla_status', 30)->default('normal')->index();
                }
                if (!Schema::hasColumn('work_orders', 'sla_warning_sent_at')) {
                    $table->timestamp('sla_warning_sent_at')->nullable();
                }
                if (!Schema::hasColumn('work_orders', 'sla_breached_sent_at')) {
                    $table->timestamp('sla_breached_sent_at')->nullable();
                }
            });
        }

        // 2. tickets SLA columns
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'sla_due_at')) {
                    $table->timestamp('sla_due_at')->nullable()->index();
                }
                if (!Schema::hasColumn('tickets', 'sla_started_at')) {
                    $table->timestamp('sla_started_at')->nullable();
                }
                if (!Schema::hasColumn('tickets', 'sla_target_minutes')) {
                    $table->integer('sla_target_minutes')->nullable();
                }
                if (!Schema::hasColumn('tickets', 'sla_status')) {
                    $table->string('sla_status', 30)->default('normal')->index();
                }
                if (!Schema::hasColumn('tickets', 'sla_warning_sent_at')) {
                    $table->timestamp('sla_warning_sent_at')->nullable();
                }
                if (!Schema::hasColumn('tickets', 'sla_breached_sent_at')) {
                    $table->timestamp('sla_breached_sent_at')->nullable();
                }
            });
        }

        // 3. installations SLA columns
        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                if (!Schema::hasColumn('installations', 'sla_due_at')) {
                    $table->timestamp('sla_due_at')->nullable()->index();
                }
                if (!Schema::hasColumn('installations', 'sla_started_at')) {
                    $table->timestamp('sla_started_at')->nullable();
                }
                if (!Schema::hasColumn('installations', 'sla_target_minutes')) {
                    $table->integer('sla_target_minutes')->nullable();
                }
                if (!Schema::hasColumn('installations', 'sla_status')) {
                    $table->string('sla_status', 30)->default('normal')->index();
                }
                if (!Schema::hasColumn('installations', 'sla_warning_sent_at')) {
                    $table->timestamp('sla_warning_sent_at')->nullable();
                }
                if (!Schema::hasColumn('installations', 'sla_breached_sent_at')) {
                    $table->timestamp('sla_breached_sent_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'sla_due_at',
            'sla_started_at',
            'sla_target_minutes',
            'sla_status',
            'sla_warning_sent_at',
            'sla_breached_sent_at'
        ];

        foreach (['work_orders', 'tickets', 'installations'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                    $existing = [];
                    foreach ($columns as $col) {
                        if (Schema::hasColumn($tableName, $col)) {
                            $existing[] = $col;
                        }
                    }
                    if (!empty($existing)) {
                        $table->dropColumn($existing);
                    }
                });
            }
        }
    }
};
