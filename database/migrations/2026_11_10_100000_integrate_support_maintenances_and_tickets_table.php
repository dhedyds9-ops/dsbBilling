<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_maintenances')) {
            Schema::table('support_maintenances', function (Blueprint $table) {
                if (!Schema::hasColumn('support_maintenances', 'ticket_id')) {
                    $table->unsignedBigInteger('ticket_id')->nullable()->after('branch_id')->index();
                }
                if (!Schema::hasColumn('support_maintenances', 'customer_id')) {
                    $table->unsignedBigInteger('customer_id')->nullable()->after('ticket_id')->index();
                }
                if (!Schema::hasColumn('support_maintenances', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('customer_id');
                }
                if (!Schema::hasColumn('support_maintenances', 'customer_phone')) {
                    $table->string('customer_phone')->nullable()->after('customer_name');
                }
                if (!Schema::hasColumn('support_maintenances', 'customer_address')) {
                    $table->text('customer_address')->nullable()->after('customer_phone');
                }
                if (!Schema::hasColumn('support_maintenances', 'source')) {
                    $table->string('source', 30)->default('manual')->after('type'); // 'ticket', 'manual', 'preventive'
                }
                if (!Schema::hasColumn('support_maintenances', 'qc_verified_by')) {
                    $table->unsignedBigInteger('qc_verified_by')->nullable()->after('resolution_notes');
                }
                if (!Schema::hasColumn('support_maintenances', 'qc_verified_at')) {
                    $table->dateTime('qc_verified_at')->nullable()->after('qc_verified_by');
                }
                if (!Schema::hasColumn('support_maintenances', 'qc_notes')) {
                    $table->text('qc_notes')->nullable()->after('qc_verified_at');
                }
            });
        }

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'requires_field_visit')) {
                    $table->boolean('requires_field_visit')->default(false)->after('category');
                }
                if (!Schema::hasColumn('tickets', 'maintenance_wo_id')) {
                    $table->unsignedBigInteger('maintenance_wo_id')->nullable()->after('requires_field_visit')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_maintenances')) {
            Schema::table('support_maintenances', function (Blueprint $table) {
                $columns = ['ticket_id', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'source', 'qc_verified_by', 'qc_verified_at', 'qc_notes'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('support_maintenances', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $columns = ['requires_field_visit', 'maintenance_wo_id'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('tickets', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
