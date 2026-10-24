<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installations', function (Blueprint $table) {
            $table->unsignedBigInteger('survey_id')->nullable()->change();
            
            if (!Schema::hasColumn('installations', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('survey_id');
            }
            if (!Schema::hasColumn('installations', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('installations', 'technician_id')) {
                $table->unsignedBigInteger('technician_id')->nullable()->after('assigned_to');
            }
            if (!Schema::hasColumn('installations', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('scheduled_at');
            }
            if (!Schema::hasColumn('installations', 'scheduled_time')) {
                $table->string('scheduled_time', 20)->nullable()->default('09:00')->after('scheduled_date');
            }
            if (!Schema::hasColumn('installations', 'priority')) {
                $table->string('priority', 20)->nullable()->default('medium')->after('status');
            }
            if (!Schema::hasColumn('installations', 'pop_id')) {
                $table->unsignedBigInteger('pop_id')->nullable()->after('priority');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installations', function (Blueprint $table) {
            $cols = [];
            foreach (['customer_id', 'customer_name', 'technician_id', 'scheduled_date', 'scheduled_time', 'priority', 'pop_id'] as $col) {
                if (Schema::hasColumn('installations', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
