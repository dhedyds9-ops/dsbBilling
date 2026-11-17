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

    public function test_reseller_can_switch_tabs_between_swap_and_dismantle(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $branch = Branch::firstOrCreate(['code' => 'BR-RES-TAB'], ['name' => 'Cabang Reseller Tab']);

        $reseller = User::factory()->create([
            'name' => 'Mitra Reseller Tab',
            'email' => 'mitra_tab@dsbilling.test',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $this->actingAs($reseller);

        \Livewire\Livewire::test(\App\Livewire\ResellerPortal\Network\SwapOnt::class)
            ->assertSet('tab', 'swap')
            ->call('setTab', 'dismantle')
            ->assertSet('tab', 'dismantle')
            ->call('setTab', 'swap')
            ->assertSet('tab', 'swap');
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

        $customer = \App\Models\CRM\Customer::create([
            'name' => 'Budi Santoso',
            'code' => 'CUST-0099',
            'phone' => '08123456789',
            'reseller_id' => $reseller->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($reseller);

        \Livewire\Livewire::test(\App\Livewire\ResellerPortal\Network\SwapOnt::class)
            ->set('searchQuery', 'Budi')
            ->assertStatus(200)
            ->assertSee('Budi Santoso');
    }

    public function test_reseller_can_replace_adaptor_for_customer(): void
    {
        $resellerRole = Role::firstOrCreate(['name' => 'reseller'], ['display_name' => 'Reseller', 'guard_name' => 'web']);
        $branch = Branch::firstOrCreate(['code' => 'BR-RES3'], ['name' => 'Cabang Reseller 3']);

        $reseller = User::factory()->create([
            'name' => 'Mitra Reseller Adaptor',
            'email' => 'mitra_adaptor@dsbilling.test',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $reseller->roles()->attach($resellerRole->id);

        $customer = \App\Models\CRM\Customer::create([
            'name' => 'Agam Pelanggan',
            'code' => 'CUST-AGAM-01',
            'phone' => '081299887766',
            'reseller_id' => $reseller->id,
            'branch_id' => $branch->id,
        ]);

        $itemAdaptor = \App\Models\Inventory\Item::create([
            'name' => 'Adaptor 12V 1.5A',
            'sku' => 'ADP-12V',
            'category' => 'Aksesoris',
            'type' => 'non-serialized',
            'unit' => 'unit',
            'branch_id' => $branch->id,
        ]);

        $custody = \App\Models\Inventory\InventoryCustody::create([
            'holder_id' => $reseller->id,
            'item_id' => $itemAdaptor->id,
            'quantity' => 5,
            'used_quantity' => 0,
            'returned_quantity' => 0,
            'status' => 'in_custody',
            'assigned_date' => now()->toDateString(),
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($reseller);

        \Livewire\Livewire::test(\App\Livewire\ResellerPortal\Network\SwapOnt::class)
            ->call('selectCustomer', $customer->id)
            ->set('swapMode', 'accessory')
            ->set('accessoryType', 'adaptor')
            ->set('accessoryQuantity', 1)
            ->set('accessoryReason', 'Adaptor Rusak / Tidak Menyala (Mati Total)')
            ->call('executeAccessoryReplacement')
            ->assertSet('swapCompleted', true)
            ->assertSee('Penggantian Adaptor 12V');

        $this->assertDatabaseHas('inventory_usages', [
            'customer_id' => $customer->id,
            'technician_id' => $reseller->id,
            'work_order_type' => 'repair_replacement',
        ]);

        $this->assertEquals(1.0, (float) $custody->fresh()->used_quantity);
    }
}
