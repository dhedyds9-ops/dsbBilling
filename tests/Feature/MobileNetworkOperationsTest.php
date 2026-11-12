<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\ISP\Odp;
use App\Models\ISP\Onu;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileNetworkOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);
        $this->branch = Branch::firstOrCreate(['code' => 'BR-NET'], ['name' => 'Cabang Network Ops']);

        $this->technician = User::factory()->create([
            'name' => 'Rahmat Teknisi',
            'email' => 'rahmat@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->technician->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);
    }

    public function test_technician_can_search_and_list_devices(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        Onu::create([
            'serial_number' => 'ZTEGC11223344',
            'mac_address' => 'AA:BB:CC:11:22:33',
            'model' => 'ZTE F663',
            'name' => 'ONT Rahmat 1',
            'status' => 'active',
            'branch_id' => $this->branch->id,
            'rx_power_dbm' => -19.45,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/network/devices?q=ZTEGC1122');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'serial_number' => 'ZTEGC11223344',
                'model' => 'ZTE F663',
            ]);
    }

    public function test_technician_can_get_device_by_serial_number(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        $onu = Onu::create([
            'serial_number' => 'HWTC99887766',
            'mac_address' => '11:22:33:44:55:66',
            'model' => 'Huawei HG8245H',
            'name' => 'ONT Pelanggan Toko',
            'status' => 'active',
            'branch_id' => $this->branch->id,
            'rx_power_dbm' => -20.10,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/network/devices/HWTC99887766');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'serial_number' => 'HWTC99887766',
                    'model' => 'Huawei HG8245H',
                ],
            ]);
    }

    public function test_technician_can_search_and_list_odps(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        Odp::create([
            'code' => 'ODP-KST-01',
            'name' => 'ODP Kusuma Timur 01',
            'address' => 'Jl. Kusuma Raya No 10',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'port_count' => 16,
            'used_port_count' => 5,
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/network/odps?q=ODP-KST&latitude=-6.2090&longitude=106.8450');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'code' => 'ODP-KST-01',
                'port_count' => 16,
                'used_port_count' => 5,
                'available_ports' => 11,
            ]);
    }

    public function test_technician_can_provision_ont_and_bind_to_work_order(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        $odp = Odp::create([
            'code' => 'ODP-MLG-05',
            'name' => 'ODP Malang Indah 05',
            'port_count' => 8,
            'used_port_count' => 2,
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'title' => 'Pasang Baru Bpk Harun',
            'type' => 'installation',
            'status' => 'assigned',
            'priority' => 'high',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        $payload = [
            'serial_number' => 'FHTT12345678',
            'mac_address' => '00:11:22:33:44:55',
            'model' => 'FiberHome HG6243C',
            'work_order_id' => $wo->id,
            'odp_id' => $odp->id,
            'odp_port' => 3,
            'wifi_ssid' => 'Harun_Home_Net',
            'wifi_password' => 'rahasia123',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/network/devices/provision-ont', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.onu.serial_number', 'FHTT12345678')
            ->assertJsonPath('data.onu.provision_status', 'provisioned');

        // Verify ONU was saved in database
        $this->assertDatabaseHas('onus', [
            'serial_number' => 'FHTT12345678',
            'odp_id' => $odp->id,
            'status' => 'active',
            'provision_status' => 'provisioned',
        ]);

        // Verify WorkOrder was updated with status in_progress and note
        $wo->refresh();
        $this->assertEquals('in_progress', $wo->status);
        $this->assertStringContainsString('FHTT12345678', $wo->notes);
    }

    public function test_technician_can_query_device_diagnostics(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        Onu::create([
            'serial_number' => 'ZTEGC99988877',
            'name' => 'ONT Rahmat 2',
            'model' => 'ZTE F670L',
            'status' => 'active',
            'rx_power_dbm' => -18.25,
            'tx_power_dbm' => 2.40,
            'branch_id' => $this->branch->id,
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/network/devices/ZTEGC99988877/diagnostics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'serial_number' => 'ZTEGC99988877',
                    'optical_quality' => 'Optimal',
                    'rx_power_dbm' => -18.25,
                ],
            ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/mobile/network/devices');
        $response->assertStatus(401);
    }
}
