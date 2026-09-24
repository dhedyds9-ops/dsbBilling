<?php

namespace Database\Seeders;

use App\Models\Finance\Journal;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $journals = [
            ['code' => 'JRN-CASH', 'name' => 'Jurnal Kas', 'type' => 'cash', 'is_active' => true],
            ['code' => 'JRN-BANK', 'name' => 'Jurnal Bank', 'type' => 'bank', 'is_active' => true],
            ['code' => 'JRN-GENERAL', 'name' => 'Jurnal Umum', 'type' => 'general', 'is_active' => true],
            ['code' => 'JRN-REV-SHARE', 'name' => 'Jurnal Revenue Sharing', 'type' => 'revenue_sharing', 'is_active' => true],
        ];

        foreach ($journals as $journal) {
            Journal::create($journal);
        }
    }
}
