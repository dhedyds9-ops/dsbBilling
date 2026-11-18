<?php

namespace Tests\Feature;

use App\Livewire\Isp\Odp\Show;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OdpDetailFiberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Branch $branch;
    protected Odc $odc;
    protected Odp $odp;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);

        $this->branch = Branch::create([
            'code' => 'BR-01',
            'name' => 'Cabang Utama',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'NOC Admin',
            'email' => 'admin_odp_test@dsbi.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
        ]);
        $this->admin->attachRole('admin');

        $this->odc = Odc::create([
            'code' => 'ODC-TEST-01',
            'name' => 'ODC Utama Test',
            'port_count' => 48,
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $this->odp = Odp::create([
            'odc_id' => $this->odc->id,
            'odc_port' => 1,
            'code' => 'ODP-TEST-01',
            'name' => 'ODP Test Cluster 1',
            'port_count' => 8,
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_odp_detail_page_renders_successfully()
    {
        $this->actingAs($this->admin);

        Livewire::test(Show::class, ['id' => $this->odp->id])
            ->assertStatus(200)
            ->assertSee('ODP • OPTICAL DISTRIBUTION POINT')
            ->assertSee('ODP Test Cluster 1')
            ->assertSee('ODP-TEST-01')
            ->assertSee('ODC Utama Test')
            ->assertSee('Total Port');
    }

    public function test_odp_detail_tabs_switch_correctly()
    {
        $this->actingAs($this->admin);

        Livewire::test(Show::class, ['id' => $this->odp->id])
            ->assertSet('activeTab', 'overview')
            ->call('setTab', 'topology')
            ->assertSet('activeTab', 'topology')
            ->assertSee('Topologi Visual Distribusi')
            ->call('setTab', 'ports')
            ->assertSet('activeTab', 'ports')
            ->assertSee('Manajemen Alokasi Port ODP')
            ->call('setTab', 'onus')
            ->assertSet('activeTab', 'onus')
            ->assertSee('Telemetri Perangkat ONT')
            ->call('setTab', 'customers')
            ->assertSet('activeTab', 'customers')
            ->assertSee('Daftar Pelanggan')
            ->call('setTab', 'history')
            ->assertSet('activeTab', 'history')
            ->assertSee('Riwayat Mutasi Port');
    }

    public function test_port_configuration_can_be_updated()
    {
        $this->actingAs($this->admin);

        Livewire::test(Show::class, ['id' => $this->odp->id])
            ->call('openPortModal', 3)
            ->assertSet('showPortModal', true)
            ->assertSet('selectedPortNumber', 3)
            ->set('portFormStatus', 'reserved')
            ->set('portFormNotes', 'Booking PSB besok pagi')
            ->set('portFormDropcore', 75)
            ->call('savePortDetails')
            ->assertSet('showPortModal', false);

        $this->odp->refresh();
        $attrs = $this->odp->attributes;
        $this->assertEquals('reserved', $attrs['ports'][3]['status']);
        $this->assertEquals('Booking PSB besok pagi', $attrs['ports'][3]['notes']);
        $this->assertEquals(75, $attrs['ports'][3]['dropcore_length']);
    }

    public function test_port_move_validation_prevents_collision()
    {
        $this->actingAs($this->admin);

        // Set port 1 and port 2 with custom configurations
        $attrs = [
            'ports' => [
                1 => ['status' => 'used', 'notes' => 'Pelanggan A'],
                2 => ['status' => 'used', 'notes' => 'Pelanggan B'],
            ]
        ];
        $this->odp->setAttribute('attributes', $attrs);
        $this->odp->save();

        Livewire::test(Show::class, ['id' => $this->odp->id])
            ->call('openPortModal', 1)
            ->set('targetMovePort', 2)
            ->call('movePort')
            ->assertSee('sudah digunakan');
    }

    public function test_port_disconnect_clears_relation()
    {
        $this->actingAs($this->admin);

        $attrs = [
            'ports' => [
                1 => ['status' => 'used', 'notes' => 'Pelanggan Lama'],
            ]
        ];
        $this->odp->setAttribute('attributes', $attrs);
        $this->odp->save();

        Livewire::test(Show::class, ['id' => $this->odp->id])
            ->call('openPortModal', 1)
            ->call('disconnectPort', 1)
            ->assertSee('berhasil diputuskan');

        $this->odp->refresh();
        $freshAttrs = $this->odp->getAttribute('attributes');
        $this->assertEquals('available', $freshAttrs['ports'][1]['status']);
    }

    public function test_branch_scope_isolation_enforced()
    {
        Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager']);

        $branchA = Branch::create([
            'id' => 98,
            'code' => 'BR-98',
            'name' => 'Cabang Barat',
            'is_active' => true,
        ]);

        $branchB = Branch::create([
            'id' => 99,
            'code' => 'BR-99',
            'name' => 'Cabang Timur',
            'is_active' => true,
        ]);

        $odpBranchA = Odp::create([
            'code' => 'ODP-BR98-01',
            'name' => 'ODP Barat',
            'port_count' => 8,
            'status' => 'active',
            'branch_id' => $branchA->id,
        ]);

        $managerBranchB = User::create([
            'name' => 'Manager Timur',
            'email' => 'manager_br99@dsbi.com',
            'password' => bcrypt('password'),
            'branch_id' => $branchB->id,
        ]);
        $managerBranchB->attachRole('manager');

        $this->actingAs($managerBranchB);

        // Attempting to access an ODP from branch A by manager of branch B should throw ModelNotFoundException
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(Show::class, ['id' => $odpBranchA->id]);
    }
}
