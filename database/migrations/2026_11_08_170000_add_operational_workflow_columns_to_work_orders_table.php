<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('work_orders', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('uuid')->index();
                }
                if (!Schema::hasColumn('work_orders', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('company_id')->index();
                }
                if (!Schema::hasColumn('work_orders', 'notes')) {
                    $table->text('notes')->nullable()->after('description');
                }
                if (!Schema::hasColumn('work_orders', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('completed_at')->index();
                }
                if (!Schema::hasColumn('work_orders', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $columns = ['company_id', 'branch_id', 'notes', 'approved_by', 'approved_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('work_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
