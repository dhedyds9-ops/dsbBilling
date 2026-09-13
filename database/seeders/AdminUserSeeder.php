<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_DEFAULT_PASSWORD', \Illuminate\Support\Str::random(16));
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make($password),
            ]
        );

        $this->command->info('User created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: ' . (env('ADMIN_DEFAULT_PASSWORD') ? '***** (from env)' : $password));
    }
}
