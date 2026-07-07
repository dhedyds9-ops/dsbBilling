<?php

namespace Database\Seeders;

use App\Models\Finance\ChartOfAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Use Laravel's truncate for SQLite compatibility
        DB::statement('DELETE FROM chart_of_accounts');
        DB::statement('DELETE FROM journals');

        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Kas Operasional', 'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
            ['code' => '1010', 'name' => 'Kas Bersama', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_id' => 1, 'level' => 2, 'is_active' => true],
            ['code' => '1020', 'name' => 'Bank BCA', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_id' => 1, 'level' => 2, 'is_active' => true],
            ['code' => '1030', 'name' => 'Bank BRI', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_id' => 1, 'level' => 2, 'is_active' => true],
            ['code' => '1100', 'name' => 'Piutang', 'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
            ['code' => '1200', 'name' => 'Persediaan', 'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
            ['code' => '1300', 'name' => 'Aset Tetap', 'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],

            // Liabilities
            ['code' => '2000', 'name' => 'Hutang Usaha', 'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_active' => true],
            ['code' => '2100', 'name' => 'Hutang Anggota', 'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_active' => true],

            // Equity
            ['code' => '3000', 'name' => 'Modal', 'account_type' => 'equity', 'normal_balance' => 'credit', 'level' => 1, 'is_active' => true],

            // Revenue
            ['code' => '4000', 'name' => 'Pendapatan Member', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'level' => 1, 'is_active' => true],
            ['code' => '4010', 'name' => 'Pendapatan Voucher', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'level' => 1, 'is_active' => true],

            // Expenses
            ['code' => '5000', 'name' => 'Beban ISP', 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
            ['code' => '5010', 'name' => 'Beban Operasional', 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
            ['code' => '5020', 'name' => 'Beban Gaji', 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
            ['code' => '5030', 'name' => 'Beban Maintenance', 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
            ['code' => '5100', 'name' => 'Beban Revenue Sharing', 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_active' => true],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create($account);
        }
    }
}
