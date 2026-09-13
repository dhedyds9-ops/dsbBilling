<?php

namespace Database\Seeders;

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
        // Seed permissions and canonical roles first
        $this->call(PermissionSeeder::class);

        // Fetch the Administrator role created by PermissionSeeder
        $adminRole = Role::where('name', 'administrator')->first();

        // Create admin user
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', \Illuminate\Support\Str::random(16))),
            ]
        );

        if ($adminRole && !$admin->hasRole('administrator')) {
            $admin->roles()->attach($adminRole);
        }

        // Seed service profile types
        $this->call(ServiceProfileTypeSeeder::class);
        // Seed chart of accounts
        $this->call(ChartOfAccountsSeeder::class);
        // Seed journals
        $this->call(JournalSeeder::class);

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

        // Seed default BHP/USO configurations
        \App\Models\Finance\BhpUsoConfig::create([
            'period_name' => 'Periode 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'calculation_basis' => 'RETAIL_REVENUE',
            'bhp_rate' => 0.0050,
            'uso_rate' => 0.0125,
            'is_active' => true,
        ]);

        \App\Models\Finance\BhpUsoConfig::create([
            'period_name' => 'Periode 2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
            'calculation_basis' => 'RESELLER_MARGIN',
            'bhp_rate' => 0.0050,
            'uso_rate' => 0.0125,
            'is_active' => true,
        ]);
    }
}
