<?php

namespace Tests\Feature\Livewire\ISP;

use App\Livewire\Isp\Odp\Show;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\Master\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OdpShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render_odp_show_and_switch_to_topology_tab()
    {
        $branch = Branch::create(['name' => 'Cabang Test', 'code' => 'TEST']);
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $olt = Olt::create([
            'branch_id' => $branch->id,
            'name' => 'OLT Test',
            'code' => 'OLT-01',
            'ip_address' => '10.10.10.1',
        ]);

        $odc = Odc::create([
            'branch_id' => $branch->id,
            'olt_id' => $olt->id,
            'name' => 'ODC Test',
            'code' => 'ODC-01',
            'port_count' => 48,
        ]);

        $odp = Odp::create([
            'branch_id' => $branch->id,
            'odc_id' => $odc->id,
            'odc_port' => 1,
            'name' => 'ODP 3682',
            'code' => 'ODP-3682',
            'port_count' => 8,
        ]);

        $component = Livewire::actingAs($user)
            ->test(Show::class, ['id' => $odp->id])
            ->assertStatus(200);

        $component->call('setTab', 'topology')
            ->assertStatus(200)
            ->assertSee('Diagram Topologi Visual Distribusi Fiber Optik')
            ->assertSee('ODP 3682');
    }

    public function test_it_can_render_odp_show_without_odc_or_without_olt()
    {
        $branch = Branch::create(['name' => 'Cabang Test 2', 'code' => 'TEST2']);
        $user = User::factory()->create(['branch_id' => $branch->id]);

        // ODP orphan (no ODC, no OLT)
        $odpOrphan = Odp::create([
            'branch_id' => $branch->id,
            'name' => 'ODP Orphan',
            'code' => 'ODP-ORPHAN',
            'port_count' => 8,
        ]);

        $component = Livewire::actingAs($user)
            ->test(Show::class, ['id' => $odpOrphan->id])
            ->assertStatus(200);

        $component->call('setTab', 'topology')
            ->assertStatus(200)
            ->assertSee('Diagram Topologi Visual Distribusi Fiber Optik');
    }
}
