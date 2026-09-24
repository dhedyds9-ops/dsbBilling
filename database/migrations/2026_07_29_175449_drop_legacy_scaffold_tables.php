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
        Schema::disableForeignKeyConstraints();

        $tables = [
            // 'members', // Still in use by Customer model
            'member_incomes',
            'income_categories',
            'expense_categories',
            'cash_accounts',
            'cash_transactions',
            'expense_sharings',
            'revenue_sharings',
            // 'internet_packages', // Still in use by InternetPackage model
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration. We don't restore the legacy tables.
    }
};
