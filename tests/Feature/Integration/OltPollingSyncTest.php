<?php

namespace Tests\Feature\Integration;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\Adapters\Provisioning\Contracts\OltDriverInterface;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\ISP\OltPollingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OltPollingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;
    protected Branch $branch2;
    protected User $managerBranch1;
    protected Olt $olt;
    protected PonPort $ponPort;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch1 = Branch::create(['name' => 'Cabang 1', 'code' => 'BR1']);
        $this->branch2 = Branch::create(['name' => 'Cabang 2', 'code' => 'BR2']);

        $managerRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager', 'guard_name' => 'web']);

        $this->managerBranch1 = User::factory()->create([
            'name' => 'Manager Branch 1',
            'email' => 'mgr1@dsbilling.test',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);
        $this->managerBranch1->roles()->attach($managerRole->id);

        $this->olt = Olt::create([
            'name' => 'CDATA-HOME',
            'code' => 'OLT-CDATA-1',
            'ip_address' => '10.10.10.1',
            'host' => '10.10.10.1',
            'port' => 23,
            'community' => 'public',
            'branch_id' => $this->branch1->id,
            'status' => 'active',
            'type' => 'cdata',
        ]);

        $this->ponPort = PonPort::create([
            'olt_id' => $this->olt->id,
            'name' => 'EPON0/1',
            'code' => 'PON-EPON0-1',
            'port_number' => 1,
            'status' => 'up',
        ]);
    }

    public function test_olt_sync_handles_existing_cross_branch_onu_without_duplicate_key_error(): void
    {
        // ONU ini dibuat di branch 2 (misal sebelum dipindah ke branch 1 atau OLT ini)
        $existingOnu = Onu::create([
            'name' => 'ONU Existing Branch 2',
            'serial_number' => 'ZTEGC85E2D31',
            'mac_address' => 'AA:BB:CC:85:E2:31',
            'branch_id' => $this->branch2->id,
            'status' => 'inactive',
        ]);

        // Simulasikan user Manager Branch 1 login dan melakukan sinkronisasi
        $this->actingAs($this->managerBranch1);

        $mockDriver = Mockery::mock(OltDriverInterface::class);
        $mockDriver->shouldReceive('getSystemInfo')->andReturn([
            'status' => 'online',
            'cpu_usage' => 10,
            'memory_usage' => 20,
            'temperature' => 45,
            'uptime' => '10 days',
        ]);
        $mockDriver->shouldReceive('getPonPortsStatus')->andReturn([
            [
                'port_name' => 'EPON0/1',
                'port_number' => 1,
                'port_index' => 1,
                'status' => 'up',
            ],
        ]);
        $mockDriver->shouldReceive('getOnuRxPower')->with(1)->andReturn([
            [
                'onu_index' => 1,
                'serial_number' => 'ZTEGC85E2D31',
                'mac_address' => 'AA:BB:CC:85:E2:31',
                'rx_power_dbm' => -19.5,
                'tx_power_dbm' => 2.1,
                'status' => 'online',
            ],
        ]);

        $mockRegistry = Mockery::mock(OltRegistry::class);
        $mockRegistry->shouldReceive('forOlt')->with(Mockery::on(fn($o) => $o->id === $this->olt->id))->andReturn($mockDriver);

        $service = new OltPollingService($mockRegistry);
        $result = $service->pollOlt($this->olt);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['onu_online']);

        // Verifikasi ONU tidak duplikat dan di-update dengan benar
        $onus = Onu::withoutGlobalScopes()->where('serial_number', 'ZTEGC85E2D31')->get();
        $this->assertCount(1, $onus, 'Record ONU tidak boleh duplikat');

        $updated = $onus->first();
        $this->assertEquals($this->olt->id, $updated->olt_id);
        $this->assertEquals('active', $updated->status);
        $this->assertEquals(-19.5, $updated->rx_power_dbm);
    }

    public function test_olt_sync_restores_soft_deleted_onu(): void
    {
        $deletedOnu = Onu::create([
            'name' => 'ONU Soft Deleted',
            'serial_number' => 'ZTEGC85E2D31',
            'mac_address' => 'AA:BB:CC:85:E2:31',
            'branch_id' => $this->branch1->id,
            'status' => 'inactive',
        ]);
        $deletedOnu->delete();
        $this->assertTrue($deletedOnu->trashed());

        $this->actingAs($this->managerBranch1);

        $mockDriver = Mockery::mock(OltDriverInterface::class);
        $mockDriver->shouldReceive('getSystemInfo')->andReturn(['status' => 'online']);
        $mockDriver->shouldReceive('getPonPortsStatus')->andReturn([
            [
                'port_name' => 'EPON0/1',
                'port_number' => 1,
                'port_index' => 1,
                'status' => 'up',
            ],
        ]);
        $mockDriver->shouldReceive('getOnuRxPower')->with(1)->andReturn([
            [
                'onu_index' => 1,
                'serial_number' => 'ZTEGC85E2D31',
                'rx_power_dbm' => -20.0,
                'status' => 'online',
            ],
        ]);

        $mockRegistry = Mockery::mock(OltRegistry::class);
        $mockRegistry->shouldReceive('forOlt')->andReturn($mockDriver);

        $service = new OltPollingService($mockRegistry);
        $result = $service->pollOlt($this->olt);

        $this->assertTrue($result['success']);

        $restoredOnu = Onu::withoutGlobalScopes()->where('serial_number', 'ZTEGC85E2D31')->first();
        $this->assertNotNull($restoredOnu);
        $this->assertFalse($restoredOnu->trashed(), 'Soft deleted ONU should be restored upon coming online');
        $this->assertEquals('active', $restoredOnu->status);
    }

    public function test_olt_sync_matches_hex_and_ascii_vendor_serial(): void
    {
        // Modem didaftarkan dengan hex ZTEG (5A544547)
        Onu::create([
            'name' => 'ONU ZTE Hex',
            'serial_number' => '5A544547C85E2D31',
            'branch_id' => $this->branch1->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($this->managerBranch1);

        $mockDriver = Mockery::mock(OltDriverInterface::class);
        $mockDriver->shouldReceive('getSystemInfo')->andReturn(['status' => 'online']);
        $mockDriver->shouldReceive('getPonPortsStatus')->andReturn([
            [
                'port_name' => 'EPON0/1',
                'port_number' => 1,
                'port_index' => 1,
                'status' => 'up',
            ],
        ]);
        // OLT mengembalikan serial dalam format ASCII ZTEG
        $mockDriver->shouldReceive('getOnuRxPower')->with(1)->andReturn([
            [
                'onu_index' => 1,
                'serial_number' => 'ZTEGC85E2D31',
                'rx_power_dbm' => -21.0,
                'status' => 'online',
            ],
        ]);

        $mockRegistry = Mockery::mock(OltRegistry::class);
        $mockRegistry->shouldReceive('forOlt')->andReturn($mockDriver);

        $service = new OltPollingService($mockRegistry);
        $result = $service->pollOlt($this->olt);

        $this->assertTrue($result['success']);

        // Pastikan tidak ada benturan constraint dan total ONU tetap 1
        $count = Onu::withoutGlobalScopes()->where('serial_number', 'LIKE', '%C85E2D31%')->count();
        $this->assertEquals(1, $count);
    }
}
