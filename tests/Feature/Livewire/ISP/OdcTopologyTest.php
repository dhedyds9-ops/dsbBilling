<?php

namespace Tests\Feature\Livewire\ISP;

use App\Livewire\Isp\Odc\Show as OdcShow;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\ISP\Pop;
use App\Models\ISP\Splitter;
use App\Models\Master\Branch;
use App\Models\User;
use App\Services\ISP\OdcTopologyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OdcTopologyTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;
    protected ?Odc $odc = null;
    protected ?Olt $olt = null;
    protected ?Odp $odp = null;
    protected ?Onu $onu = null;
    protected ?Customer $customer = null;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Authenticated user with admin role
        $this->user = User::first() ?? User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['display_name' => 'Super Admin', 'description' => 'Super Admin Role']
        );
        $this->user->roles()->syncWithoutDetaching([$role->id]);
        $this->actingAs($this->user);

        // 2. Setup branch & POP
        $branch = Branch::first() ?? Branch::create(['name' => 'Cabang Pusat', 'code' => 'PST', 'is_active' => true]);
        $pop = Pop::first() ?? Pop::create(['name' => 'POP Pusat', 'code' => 'POP-01', 'status' => 'active']);

        // 3. Setup OLT & PonPort
        $this->olt = Olt::first() ?? Olt::create([
            'name' => 'OLT Test Core',
            'code' => 'OLT-TEST',
            'model' => 'FD1604E',
            'ip_address' => '10.10.10.1',
            'status' => 'active',
            'pop_id' => $pop->id,
            'branch_id' => $branch->id,
            'pon_port_count' => 16,
        ]);

        $ponPort = PonPort::first() ?? PonPort::create([
            'olt_id' => $this->olt->id,
            'code' => 'PON-1',
            'name' => 'GPON Port 1',
            'port_number' => 1,
            'port_index' => 1,
            'status' => 'active',
        ]);

        // 4. Setup ODC
        $this->odc = Odc::first() ?? Odc::create([
            'olt_id' => $this->olt->id,
            'pop_id' => $pop->id,
            'branch_id' => $branch->id,
            'code' => 'ODC-TEST-01',
            'name' => 'ODC Test Cabinet',
            'port_count' => 48,
            'active_port_count' => 2,
            'status' => 'active',
            'latitude' => '-6.2000',
            'longitude' => '106.8166',
        ]);

        // 5. Setup Splitter inside ODC
        Splitter::firstOrCreate(
            ['code' => 'SPL-ODC-TEST'],
            [
                'odc_id' => $this->odc->id,
                'name' => 'Splitter Tray ODC 1:4',
                'ratio' => 4,
                'port_count' => 4,
                'status' => 'active',
            ]
        );

        // 6. Setup ODP connected to ODC
        $this->odp = Odp::withoutGlobalScopes()->where('odc_id', $this->odc->id)->first();
        if (!$this->odp) {
            $this->odp = Odp::create([
                'odc_id' => $this->odc->id,
                'olt_id' => $this->olt->id,
                'pon_port_id' => $ponPort->id,
                'odc_port' => 1,
                'code' => 'ODP-TEST-01',
                'name' => 'ODP Test Box',
                'port_count' => 8,
                'used_port_count' => 1,
                'split_ratio' => 8,
                'status' => 'active',
                'branch_id' => $branch->id,
            ]);
        }

        // 7. Setup ONU
        $this->onu = Onu::withoutGlobalScopes()->where('odp_id', $this->odp->id)->first();
        if (!$this->onu) {
            $this->onu = Onu::create([
                'odp_id' => $this->odp->id,
                'olt_id' => $this->olt->id,
                'pon_port_id' => $ponPort->id,
                'code' => 'ONU-TEST-01',
                'name' => 'ONU Pelanggan Test',
                'serial_number' => 'ZTEG12345678',
                'model' => 'F609',
                'operational_status' => 'online',
                'rx_power_dbm' => -20.5,
                'status' => 'active',
                'branch_id' => $branch->id,
            ]);
        }

        // 8. Setup Customer & CustomerService
        $this->customer = Customer::withoutGlobalScopes()->first() ?? Customer::create([
            'code' => 'CUST-TEST-01',
            'name' => 'Budi Testing',
            'phone' => '081234567890',
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);

        $cs = CustomerService::withoutGlobalScopes()->where('onu_id', $this->onu->id)->first();
        if (!$cs) {
            CustomerService::create([
                'customer_id' => $this->customer->id,
                'onu_id' => $this->onu->id,
                'username' => 'budi_test',
                'password' => 'secret123',
                'status' => 'active',
                'branch_id' => $branch->id,
            ]);
        }
    }

    public function test_odc_detail_renders_successfully_with_header_and_kpi()
    {
        Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->assertStatus(200)
            ->assertSee($this->odc->name)
            ->assertSee($this->odc->code)
            ->assertSee('Overview')
            ->assertSee('Topologi Jaringan')
            ->assertSee('Port Management')
            ->assertSee('ODP Terhubung')
            ->assertSee('Pelanggan')
            ->assertSee('Riwayat');
    }

    public function test_odc_topology_tab_loads_complete_graph_nodes_and_edges()
    {
        $component = Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->call('setTab', 'topology')
            ->assertStatus(200)
            ->assertSet('activeTab', 'topology');

        $topo = $component->get('topologyData');
        $this->assertNotEmpty($topo['nodes'], 'Topology nodes should not be empty');
        $this->assertNotEmpty($topo['edges'], 'Topology edges should not be empty');

        $groups = array_column($topo['nodes'], 'group');
        $this->assertContains('olt', $groups, 'Topology must contain OLT node');
        $this->assertContains('feeder', $groups, 'Topology must contain Feeder node');
        $this->assertContains('odc', $groups, 'Topology must contain ODC node');
        $this->assertContains('odp', $groups, 'Topology must contain ODP node');
        $this->assertContains('onu', $groups, 'Topology must contain ONU node');
        $this->assertContains('customer', $groups, 'Topology must contain Customer node');
    }

    public function test_odc_port_management_tab_displays_matrix_and_filters()
    {
        $component = Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->call('setTab', 'ports')
            ->assertStatus(200)
            ->assertSet('activeTab', 'ports');

        $ports = $component->get('ports');
        $this->assertCount((int) $this->odc->port_count, $ports, 'Should display all ports of ODC');

        // Check port 1 connected to ODP
        $port1 = $ports[0];
        $this->assertEquals(1, $port1['port_number']);
        $this->assertEquals('used', $port1['status']);
        $this->assertNotEmpty($port1['odp_code']);

        // Check filter by available
        $component->set('portStatusFilter', 'available');
        $availPorts = $component->get('ports');
        $this->assertGreaterThan(0, count($availPorts));
        $this->assertEquals('available', $availPorts[0]['status']);
    }

    public function test_odc_connected_odps_tab_lists_distribution_points()
    {
        Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->call('setTab', 'odps')
            ->assertStatus(200)
            ->assertSee($this->odp->code)
            ->assertSee($this->odp->name);
    }

    public function test_odc_customers_tab_lists_downstream_subscribers()
    {
        Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->call('setTab', 'customers')
            ->assertStatus(200)
            ->assertSee($this->customer->name)
            ->assertSee($this->onu->serial_number);
    }

    public function test_odc_topology_tracing_and_node_selection()
    {
        $component = Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->call('selectNode', 'odp-' . $this->odp->id)
            ->assertSet('showNodeDrawer', true)
            ->assertSet('selectedNodeId', 'odp-' . $this->odp->id);

        $node = $component->get('selectedNode');
        $this->assertEquals('odp', $node['group']);

        // Trace by customer name
        $component->set('traceQuery', substr($this->customer->name, 0, 4));
        $component->call('tracePath');
        $this->assertNotNull($component->get('tracedNodeId'));

        // Clear trace
        $component->call('clearTrace');
        $this->assertNull($component->get('tracedNodeId'));
    }

    public function test_odc_topology_service_unit_calculations()
    {
        $service = app(OdcTopologyService::class);
        $summary = $service->getSummary($this->odc);

        $this->assertEquals((int) $this->odc->port_count, $summary['total_ports']);
        $this->assertGreaterThanOrEqual(1, $summary['used_ports']);
        $this->assertGreaterThan(0, $summary['available_ports']);
        $this->assertGreaterThanOrEqual(1, $summary['odps_count']);
        $this->assertGreaterThanOrEqual(1, $summary['onus_count']);
        $this->assertGreaterThanOrEqual(1, $summary['customers_count']);
    }

    public function test_odc_port_assignment_and_disconnection()
    {
        // Create an unassigned ODP
        $newOdp = Odp::create([
            'code' => 'ODP-UNASSIGNED',
            'name' => 'ODP Belum Terhubung',
            'port_count' => 8,
            'status' => 'active',
            'branch_id' => $this->odc->branch_id,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->call('setTab', 'ports')
            ->call('openPortModal', 10)
            ->assertSet('showPortModal', true)
            ->assertSet('targetPortNumber', 10);

        // Assign to port 10
        $component->set('selectedOdpIdForPort', $newOdp->id)
            ->call('assignPort')
            ->assertSet('showPortModal', false);

        $newOdp->refresh();
        $this->assertEquals($this->odc->id, $newOdp->odc_id);
        $this->assertEquals(10, $newOdp->odc_port);

        // Disconnect port 10
        $component->call('disconnectPort', 10);
        $newOdp->refresh();
        $this->assertNull($newOdp->odc_port);
    }

    public function test_optical_link_budget_calculation()
    {
        $component = Livewire::actingAs($this->user)
            ->test(OdcShow::class, ['id' => $this->odc->id])
            ->call('selectNode', 'onu-' . $this->onu->id);

        $budget = $component->get('linkBudget');
        $this->assertNotNull($budget, 'Link budget must be computed for selected ONU');
        $this->assertArrayHasKey('tx_olt', $budget);
        $this->assertArrayHasKey('total_attenuation', $budget);
        $this->assertArrayHasKey('calculated_rx', $budget);
        $this->assertArrayHasKey('actual_rx', $budget);
        $this->assertArrayHasKey('status', $budget);
    }
}
