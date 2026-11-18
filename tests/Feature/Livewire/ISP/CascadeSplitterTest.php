<?php

namespace Tests\Feature\Livewire\ISP;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\Pop;
use App\Models\ISP\Splitter;
use App\Models\ISP\SplitterPort;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\ISP\OdcTopologyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CascadeSplitterTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;
    protected ?Branch $branch = null;
    protected ?Olt $olt = null;
    protected ?Odc $odc = null;
    protected ?Odp $odp = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $role = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['display_name' => 'Super Admin', 'description' => 'Super Admin Role']
        );
        $this->user->roles()->syncWithoutDetaching([$role->id]);
        $this->actingAs($this->user);

        $this->branch = Branch::first() ?: Branch::create([
            'code' => 'BR-01',
            'name' => 'Cabang Utama',
            'status' => 'active',
        ]);

        $pop = Pop::first() ?: Pop::create([
            'code' => 'POP-01',
            'name' => 'POP Pusat',
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $this->olt = Olt::create([
            'pop_id' => $pop->id,
            'branch_id' => $this->branch->id,
            'code' => 'OLT-SPL-01',
            'name' => 'OLT Test Splitter',
            'model' => 'ZTE C320',
            'ip_address' => '10.10.10.1',
            'pon_port_count' => 16,
            'status' => 'active',
        ]);

        $this->odc = Odc::create([
            'olt_id' => $this->olt->id,
            'pop_id' => $pop->id,
            'branch_id' => $this->branch->id,
            'code' => 'ODC-SPL-01',
            'name' => 'ODC Cabinet Cascade Test',
            'port_count' => 48,
            'active_port_count' => 0,
            'status' => 'active',
        ]);

        $this->odp = Odp::create([
            'odc_id' => $this->odc->id,
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'code' => 'ODP-SPL-01',
            'name' => 'ODP Distribution Box Test',
            'port_count' => 8,
            'used_port_count' => 0,
            'status' => 'active',
        ]);
    }

    public function test_can_create_plc_and_fbt_splitters_with_auto_generated_ports(): void
    {
        // 1. Create PLC Splitter 1:8
        $plcSplitter = Splitter::create([
            'odc_id' => $this->odc->id,
            'code' => 'SPL-PLC-1-8',
            'name' => 'Splitter PLC Tray A',
            'type' => 'plc',
            'ratio_label' => '1:8',
            'package_type' => 'tray',
            'input_port_count' => 1,
            'port_count' => 8,
            'status' => 'active',
        ]);

        $plcSplitter->syncPorts();

        $this->assertDatabaseHas('splitters', [
            'id' => $plcSplitter->id,
            'type' => 'plc',
            'ratio_label' => '1:8',
            'insertion_loss_db' => 10.50,
        ]);

        $this->assertEquals(1, $plcSplitter->inputPorts()->count());
        $this->assertEquals(8, $plcSplitter->outputPorts()->count());

        $firstPort = $plcSplitter->outputPorts()->where('port_number', 1)->first();
        $this->assertEquals('Blue', $firstPort->core_color);
        $this->assertEquals(10.50, $firstPort->attenuation_db);
        $this->assertEquals('available', $firstPort->status);

        // 2. Create FBT Splitter 70:30
        $fbtSplitter = Splitter::create([
            'odc_id' => $this->odc->id,
            'code' => 'SPL-FBT-70-30',
            'name' => 'Splitter FBT Coupler',
            'type' => 'fbt',
            'ratio_label' => '70:30',
            'package_type' => 'mini_tube',
            'input_port_count' => 1,
            'port_count' => 2,
            'status' => 'active',
        ]);

        $fbtSplitter->syncPorts();

        $this->assertDatabaseHas('splitters', [
            'id' => $fbtSplitter->id,
            'type' => 'fbt',
            'ratio_label' => '70:30',
            'insertion_loss_db' => 5.60,
        ]);

        $this->assertEquals(2, $fbtSplitter->outputPorts()->count());
    }

    public function test_can_install_splitter_in_odc_via_livewire(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Isp\Odc\Show::class, ['id' => $this->odc->id])
            ->call('openAddSplitterModal')
            ->assertSet('showSplitterModal', true)
            ->set('splitterCode', 'SPL-ODC-T1')
            ->set('splitterName', 'Splitter Tray 1')
            ->set('splitterType', 'plc')
            ->set('splitterRatio', '1:4')
            ->set('splitterPortCount', 4)
            ->set('splitterInputPorts', 1)
            ->call('createSplitter')
            ->assertSet('showSplitterModal', false);

        $this->assertDatabaseHas('splitters', [
            'odc_id' => $this->odc->id,
            'code' => 'SPL-ODC-T1',
            'type' => 'plc',
            'ratio_label' => '1:4',
            'port_count' => 4,
        ]);

        $splitter = Splitter::where('code', 'SPL-ODC-T1')->first();
        $this->assertEquals(4, $splitter->outputPorts()->count());
    }

    public function test_can_assign_and_disconnect_odc_splitter_port_to_odp(): void
    {
        $splitter = Splitter::create([
            'odc_id' => $this->odc->id,
            'code' => 'SPL-ODC-TEST-01',
            'name' => 'Splitter Tray Test',
            'type' => 'plc',
            'ratio_label' => '1:8',
            'port_count' => 8,
            'status' => 'active',
        ]);
        $splitter->syncPorts();

        $port = $splitter->outputPorts()->where('port_number', 1)->first();

        // Assign Port to ODP via Livewire
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Isp\Odc\Show::class, ['id' => $this->odc->id])
            ->call('openSplitterPortModal', $port->id)
            ->assertSet('showSplitterPortModal', true)
            ->set('portFormStatus', 'used')
            ->set('portFormTargetType', 'odp')
            ->set('portFormTargetId', $this->odp->id)
            ->set('portFormCoreColor', 'Blue')
            ->call('saveSplitterPort')
            ->assertSet('showSplitterPortModal', false);

        $port->refresh();
        $this->assertEquals('used', $port->status);
        $this->assertEquals(Odp::class, $port->target_type);
        $this->assertEquals($this->odp->id, $port->target_id);

        // Disconnect Port
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Isp\Odc\Show::class, ['id' => $this->odc->id])
            ->call('disconnectSplitterPort', $port->id);

        $port->refresh();
        $this->assertEquals('available', $port->status);
        $this->assertNull($port->target_id);
    }

    public function test_can_install_splitter_in_odp_via_livewire(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Isp\Odp\Show::class, ['id' => $this->odp->id])
            ->call('openAddSplitterModal')
            ->assertSet('showSplitterModal', true)
            ->set('splitterCode', 'SPL-ODP-B1')
            ->set('splitterName', 'Splitter Box 1')
            ->set('splitterType', 'plc')
            ->set('splitterRatio', '1:8')
            ->set('splitterPortCount', 8)
            ->set('splitterInputPorts', 1)
            ->call('createSplitter')
            ->assertSet('showSplitterModal', false);

        $this->assertDatabaseHas('splitters', [
            'odp_id' => $this->odp->id,
            'code' => 'SPL-ODP-B1',
            'type' => 'plc',
            'port_count' => 8,
        ]);
    }

    public function test_can_assign_odp_splitter_port_to_onu(): void
    {
        $splitter = Splitter::create([
            'odp_id' => $this->odp->id,
            'code' => 'SPL-ODP-ASSIGN',
            'name' => 'Splitter ODP Assign',
            'type' => 'plc',
            'ratio_label' => '1:8',
            'port_count' => 8,
            'status' => 'active',
        ]);
        $splitter->syncPorts();

        $onu = Onu::create([
            'odp_id' => $this->odp->id,
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'code' => 'ONU-TEST-001',
            'name' => 'Modem Pelanggan 01',
            'serial_number' => 'ZTEGC1234567',
            'mac_address' => '00:11:22:33:44:55',
            'status' => 'active',
        ]);

        $port = $splitter->outputPorts()->where('port_number', 1)->first();

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Isp\Odp\Show::class, ['id' => $this->odp->id])
            ->call('openSplitterPortModal', $port->id)
            ->assertSet('showSplitterPortModal', true)
            ->set('spPortFormStatus', 'used')
            ->set('spPortFormTargetType', 'onu')
            ->set('spPortFormTargetId', $onu->id)
            ->call('saveSplitterPort')
            ->assertSet('showSplitterPortModal', false);

        $port->refresh();
        $this->assertEquals('used', $port->status);
        $this->assertEquals(Onu::class, $port->target_type);
        $this->assertEquals($onu->id, $port->target_id);
    }

    public function test_topology_service_renders_cascade_splitter_nodes_independently(): void
    {
        // 1. ODC Splitters: 1 PLC and 1 FBT
        $splOdc1 = Splitter::create([
            'odc_id' => $this->odc->id,
            'code' => 'SPL-ODC-PLC-1',
            'name' => 'Splitter ODC PLC 1',
            'type' => 'plc',
            'ratio_label' => '1:4',
            'port_count' => 4,
            'status' => 'active',
        ]);
        $splOdc1->syncPorts();

        $splOdc2 = Splitter::create([
            'odc_id' => $this->odc->id,
            'code' => 'SPL-ODC-FBT-1',
            'name' => 'Splitter ODC FBT Tap',
            'type' => 'fbt',
            'ratio_label' => '70:30',
            'port_count' => 2,
            'status' => 'active',
        ]);
        $splOdc2->syncPorts();

        // Connect ODC Splitter 1 Port 1 to ODP
        $odcPort1 = $splOdc1->outputPorts()->where('port_number', 1)->first();
        $odcPort1->assignTarget($this->odp, 1, "{$this->odp->name} [{$this->odp->code}]");

        // 2. ODP Splitter: 1 PLC 1:8
        $splOdp = Splitter::create([
            'odp_id' => $this->odp->id,
            'code' => 'SPL-ODP-BOX-1',
            'name' => 'Splitter Box ODP 1',
            'type' => 'plc',
            'ratio_label' => '1:8',
            'port_count' => 8,
            'status' => 'active',
        ]);
        $splOdp->syncPorts();

        // 3. ONU & Customer
        $onu = Onu::create([
            'odp_id' => $this->odp->id,
            'olt_id' => $this->olt->id,
            'branch_id' => $this->branch->id,
            'code' => 'ONU-TEST-002',
            'name' => 'ONU Budi',
            'serial_number' => 'ZTEGCAABBCCDD',
            'status' => 'active',
            'operational_status' => 'online',
        ]);

        $odpPort1 = $splOdp->outputPorts()->where('port_number', 1)->first();
        $odpPort1->assignTarget($onu, null, "{$onu->name} [{$onu->serial_number}]");

        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        CustomerService::create([
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'service_type' => 'pppoe',
            'username' => 'budi_pppoe',
            'status' => 'active',
        ]);

        // Generate Topology
        $service = app(OdcTopologyService::class);
        $topo = $service->getTopologyData($this->odc);

        $nodeIds = array_column($topo['nodes'], 'id');

        // Assert independent nodes exist
        $this->assertContains('spl-odc-' . $splOdc1->id, $nodeIds);
        $this->assertContains('spl-odc-' . $splOdc2->id, $nodeIds);
        $this->assertContains('odp-' . $this->odp->id, $nodeIds);
        $this->assertContains('spl-odp-' . $splOdp->id, $nodeIds);
        $this->assertContains('onu-' . $onu->id, $nodeIds);
        $this->assertContains('cust-' . $customer->id, $nodeIds);

        // Assert edges follow the cascade hierarchy:
        // ODC Splitter -> ODP
        $edgeOdcToOdp = collect($topo['edges'])->first(fn($e) => $e['from'] === 'spl-odc-' . $splOdc1->id && $e['to'] === 'odp-' . $this->odp->id);
        $this->assertNotNull($edgeOdcToOdp, 'Edge from ODC Splitter to ODP must exist');

        // ODP -> ODP Splitter
        $edgeOdpToSpl = collect($topo['edges'])->first(fn($e) => $e['from'] === 'odp-' . $this->odp->id && $e['to'] === 'spl-odp-' . $splOdp->id);
        $this->assertNotNull($edgeOdpToSpl, 'Edge from ODP to ODP Splitter must exist');

        // ODP Splitter -> ONU
        $edgeSplToOnu = collect($topo['edges'])->first(fn($e) => $e['from'] === 'spl-odp-' . $splOdp->id && $e['to'] === 'onu-' . $onu->id);
        $this->assertNotNull($edgeSplToOnu, 'Edge from ODP Splitter to ONU must exist');
    }
}
