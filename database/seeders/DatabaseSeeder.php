<?php

namespace Database\Seeders;

use App\Models\Finance\CashAccount;
use App\Models\Master\ExpenseCategory;
use App\Models\Master\IncomeCategory;
use App\Models\Master\Member;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'description' => 'Full access to all features',
        ]);

        $owner = Role::create([
            'name' => 'owner',
            'display_name' => 'Owner',
            'description' => 'Can view all reports',
        ]);

        $manager = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Can approve transactions and reports',
        ]);

        $finance = Role::create([
            'name' => 'finance',
            'display_name' => 'Finance',
            'description' => 'Can manage finances',
        ]);

        $treasurer = Role::create([
            'name' => 'treasurer',
            'display_name' => 'Treasurer',
            'description' => 'Can manage cash',
        ]);

        $supervisor = Role::create([
            'name' => 'supervisor',
            'display_name' => 'Supervisor',
            'description' => 'Can approve operational tasks',
        ]);

        $operator = Role::create([
            'name' => 'operator',
            'display_name' => 'Operator',
            'description' => 'Can input data',
        ]);

        $auditor = Role::create([
            'name' => 'auditor',
            'display_name' => 'Auditor',
            'description' => 'Can only view reports and audit trail',
        ]);

        $customer = Role::create([
            'name' => 'customer',
            'display_name' => 'Customer',
            'description' => 'Customer portal access',
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $admin->roles()->attach($superAdmin);

        // Seed permissions
        $this->call(PermissionSeeder::class);
        // Seed service profile types
        $this->call(ServiceProfileTypeSeeder::class);
        // Seed chart of accounts
        $this->call(ChartOfAccountsSeeder::class);
        // Seed journals
        $this->call(JournalSeeder::class);

        // Create income categories
        IncomeCategory::create([
            'code' => 'IC001',
            'name' => 'Member',
            'status' => 'active',
            'description' => 'Income from members',
            'created_by' => $admin->id,
        ]);

        IncomeCategory::create([
            'code' => 'IC002',
            'name' => 'Voucher',
            'status' => 'active',
            'description' => 'Income from vouchers',
            'created_by' => $admin->id,
        ]);

        IncomeCategory::create([
            'code' => 'IC003',
            'name' => 'Installation',
            'status' => 'active',
            'description' => 'Income from installations',
            'created_by' => $admin->id,
        ]);

        IncomeCategory::create([
            'code' => 'IC004',
            'name' => 'Maintenance',
            'status' => 'active',
            'description' => 'Income from maintenance',
            'created_by' => $admin->id,
        ]);

        IncomeCategory::create([
            'code' => 'IC005',
            'name' => 'Sales',
            'status' => 'active',
            'description' => 'Income from sales',
            'created_by' => $admin->id,
        ]);

        IncomeCategory::create([
            'code' => 'IC006',
            'name' => 'Other',
            'status' => 'active',
            'description' => 'Other income',
            'created_by' => $admin->id,
        ]);

        // Create expense categories
        ExpenseCategory::create([
            'code' => 'EC001',
            'name' => 'ISP',
            'status' => 'active',
            'description' => 'ISP expenses',
            'affects_revenue_sharing' => true,
            'created_by' => $admin->id,
        ]);

        ExpenseCategory::create([
            'code' => 'EC002',
            'name' => 'Beban Bersama',
            'status' => 'active',
            'description' => 'Beban Bersama',
            'affects_revenue_sharing' => true,
            'created_by' => $admin->id,
        ]);

        ExpenseCategory::create([
            'code' => 'EC003',
            'name' => 'Kas Bersama',
            'status' => 'active',
            'description' => 'Kas Bersama',
            'affects_revenue_sharing' => true,
            'created_by' => $admin->id,
        ]);

        ExpenseCategory::create([
            'code' => 'EC004',
            'name' => 'Salary',
            'status' => 'active',
            'description' => 'Salary expenses',
            'affects_revenue_sharing' => false,
            'created_by' => $admin->id,
        ]);

        ExpenseCategory::create([
            'code' => 'EC005',
            'name' => 'Maintenance',
            'status' => 'active',
            'description' => 'Maintenance expenses',
            'affects_revenue_sharing' => true,
            'created_by' => $admin->id,
        ]);

        ExpenseCategory::create([
            'code' => 'EC006',
            'name' => 'Inventory',
            'status' => 'active',
            'description' => 'Inventory expenses',
            'affects_revenue_sharing' => false,
            'created_by' => $admin->id,
        ]);

        // Create cash accounts
        CashAccount::create([
            'code' => 'CA001',
            'name' => 'Kas Operasional',
            'type' => 'kas_besar',
            'balance' => 0,
            'status' => 'active',
            'description' => 'Operational cash',
            'created_by' => $admin->id,
        ]);

        CashAccount::create([
            'code' => 'CA002',
            'name' => 'Kas Bersama',
            'type' => 'kas_besar',
            'balance' => 0,
            'status' => 'active',
            'description' => 'Shared cash',
            'created_by' => $admin->id,
        ]);

        CashAccount::create([
            'code' => 'CA003',
            'name' => 'Bank BCA',
            'type' => 'tabungan',
            'balance' => 0,
            'status' => 'active',
            'description' => 'BCA bank account',
            'created_by' => $admin->id,
        ]);

        // Create sample members
        Member::create([
            'code' => 'M001',
            'name' => 'Hendra',
            'phone' => '081234567890',
            'email' => 'hendra@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        Member::create([
            'code' => 'M002',
            'name' => 'Aceng',
            'phone' => '081234567891',
            'email' => 'aceng@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        Member::create([
            'code' => 'M003',
            'name' => 'Dedi',
            'phone' => '081234567892',
            'email' => 'dedi@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        Member::create([
            'code' => 'M004',
            'name' => 'Ajo',
            'phone' => '081234567893',
            'email' => 'ajo@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
    }
}
