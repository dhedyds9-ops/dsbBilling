<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Keuangan\IncomeHarian\Index as IncomeHarianIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\User;

class IncomeFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user to bypass auth blocks
        $this->actingAs(User::factory()->create());
    }

    public function test_customer_user_type_only_shows_pppoe_and_hotspot()
    {
        $component = Livewire::test(IncomeHarianIndex::class)
            ->set('filters.user_type', 'customer');

        $services = $component->get('services');
        $serviceIds = collect($services)->pluck('id')->toArray();

        $this->assertContains('pppoe', $serviceIds);
        $this->assertContains('hotspot', $serviceIds);
        $this->assertNotContains('voucher', $serviceIds);
        $this->assertNotContains('evoucher', $serviceIds);
    }

    public function test_voucher_user_type_only_shows_voucher_and_evoucher()
    {
        $component = Livewire::test(IncomeHarianIndex::class)
            ->set('filters.user_type', 'voucher');

        $services = $component->get('services');
        $serviceIds = collect($services)->pluck('id')->toArray();

        $this->assertContains('voucher', $serviceIds);
        $this->assertContains('evoucher', $serviceIds);
        $this->assertNotContains('pppoe', $serviceIds);
        $this->assertNotContains('hotspot', $serviceIds);
    }

    public function test_changing_user_type_resets_dependent_filters()
    {
        Livewire::test(IncomeHarianIndex::class)
            ->set('filters.user_type', 'customer')
            ->set('filters.service_type', 'pppoe')
            ->set('filters.profile_paket', '1')
            // Change user type to voucher
            ->set('filters.user_type', 'voucher')
            // It should auto reset service_type and profile_paket to 'all'
            ->assertSet('filters.service_type', 'all')
            ->assertSet('filters.profile_paket', 'all');
    }

    public function test_changing_service_type_resets_profile_paket()
    {
        Livewire::test(IncomeHarianIndex::class)
            ->set('filters.user_type', 'customer')
            ->set('filters.service_type', 'pppoe')
            ->set('filters.profile_paket', '1')
            // Change service type to hotspot
            ->set('filters.service_type', 'hotspot')
            // It should auto reset profile_paket
            ->assertSet('filters.profile_paket', 'all');
    }
}
