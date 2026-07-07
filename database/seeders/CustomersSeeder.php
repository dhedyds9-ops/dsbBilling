<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        $customerNames = [
            'Asep Sutisna', 'Siti Nurhaliza', 'Rudi Hartono', 'Dewi Lestari', 'Agus Suparman',
            'Yulianti', 'Budi Santoso', 'Rina Marlina', 'Dadan Hermawan', 'Sri Wahyuni',
        ];

        foreach ($customerNames as $index => $name) {
            User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
}
