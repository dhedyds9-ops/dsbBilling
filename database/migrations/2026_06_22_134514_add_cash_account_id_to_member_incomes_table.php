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
        Schema::table('member_incomes', function (Blueprint $table) {
            $table->foreignId('cash_account_id')->nullable()->after('income_category_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_incomes', function (Blueprint $table) {
            $table->dropForeign(['cash_account_id']);
            $table->dropColumn('cash_account_id');
        });
    }
};
