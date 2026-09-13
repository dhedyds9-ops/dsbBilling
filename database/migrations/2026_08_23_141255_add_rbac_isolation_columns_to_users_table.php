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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'job_title') && !Schema::hasColumn('users', 'job_function')) {
                $table->renameColumn('job_title', 'job_function');
            }
            if (!Schema::hasColumn('users', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('password');
                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'reseller_id')) {
                $table->unsignedBigInteger('reseller_id')->nullable()->after('branch_id');
                $table->foreign('reseller_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['reseller_id']);
            $table->dropColumn(['branch_id', 'reseller_id']);
            $table->renameColumn('job_function', 'job_title');
        });
    }
};
