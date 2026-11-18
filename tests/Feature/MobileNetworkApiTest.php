<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileNetworkApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $branch = \App\Models\Master\Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'CBG-01',
            'is_active' => true,
        ]);
        $this->user = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $this->user->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now(),
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        Sanctum::actingAs($this->user, ['*']);
    }

    public function test_update_device_location_persists_to_database(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-LOC-01',
            'name' => 'Pelanggan Uji Lokasi',
            'phone' => '081234567890',
            'status' => 'active',
            'branch_id' => 1,
        ]);

        $olt = Olt::create([
            'code' => 'OLT-TEST-LOC',
            'name' => 'OLT Test',
            'ip_address' => '10.0.0.1',
            'status' => 'active',
            'port_count' => 8,
            'branch_id' => 1,
        ]);

        $odp = Odp::create([
            'code' => 'ODP-TEST-LOC',
            'name' => 'ODP Test Lokasi',
            'latitude' => '-6.200000',
            'longitude' => '106.800000',
            'branch_id' => 1,
        ]);

        $onu = Onu::create([
            'code' => 'ONU-TEST-01',
            'name' => 'ONU Pelanggan',
            'serial_number' => 'FHTT12345678',
            'olt_id' => $olt->id,
            'status' => 'online',
            'branch_id' => 1,
        ]);

        CustomerService::create([
            'customer_id' => $customer->id,
            'onu_id' => $onu->id,
            'status' => 'active',
            'username' => 'uji_lokasi',
        ]);

        $response = $this->postJson('/api/mobile/network/devices/location', [
            'device_id' => "cust-{$customer->id}",
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'odp_id' => $odp->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'latitude' => '-6.917464',
            'longitude' => '107.619123',
        ]);

        $this->assertDatabaseHas('onus', [
            'id' => $onu->id,
            'latitude' => '-6.917464',
            'longitude' => '107.619123',
            'odp_id' => $odp->id,
        ]);
    }

    public function test_show_and_update_node_odp(): void
    {
        $odp = Odp::create([
            'code' => 'ODP-SHOW-01',
            'name' => 'ODP Lama',
            'latitude' => '-6.100000',
            'longitude' => '106.700000',
            'port_count' => 8,
            'branch_id' => 1,
        ]);

        $showRes = $this->getJson("/api/mobile/network/nodes/odp/{$odp->id}");
        $showRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'ODP-SHOW-01');

        $updateRes = $this->postJson("/api/mobile/network/nodes/odp/{$odp->id}/update", [
            'name' => 'ODP Diperbarui',
            'code' => 'ODP-EDIT-01',
            'latitude' => -6.123456,
            'longitude' => 106.654321,
            'address' => 'Jl. Merdeka No. 45',
            'port_count' => 16,
        ]);

        $updateRes->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('odps', [
            'id' => $odp->id,
            'name' => 'ODP Diperbarui',
            'code' => 'ODP-EDIT-01',
            'latitude' => '-6.123456',
            'longitude' => '106.654321',
            'address' => 'Jl. Merdeka No. 45',
            'port_count' => 16,
        ]);
    }

    public function test_update_node_olt(): void
    {
        $olt = Olt::create([
            'code' => 'OLT-NODE-01',
            'name' => 'OLT Lama',
            'ip_address' => '192.168.1.1',
            'status' => 'active',
            'port_count' => 8,
            'branch_id' => 1,
        ]);

        $updateRes = $this->postJson("/api/mobile/network/nodes/olt/{$olt->id}/update", [
            'name' => 'OLT Core Baru',
            'code' => 'OLT-CORE-01',
            'ip_address' => '192.168.1.254',
            'model' => 'FD1608SN',
            'latitude' => -6.900000,
            'longitude' => 107.600000,
            'address' => 'Ruang NOC Lt 2',
        ]);

        $updateRes->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('olts', [
            'id' => $olt->id,
            'name' => 'OLT Core Baru',
            'code' => 'OLT-CORE-01',
            'ip_address' => '192.168.1.254',
            'model' => 'FD1608SN',
            'latitude' => '-6.9',
            'longitude' => '107.6',
            'address' => 'Ruang NOC Lt 2',
        ]);
    }
}
