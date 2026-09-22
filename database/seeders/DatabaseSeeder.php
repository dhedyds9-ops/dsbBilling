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
        $rawPassword = env('ADMIN_DEFAULT_PASSWORD', \Illuminate\Support\Str::random(16));
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make($rawPassword),
            ]
        );

        if ($adminRole && !$admin->hasRole('administrator')) {
            $admin->roles()->attach($adminRole);
        }

        // Tampilkan info kredensial ke layar agar pengguna tahu
        $this->command->info('=====================================');
        $this->command->info('Admin User created/verified!');
        $this->command->info('Username: admin');
        $this->command->info('Email   : admin@example.com');
        $this->command->info('Password: ' . $rawPassword);
        $this->command->info('=====================================');

        // Seed service profile types
        $this->call(ServiceProfileTypeSeeder::class);
        // Seed chart of accounts
        $this->call(ChartOfAccountsSeeder::class);
        // Seed journals
        $this->call(JournalSeeder::class);

        }
}
