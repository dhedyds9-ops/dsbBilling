<?php

namespace Tests\Feature\Livewire\ISP;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\ISP\Splitter;
use App\Models\ISP\SplitterPort;
use App\Models\User;
use App\Services\ISP\OpticalLinkBudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpticalLinkBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Olt $olt;
    protected PonPort $ponPort;
    protected Odc $odc;
    protected Odp $odp;
    protected Splitter $odcSplitter;
    protected Splitter $odpSplitter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'administrator'],
            ['display_name' => 'Administrator', 'description' => 'Admin Role']
        );
        $this->user->roles()->syncWithoutDetaching([$role->id]);
        $this->actingAs($this->user);

        $this->olt = Olt::create([
            'name' => 'OLT Core Pusat',
            'code' => 'OLT-PST-01',
            'ip_address' => '10.10.10.1',
            'status' => 'active',
            'latitude' => -6.2000,
            'longitude' => 106.8000,
        ]);

        $this->ponPort = PonPort::create([
            'olt_id' => $this->olt->id,
            'name' => 'GPON 0/1/1',
            'code' => 'OLT-PST-01-PON-1',
            'port_number' => 1,
            'port_index' => 1,
            'tx_power_dbm' => 4.50,
            'sfp_class' => 'Class C+',
            'status' => 'active',
        ]);

        $this->odc = Odc::create([
            'olt_id' => $this->olt->id,
            'name' => 'ODC Melati 01',
            'code' => 'ODC-MLT-01',
            'latitude' => -6.2080,
            'longitude' => 106.8080,
            'port_count' => 48,
            'status' => 'active',
        ]);

        $this->odcSplitter = Splitter::create([
            'odc_id' => $this->odc->id,
            'code' => 'SPL-ODC-01',
            'name' => 'Splitter ODC Tier-1',
            'type' => 'plc',
            'ratio_label' => '1:4',
            'port_count' => 4,
            'insertion_loss_db' => 7.20,
            'status' => 'active',
        ]);

        $this->odp = Odp::create([
            'odc_id' => $this->odc->id,
            'olt_id' => $this->olt->id,
            'name' => 'ODP Melati RW 03',
            'code' => 'ODP-MLT-03',
            'latitude' => -6.2120,
            'longitude' => 106.8120,
            'port_count' => 8,
            'status' => 'active',
        ]);

        $this->odpSplitter = Splitter::create([
            'odp_id' => $this->odp->id,
            'code' => 'SPL-ODP-01',
            'name' => 'Splitter ODP Tier-2',
            'type' => 'plc',
            'ratio_label' => '1:8',
            'port_count' => 8,
            'insertion_loss_db' => 10.50,
            'status' => 'active',
        ]);
    }

    public function test_optical_link_budget_calculates_ideal_path_and_segments(): void
    {
        $customer = Customer::create([
            'name' => 'Bpk. Ahmad Dahlan',
            'code' => 'CUST-MLT-001',
            'latitude' => -6.2125,
            'longitude' => 106.8125,
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 12',
            'status' => 'active',
        ]);

        $onu = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port_id' => $this->ponPort->id,
            'odp_id' => $this->odp->id,
            'name' => 'ONU Ahmad',
            'serial_number' => 'ZTEGC1112233',
            'rx_power_dbm' => -15.50,
            'tx_power_dbm' => 2.10,
            'distance_meters' => 1350,
            'status' => 'active',
            'operational_status' => 'online',
        ]);

        CustomerService::create([
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'service_type' => 'broadband',
            'status' => 'active',
        ]);

        /** @var OpticalLinkBudgetService $service */
        $service = app(OpticalLinkBudgetService::class);
        $result = $service->calculateCustomerBudget($customer);

        $this->assertTrue($result['success']);
        $this->assertEquals('Bpk. Ahmad Dahlan', $result['customer']['name']);
        $this->assertEquals('ZTEGC1112233', $result['onu']['serial_number']);
        $this->assertEquals(4.50, $result['olt']['tx_power_dbm']);
        $this->assertCount(6, $result['segments']);

        // Assert segments breakdown
        $segmentKeys = array_column($result['segments'], 'key');
        $this->assertContains('olt_tx', $segmentKeys);
        $this->assertContains('feeder', $segmentKeys);
        $this->assertContains('odc_splitter', $segmentKeys);
        $this->assertContains('distribution', $segmentKeys);
        $this->assertContains('odp_splitter', $segmentKeys);
        $this->assertContains('drop_cable', $segmentKeys);

        // Assert metrics & status
        $this->assertGreaterThan(18.0, $result['metrics']['total_estimated_loss_db']);
        $this->assertEquals(-15.50, $result['metrics']['actual_rx_dbm']);
        $this->assertEquals(1350, $result['metrics']['rtt_distance_meters']);
        $this->assertEquals('ideal', $result['diagnosis']['status']);
    }

    public function test_diagnose_fault_location_pinpoints_drop_cable_when_single_onu_degraded(): void
    {
        // Neighbor ONU on same ODP with normal signal
        Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port_id' => $this->ponPort->id,
            'odp_id' => $this->odp->id,
            'name' => 'ONU Neighbor Good',
            'serial_number' => 'ZTEGC9999001',
            'rx_power_dbm' => -18.50,
            'status' => 'active',
        ]);

        // Target degraded ONU
        $badOnu = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port_id' => $this->ponPort->id,
            'odp_id' => $this->odp->id,
            'name' => 'ONU Degraded Drop',
            'serial_number' => 'ZTEGC9999002',
            'rx_power_dbm' => -29.50, // severely high attenuation
            'status' => 'active',
        ]);

        /** @var OpticalLinkBudgetService $service */
        $service = app(OpticalLinkBudgetService::class);
        $result = $service->calculateOnuBudget($badOnu);

        $this->assertEquals('critical', $result['diagnosis']['status']);
        $this->assertEquals('drop_cable', $result['diagnosis']['fault_segment']);
        $this->assertStringContainsStringIgnoringCase('kabel drop', $result['diagnosis']['recommendation']);
    }

    public function test_diagnose_fault_location_pinpoints_distribution_when_all_onus_on_odp_degraded(): void
    {
        // All ONUs on this ODP are degraded
        $onu1 = Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port_id' => $this->ponPort->id,
            'odp_id' => $this->odp->id,
            'name' => 'ONU ODP 1',
            'serial_number' => 'HWTC11110001',
            'rx_power_dbm' => -28.50,
            'status' => 'active',
        ]);

        Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port_id' => $this->ponPort->id,
            'odp_id' => $this->odp->id,
            'name' => 'ONU ODP 2',
            'serial_number' => 'HWTC11110002',
            'rx_power_dbm' => -29.00,
            'status' => 'active',
        ]);

        Onu::create([
            'olt_id' => $this->olt->id,
            'pon_port_id' => $this->ponPort->id,
            'odp_id' => $this->odp->id,
            'name' => 'ONU ODP 3',
            'serial_number' => 'HWTC11110003',
            'rx_power_dbm' => -28.80,
            'status' => 'active',
        ]);

        /** @var OpticalLinkBudgetService $service */
        $service = app(OpticalLinkBudgetService::class);
        $result = $service->calculateOnuBudget($onu1);

        $this->assertEquals('critical', $result['diagnosis']['status']);
        $this->assertEquals('distribution_cable', $result['diagnosis']['fault_segment']);
        $this->assertStringContainsString('kabel distribusi', strtolower($result['diagnosis']['recommendation']));
    }

    public function test_map_api_controller_optical_budget_endpoint_returns_valid_json(): void
    {
        $customer = Customer::create([
            'name' => 'Siti Nurhaliza',
            'code' => 'CUST-SITI-01',
            'phone' => '081234567899',
            'address' => 'Jl. Kenanga No. 8',
            'status' => 'active',
        ]);

        $onu = Onu::create([
            'olt_id' => $this->olt->id,
            'odp_id' => $this->odp->id,
            'name' => 'ONU Siti',
            'serial_number' => 'CDAT88889999',
            'rx_power_dbm' => -19.50,
            'status' => 'active',
        ]);

        CustomerService::create([
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('map.optical-budget', $customer->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'customer' => ['id', 'code', 'name'],
            'onu' => ['id', 'serial_number'],
            'olt' => ['id', 'tx_power_dbm'],
            'segments',
            'metrics' => ['total_estimated_loss_db', 'theoretical_rx_dbm', 'actual_rx_dbm', 'deviation_db'],
            'diagnosis' => ['status', 'title', 'recommendation'],
        ]);
    }

    public function test_haversine_distance_calculation_accuracy(): void
    {
        // Jakarta Monas (-6.1754, 106.8272) to Bundaran HI (-6.1950, 106.8230) ~ 2.2 km
        $dist = OpticalLinkBudgetService::calculateHaversineDistance(-6.1754, 106.8272, -6.1950, 106.8230);

        $this->assertNotNull($dist);
        $this->assertGreaterThan(2100, $dist);
        $this->assertLessThan(2300, $dist);
    }
}
