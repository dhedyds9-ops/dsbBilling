<?php

namespace Tests\Feature\Livewire\ISP;

use App\Livewire\Isp\Odp\Show as OdpShow;
use App\Livewire\Isp\Odc\Show as OdcShow;
use App\Livewire\Isp\Odp\Create as OdpCreate;
use App\Livewire\Isp\Odp\Edit as OdpEdit;
use App\Livewire\Isp\Odc\Create as OdcCreate;
use App\Livewire\Isp\Odc\Edit as OdcEdit;
use App\Models\ISP\JointClosure;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\Master\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OdpJointClosureHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_joint_closure_hierarchy_and_topology_rendering()
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'MAIN']);
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $olt = Olt::create([
            'branch_id' => $branch->id,
            'name' => 'OLT Core GPON',
            'code' => 'OLT-01',
            'ip_address' => '10.10.10.1',
        ]);

        // Scenario: Parent Closure -> Child Closure (Cascade Closure)
        $parentClosure = JointClosure::create([
            'branch_id' => $branch->id,
            'olt_id' => $olt->id,
            'pon_port' => 1,
            'code' => 'JC-FEEDER-01',
            'name' => 'Closure Feeder Dome',
            'type' => 'dome',
            'port_count' => 48,
            'status' => 'active',
        ]);

        $childClosure = JointClosure::create([
            'branch_id' => $branch->id,
            'parent_closure_id' => $parentClosure->id,
            'code' => 'JC-DIST-01',
            'name' => 'Closure Distribusi Inline',
            'type' => 'inline',
            'port_count' => 24,
            'status' => 'active',
        ]);

        // ODC connected via Feeder Closure
        $odc = Odc::create([
            'branch_id' => $branch->id,
            'olt_id' => $olt->id,
            'joint_closure_id' => $parentClosure->id,
            'name' => 'ODC Sentral',
            'code' => 'ODC-01',
            'port_count' => 48,
        ]);

        // ODP connected via Distribution Closure
        $odp = Odp::create([
            'branch_id' => $branch->id,
            'odc_id' => $odc->id,
            'joint_closure_id' => $childClosure->id,
            'odc_port' => 1,
            'name' => 'ODP Distribusi 01',
            'code' => 'ODP-01',
            'port_count' => 8,
        ]);

        // Assert Model Relationships
        $this->assertEquals($parentClosure->id, $childClosure->parentClosure->id);
        $this->assertTrue($parentClosure->childClosures->contains($childClosure));
        $this->assertEquals($childClosure->id, $odp->jointClosure->id);
        $this->assertEquals($parentClosure->id, $odc->jointClosure->id);
        $this->assertTrue($childClosure->odps->contains($odp));
        $this->assertTrue($parentClosure->odcs->contains($odc));

        // Test ODP Show Topology Tab with Joint Closure
        Livewire::actingAs($user)
            ->test(OdpShow::class, ['id' => $odp->id])
            ->set('activeTab', 'topology')
            ->assertStatus(200)
            ->assertSee('JC-DIST-01')
            ->assertSee('DIST. CLOSURE');

        // Test ODC Show Topology Tab with Joint Closure
        Livewire::actingAs($user)
            ->test(OdcShow::class, ['id' => $odc->id])
            ->set('activeTab', 'topology')
            ->assertStatus(200)
            ->assertSee('JC-FEEDER-01')
            ->assertSee('FEEDER CLOSURE');
    }

    public function test_can_save_odp_with_joint_closure_in_create_and_edit()
    {
        $branch = Branch::create(['name' => 'Cabang B', 'code' => 'BR-B']);
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $odc = Odc::create([
            'branch_id' => $branch->id,
            'name' => 'ODC Barat',
            'code' => 'ODC-B01',
            'port_count' => 24,
        ]);

        $closure = JointClosure::create([
            'branch_id' => $branch->id,
            'name' => 'Closure Dome B',
            'code' => 'JC-B01',
            'type' => 'dome',
            'port_count' => 24,
            'status' => 'active',
        ]);

        // Test Odp Create
        Livewire::actingAs($user)
            ->test(OdpCreate::class)
            ->set('code', 'ODP-NEW-01')
            ->set('name', 'ODP Baru')
            ->set('odc_id', $odc->id)
            ->set('joint_closure_id', $closure->id)
            ->set('odc_port', 2)
            ->set('port_count', 8)
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $newOdp = Odp::where('code', 'ODP-NEW-01')->first();
        $this->assertNotNull($newOdp);
        $this->assertEquals($closure->id, $newOdp->joint_closure_id);

        // Test Odp Edit
        Livewire::actingAs($user)
            ->test(OdpEdit::class, ['id' => $newOdp->id])
            ->set('name', 'ODP Baru Updated')
            ->set('joint_closure_id', null)
            ->call('save')
            ->assertHasNoErrors();

        $newOdp->refresh();
        $this->assertEquals('ODP Baru Updated', $newOdp->name);
        $this->assertNull($newOdp->joint_closure_id);
    }
}
