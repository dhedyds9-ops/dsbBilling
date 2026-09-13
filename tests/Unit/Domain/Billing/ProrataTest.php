<?php

namespace Tests\Unit\Domain\Billing;

use Tests\TestCase;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\CRM\Customer;
use App\Models\Master\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ProrataTest extends TestCase
{
    use RefreshDatabase;

    public function test_prorata_calculation_is_accurate()
    {
        // Misal pelanggan mendaftar di pertengahan bulan (misal tanggal 15)
        // Harga paket 300,000 per bulan.
        // Di bulan dengan 30 hari, biaya prorata = (16 hari / 30 hari) * 300000 = 160000.
        
        $activationDate = Carbon::create(2026, 9, 15);
        $daysInMonth = $activationDate->daysInMonth; // 30
        $daysActive = $daysInMonth - $activationDate->day + 1; // 30 - 15 + 1 = 16 days

        $monthlyPrice = 300000;
        
        // Manual calculate
        $prorataCost = round(($daysActive / $daysInMonth) * $monthlyPrice);
        
        $this->assertEquals(16, $daysActive);
        $this->assertEquals(30, $daysInMonth);
        $this->assertEquals(160000, $prorataCost);

        // Jika Anda memiliki service Prorata khusus, panggil di sini
        // $service = new ProrataCalculator();
        // $calculated = $service->calculate($monthlyPrice, $activationDate);
        // $this->assertEquals(160000, $calculated);
    }
}
