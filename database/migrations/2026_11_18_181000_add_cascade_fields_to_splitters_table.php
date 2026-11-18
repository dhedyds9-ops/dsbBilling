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
        if (Schema::hasTable('splitters')) {
            Schema::table('splitters', function (Blueprint $table) {
                if (!Schema::hasColumn('splitters', 'type')) {
                    $table->string('type', 20)->default('plc')->after('serial_number');
                }
                if (!Schema::hasColumn('splitters', 'ratio_label')) {
                    $table->string('ratio_label', 20)->default('1:8')->after('type');
                }
                if (!Schema::hasColumn('splitters', 'package_type')) {
                    $table->string('package_type', 30)->default('tray')->after('ratio_label');
                }
                if (!Schema::hasColumn('splitters', 'input_port_count')) {
                    $table->unsignedSmallInteger('input_port_count')->default(1)->after('package_type');
                }
                if (!Schema::hasColumn('splitters', 'insertion_loss_db')) {
                    $table->decimal('insertion_loss_db', 5, 2)->nullable()->after('port_count');
                }
                if (!Schema::hasColumn('splitters', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('status')->constrained('branches')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('splitters')) {
            Schema::table('splitters', function (Blueprint $table) {
                if (Schema::hasColumn('splitters', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                $columns = ['type', 'ratio_label', 'package_type', 'input_port_count', 'insertion_loss_db'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('splitters', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
