<?php

namespace Tests\Feature\Livewire\ISP;

use App\Livewire\Isp\Odc\Create as OdcCreate;
use App\Livewire\Isp\Odc\Edit as OdcEdit;
use App\Livewire\Isp\Odp\Create as OdpCreate;
use App\Livewire\Isp\Odp\Edit as OdpEdit;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OdcOdpFormsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Olt $olt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $role = Role::firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Administrator', 'description' => 'Admin Role']
        );
        $this->user->roles()->syncWithoutDetaching([$role->id]);
        $this->actingAs($this->user);

        $this->branch = Branch::firstOrCreate(
            ['code' => 'CBG-01'],
            ['name' => 'Cabang Utama', 'is_active' => true]
        );

        $this->olt = Olt::create([
            'name' => 'OLT Pusat',
            'code' => 'OLT-PST',
            'ip_address' => '10.10.10.1',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_can_create_tier1_odc_with_direct_olt_uplink(): void
    {
        Livewire::test(OdcCreate::class)
            ->set('code', 'ODC-T1-TEST')
            ->set('name', 'ODC Tier 1 Test')
            ->set('uplink_type', 'olt')
            ->set('olt_id', $this->olt->id)
            ->set('status', 'active')
            ->set('port_count', 48)
            ->call('save')
            ->assertRedirect(route('isp.odcs.index'));

        $this->assertDatabaseHas('odcs', [
            'code' => 'ODC-T1-TEST',
            'olt_id' => $this->olt->id,
            'parent_odc_id' => null,
        ]);
    }

    public function test_can_create_tier2_cascade_sub_odc_with_parent_odc(): void
    {
        $parentOdc = Odc::create([
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'code' => 'ODC-PARENT-01',
            'name' => 'Induk ODC Utama',
            'status' => 'active',
            'port_count' => 48,
        ]);

        Livewire::test(OdcCreate::class)
            ->set('code', 'ODC-SUB-01')
            ->set('name', 'Sub ODC Cascade')
            ->set('uplink_type', 'odc')
            ->set('parent_odc_id', $parentOdc->id)
            ->set('status', 'active')
            ->set('port_count', 24)
            ->call('save')
            ->assertRedirect(route('isp.odcs.index'));

        $savedSub = Odc::where('code', 'ODC-SUB-01')->first();
        $this->assertNotNull($savedSub);
        $this->assertEquals($parentOdc->id, $savedSub->parent_odc_id);
        $this->assertEquals($this->olt->id, $savedSub->olt_id); // Auto-inherited
    }

    public function test_can_edit_odc_and_switch_to_cascade(): void
    {
        $parentOdc = Odc::create([
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'code' => 'ODC-PARENT-02',
            'name' => 'Induk ODC 02',
            'status' => 'active',
        ]);

        $subOdc = Odc::create([
            'olt_id' => $this->olt->id,
            'code' => 'ODC-STANDALONE',
            'name' => 'Standalone ODC',
            'status' => 'active',
        ]);

        Livewire::test(OdcEdit::class, ['id' => $subOdc->id])
            ->set('uplink_type', 'odc')
            ->set('parent_odc_id', $parentOdc->id)
            ->call('save')
            ->assertRedirect(route('isp.odcs.show', $subOdc->id));

        $subOdc->refresh();
        $this->assertEquals($parentOdc->id, $subOdc->parent_odc_id);
    }

    public function test_can_create_odp_with_odc_uplink(): void
    {
        $odc = Odc::create([
            'olt_id' => $this->olt->id,
            'code' => 'ODC-FOR-ODP',
            'name' => 'ODC For ODP',
            'status' => 'active',
        ]);

        Livewire::test(OdpCreate::class)
            ->set('code', 'ODP-TEST-01')
            ->set('name', 'ODP Melati 01')
            ->set('uplink_type', 'odc')
            ->set('odc_id', $odc->id)
            ->set('odc_port', 2)
            ->set('status', 'active')
            ->set('port_count', 8)
            ->call('save')
            ->assertRedirect(route('isp.odps.index'));

        $this->assertDatabaseHas('odps', [
            'code' => 'ODP-TEST-01',
            'odc_id' => $odc->id,
            'odc_port' => 2,
            'parent_odp_id' => null,
        ]);
    }

    public function test_can_create_cascade_sub_odp_with_parent_odp(): void
    {
        $parentOdp = Odp::create([
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'code' => 'ODP-PARENT-01',
            'name' => 'Induk ODP 01',
            'status' => 'active',
            'port_count' => 8,
        ]);

        Livewire::test(OdpCreate::class)
            ->set('code', 'ODP-CHILD-01')
            ->set('name', 'Sub ODP Cascade')
            ->set('uplink_type', 'odp')
            ->set('parent_odp_id', $parentOdp->id)
            ->set('status', 'active')
            ->set('port_count', 8)
            ->call('save')
            ->assertRedirect(route('isp.odps.index'));

        $savedSubOdp = Odp::where('code', 'ODP-CHILD-01')->first();
        $this->assertNotNull($savedSubOdp);
        $this->assertEquals($parentOdp->id, $savedSubOdp->parent_odp_id);
        $this->assertEquals($this->olt->id, $savedSubOdp->olt_id); // Auto-inherited
    }

    public function test_can_edit_odp(): void
    {
        $odc = Odc::create([
            'olt_id' => $this->olt->id,
            'code' => 'ODC-ODP-EDIT',
            'name' => 'ODC For Edit',
            'status' => 'active',
        ]);

        $odp = Odp::create([
            'olt_id' => $this->olt->id,
            'odc_id' => $odc->id,
            'code' => 'ODP-EDIT-01',
            'name' => 'ODP Original',
            'status' => 'active',
        ]);

        Livewire::test(OdpEdit::class, ['id' => $odp->id])
            ->set('name', 'ODP Renamed')
            ->call('save')
            ->assertRedirect(route('isp.odps.show', $odp->id));

        $odp->refresh();
        $this->assertEquals('ODP Renamed', $odp->name);
    }
}
