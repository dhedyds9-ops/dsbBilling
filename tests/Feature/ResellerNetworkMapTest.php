<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\ResellerPortal\Network\Map\Index;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResellerNetworkMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => UserRole::Reseller->value], ['display_name' => 'Reseller']);
    }

    private function createResellerUser(int $branchId = 1): User
    {
        $branch = Branch::firstOrCreate(['id' => $branchId], ['name' => 'Cabang ' . $branchId, 'code' => 'BR-' . $branchId]);
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'job_function' => null,
        ]);
        $user->attachRole(UserRole::Reseller->value);
        return $user;
    }

    public function test_reseller_can_access_network_map_page()
    {
        $reseller = $this->createResellerUser(1);

        $response = $this->actingAs($reseller)->get(route('reseller-portal.network.map'));
        $response->assertStatus(200);
        $response->assertSee('Peta Jaringan Satelit');
        $response->assertSee('+ Tambah ODP');
        $response->assertSee('+ Tambah ODC');
    }

    public function test_reseller_sees_branch_odc_and_odp()
    {
        $reseller = $this->createResellerUser(1);

        $olt = Olt::withoutGlobalScopes()->create([
            'name' => 'OLT Test 1',
            'code' => 'OLT-01',
            'ip_address' => '10.10.10.1',
            'branch_id' => 1,
            'latitude' => '-6.2000',
            'longitude' => '106.8000',
        ]);

        $odc = Odc::withoutGlobalScopes()->create([
            'name' => 'ODC Test 1',
            'code' => 'ODC-01',
            'olt_id' => $olt->id,
            'branch_id' => 1,
            'port_count' => 48,
            'latitude' => '-6.2010',
            'longitude' => '106.8010',
        ]);

        $odp = Odp::withoutGlobalScopes()->create([
            'name' => 'ODP Test 1',
            'code' => 'ODP-01',
            'odc_id' => $odc->id,
            'branch_id' => 1,
            'port_count' => 8,
            'latitude' => '-6.2020',
            'longitude' => '106.8020',
        ]);

        $this->actingAs($reseller);

        Livewire::test(Index::class)
            ->assertSee('ODC (1)')
            ->assertSee('ODP (1)')
            ->assertSee('OLT (1)');
    }

    public function test_reseller_can_create_new_odp_via_livewire()
    {
        $reseller = $this->createResellerUser(1);
        $this->actingAs($reseller);

        Livewire::test(Index::class)
            ->call('openCreateModal', 'odp', -6.2100, 106.8100)
            ->assertSet('showModal', true)
            ->assertSet('nodeType', 'odp')
            ->set('nodeName', 'ODP Baru Den Roy')
            ->set('nodeCode', 'ODP-DENROY-99')
            ->set('portCount', 8)
            ->set('latitude', '-6.2100')
            ->set('longitude', '106.8100')
            ->set('address', 'Jl. Cisarap No. 12')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertDispatched('node-saved');

        $this->assertDatabaseHas('odps', [
            'name' => 'ODP Baru Den Roy',
            'code' => 'ODP-DENROY-99',
            'branch_id' => 1,
            'reseller_id' => $reseller->id,
            'created_by' => $reseller->id,
        ]);
    }

    public function test_reseller_can_create_new_odc_via_livewire()
    {
        $reseller = $this->createResellerUser(1);
        $this->actingAs($reseller);

        Livewire::test(Index::class)
            ->call('openCreateModal', 'odc', -6.2050, 106.8050)
            ->assertSet('showModal', true)
            ->assertSet('nodeType', 'odc')
            ->set('nodeName', 'ODC Baru Den Roy')
            ->set('nodeCode', 'ODC-DENROY-88')
            ->set('portCount', 48)
            ->set('latitude', '-6.2050')
            ->set('longitude', '106.8050')
            ->set('address', 'Jl. Raya Cisarap')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertDispatched('node-saved');

        $this->assertDatabaseHas('odcs', [
            'name' => 'ODC Baru Den Roy',
            'code' => 'ODC-DENROY-88',
            'branch_id' => 1,
            'reseller_id' => $reseller->id,
            'created_by' => $reseller->id,
        ]);
    }

    public function test_reseller_can_edit_existing_odp_via_livewire()
    {
        $reseller = $this->createResellerUser(1);

        $odp = Odp::withoutGlobalScopes()->create([
            'name' => 'ODP Lama',
            'code' => 'ODP-LAMA-01',
            'branch_id' => 1,
            'reseller_id' => $reseller->id,
            'port_count' => 8,
            'latitude' => '-6.2020',
            'longitude' => '106.8020',
            'created_by' => $reseller->id,
        ]);

        $this->actingAs($reseller);

        Livewire::test(Index::class)
            ->call('openEditModal', 'odp', $odp->id)
            ->assertSet('showModal', true)
            ->assertSet('nodeName', 'ODP Lama')
            ->set('nodeName', 'ODP Sudah Diperbarui')
            ->set('portCount', 16)
            ->call('save')
            ->assertSet('showModal', false)
            ->assertDispatched('node-saved');

        $this->assertDatabaseHas('odps', [
            'id' => $odp->id,
            'name' => 'ODP Sudah Diperbarui',
            'port_count' => 16,
        ]);
    }
}
