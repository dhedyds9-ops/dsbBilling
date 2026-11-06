<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'optical_power')) {
                    $table->string('optical_power', 50)->nullable()->after('resolution_notes');
                }
                if (!Schema::hasColumn('tickets', 'digital_signature')) {
                    $table->longText('digital_signature')->nullable()->after('optical_power');
                }
                if (!Schema::hasColumn('tickets', 'checklists')) {
                    $table->json('checklists')->nullable()->after('digital_signature');
                }
            });
        }

        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                if (!Schema::hasColumn('installations', 'optical_power')) {
                    $table->string('optical_power', 50)->nullable()->after('digital_signature');
                }
                if (!Schema::hasColumn('installations', 'checklists')) {
                    $table->json('checklists')->nullable()->after('optical_power');
                }
            });
        }

        if (Schema::hasTable('support_maintenance_materials')) {
            Schema::table('support_maintenance_materials', function (Blueprint $table) {
                if (!Schema::hasColumn('support_maintenance_materials', 'technician_id')) {
                    $table->unsignedBigInteger('technician_id')->nullable()->index()->after('branch_id');
                }
            });
        }

        if (Schema::hasTable('support_maintenance_usages')) {
            Schema::table('support_maintenance_usages', function (Blueprint $table) {
                if (!Schema::hasColumn('support_maintenance_usages', 'installation_id')) {
                    $table->unsignedBigInteger('installation_id')->nullable()->index()->after('maintenance_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $cols = [];
                foreach (['optical_power', 'digital_signature', 'checklists'] as $c) {
                    if (Schema::hasColumn('tickets', $c)) $cols[] = $c;
                }
                if ($cols) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('installations')) {
            Schema::table('installations', function (Blueprint $table) {
                $cols = [];
                foreach (['optical_power', 'checklists'] as $c) {
                    if (Schema::hasColumn('installations', $c)) $cols[] = $c;
                }
                if ($cols) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('support_maintenance_materials')) {
            Schema::table('support_maintenance_materials', function (Blueprint $table) {
                if (Schema::hasColumn('support_maintenance_materials', 'technician_id')) {
                    $table->dropColumn('technician_id');
                }
            });
        }

        if (Schema::hasTable('support_maintenance_usages')) {
            Schema::table('support_maintenance_usages', function (Blueprint $table) {
                if (Schema::hasColumn('support_maintenance_usages', 'installation_id')) {
                    $table->dropColumn('installation_id');
                }
            });
        }
    }
};
