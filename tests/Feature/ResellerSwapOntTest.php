<?php

namespace Tests\Feature;

use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResellerSwapOntTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reseller_swap_ont(): void
    {
        $response = $this->get('/reseller-portal/network/swap-ont');
        $response->assertRedirect('/login');
    }

    public function test_reseller_can_access_swap_ont_page(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $branch = Branch::firstOrCreate(['code' => 'BR-RES'], ['name' => 'Cabang Reseller']);

        $reseller = User::factory()->create([
            'name' => 'Mitra Reseller Test',
            'email' => 'mitra_swap@dsbilling.test',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $this->actingAs($reseller);

        $response = $this->get('/reseller-portal/network/swap-ont');
        $response->assertOk();
    }

    public function test_reseller_can_search_customers_without_500_error(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $branch = Branch::firstOrCreate(['code' => 'BR-RES2'], ['name' => 'Cabang Reseller 2']);

        $reseller = User::factory()->create([
            'name' => 'Mitra Reseller Test 2',
            'email' => 'mitra_swap2@dsbilling.test',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $this->actingAs($reseller);

        \Livewire\Livewire::test(\App\Livewire\ResellerPortal\Network\SwapOnt::class)
            ->set('searchQuery', 'Pelanggan')
            ->assertStatus(200);
    }
}
