<?php

namespace Tests\Feature;

use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\ISP\Odp;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileEndToEndFieldWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected User $manager;
    protected User $customer;
    protected Branch $branch;
    protected Warehouse $warehouse;
    protected Item $itemOnt;
    protected Item $itemCable;
    protected Odp $odp;

    protected function setUp(): void
    {
        parent::setUp();

        $mgrRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager', 'guard_name' => 'web']);
        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->branch = Branch::firstOrCreate(['code' => 'BR-E2E'], ['name' => 'Cabang E2E Field']);

        $this->warehouse = Warehouse::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'WH-E2E',
            'name' => 'Gudang Utama E2E',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->technician = User::factory()->create([
            'name' => 'Budi Teknisi E2E',
            'email' => 'budi_e2e@dsbilling.test',
            'password' => bcrypt('Password123!'),
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

        $this->manager = User::factory()->create([
            'name' => 'Agus Manager E2E',
            'email' => 'agus_mgr@dsbilling.test',
            'password' => bcrypt('Password123!'),
            'job_function' => 'MANAGER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->manager->roles()->attach($mgrRole->id);

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Pak Joko',
            'email' => 'joko@gmail.test',
            'job_function' => 'CUSTOMER',
            'branch_id' => $this->branch->id,
            'whatsapp' => '081234567890',
            'address' => 'Jl. Merdeka No. 45, Kebumen',
        ]);

        $this->itemOnt = Item::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'ONT-HW-8245',
            'name' => 'ONT Huawei EchoLife HG8245H5',
            'unit' => 'unit',
            'type' => 'serialized',
            'is_active' => true,
        ]);

        $this->itemCable = Item::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'CBL-DC-1C',
            'name' => 'Drop Core Fiber Optic 1 Core',
            'unit' => 'meter',
            'type' => 'bulk',
            'is_active' => true,
        ]);

        $this->odp = Odp::create([
            'uuid' => (string) Str::uuid(),
            'code' => 'ODP-KBM-01',
            'name' => 'ODP Alun-Alun Kebumen 01',
            'port_count' => 8,
            'used_port_count' => 2,
            'latitude' => -7.6745,
            'longitude' => 109.6582,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_complete_field_operations_e2e_lifecycle(): void
    {
        Storage::fake('public');

        // ==========================================
        // 1. Step 1: Technician Login & Auth Token
        // ==========================================
        $loginRes = $this->postJson('/api/mobile/auth/login', [
            'email' => 'budi_e2e@dsbilling.test',
            'password' => 'Password123!',
        ]);

        $loginRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token', 'user' => ['id', 'role', 'permissions']]);

        $token = $loginRes->json('token');
        $this->assertNotEmpty($token);
        $authHeaders = ['Authorization' => "Bearer $token"];

        // ==========================================
        // 2. Step 2: Work Order Assignment & Fetching
        // ==========================================
        $workOrder = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Pemasangan Paket Internet 50 Mbps Rumah',
            'description' => 'Pasang drop core 65 meter dan setting ONT Wi-Fi',
            'status' => 'assigned',
            'priority' => 'high',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        $checklist = WorkOrderChecklist::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $workOrder->id,
            'item' => 'Tarik kabel drop core dari ODP ke rumah pelanggan',
            'completed' => false,
            'order' => 1,
        ]);

        $fetchWoRes = $this->withHeaders($authHeaders)->getJson('/api/mobile/workforce/work-orders');
        $fetchWoRes->assertOk()
            ->assertJsonPath('success', true);
        $this->assertCount(1, $fetchWoRes->json('data'));

        // ==========================================
        // 3. Step 3: Start Work Order (in_progress)
        // ==========================================
        $startRes = $this->withHeaders($authHeaders)->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/start");
        $startRes->assertOk();
        $this->assertEquals('in_progress', $workOrder->fresh()->status);

        // ==========================================
        // 4. Step 4: Material Requisition & Approval
        // ==========================================
        $reqRes = $this->withHeaders($authHeaders)->postJson('/api/mobile/inventory/requisitions', [
            'notes' => 'Kebutuhan material instalasi SPK 001',
            'items' => [
                ['item_id' => $this->itemCable->id, 'quantity' => 75],
                ['item_id' => $this->itemOnt->id, 'quantity' => 1, 'serial_number' => 'HWTC8245ABCD'],
            ],
        ]);
        $reqRes->assertOk();
        $requisitionId = $reqRes->json('data.id');

        // Manager approves the material requisition
        Sanctum::actingAs($this->manager, ['*']);
        $approveRes = $this->postJson("/api/mobile/inventory/requisitions/{$requisitionId}/approve");
        $approveRes->assertOk();

        // Switch back to technician
        Sanctum::actingAs($this->technician, ['*']);

        // Technician checks custody stock
        $custodyRes = $this->getJson('/api/mobile/inventory/my-stock');
        $custodyRes->assertOk();
        $this->assertGreaterThanOrEqual(1, count($custodyRes->json('data')));

        // ==========================================
        // 5. Step 5: Record Material Usage on SPK
        // ==========================================
        $matUsageRes = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/materials", [
            'material_name' => 'Drop Core Fiber Optic 1 Core',
            'material_id' => $this->itemCable->id,
            'quantity' => 65,
            'notes' => 'Terkuras 65 meter dari tiang ke roset',
        ]);
        $matUsageRes->assertOk();

        // ==========================================
        // 6. Step 6: Checklist & OPM Measurements
        // ==========================================
        $toggleRes = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/checklists/{$checklist->id}/toggle", [
            'completed' => true,
        ]);
        $toggleRes->assertOk();
        $this->assertTrue((bool) $checklist->fresh()->completed);

        // Save optical attenuation measurements (-19.2 dBm is optimal)
        $measureRes = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/measurements", [
            'optical_rx_power' => -19.2,
            'optical_tx_power' => 2.4,
            'notes' => 'Redaman roset sangat baik di bawah -20 dBm',
        ]);
        $measureRes->assertOk()
            ->assertJsonPath('data.optical_rx_power', -19.2);

        // ==========================================
        // 7. Step 7: Provision ONT via Proxy
        // ==========================================
        $provisionRes = $this->postJson('/api/mobile/network/devices/provision-ont', [
            'work_order_id' => $workOrder->id,
            'serial_number' => 'HWTC8245ABCD',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'odp_id' => $this->odp->id,
            'odp_port' => 3,
            'wifi_ssid' => 'PakJoko-HomeNet',
            'wifi_password' => 'rahasia123',
        ]);
        $provisionRes->assertOk()
            ->assertJsonPath('success', true);

        // ==========================================
        // 8. Step 8: Evidence Photo & Digital Signature
        // ==========================================
        $photo = UploadedFile::fake()->image('modem_terpasang.jpg', 800, 600);
        $evidenceRes = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/evidence", [
            'photo' => $photo,
            'type' => 'after',
            'caption' => 'ONT menyala normal PON hijau',
        ]);
        $evidenceRes->assertOk();

        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $signRes = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/sign", [
            'signature' => $dummySignature,
        ]);
        $signRes->assertOk();

        // ==========================================
        // 9. Step 9: Complete Work Order & Manager Approval
        // ==========================================
        $completeRes = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/complete", [
            'notes' => 'Pemasangan selesai dan internet aktif di perangkat pelanggan.',
        ]);
        $completeRes->assertOk();
        $this->assertEquals('pending_approval', $workOrder->fresh()->status);

        // Manager reviews evidence and approves the work order to completed
        Sanctum::actingAs($this->manager, ['*']);
        $approveWoRes = $this->postJson("/api/mobile/workforce/work-orders/{$workOrder->id}/status", [
            'status' => 'completed',
            'notes' => 'Pekerjaan diverifikasi lengkap dan disetujui Supervisor.',
        ]);
        $approveWoRes->assertOk();
        $this->assertEquals('completed', $workOrder->fresh()->status);

        // Switch back to technician
        Sanctum::actingAs($this->technician, ['*']);

        // ==========================================
        // 10. Step 10: Reports & Activity Logs Verification
        // ==========================================
        $kpiRes = $this->getJson('/api/mobile/reports/technician-performance');
        $kpiRes->assertOk()
            ->assertJsonPath('data.total_completed', 1);

        $matReportRes = $this->getJson('/api/mobile/reports/material-consumption');
        $matReportRes->assertOk()
            ->assertJsonPath('data.total_materials_used', 65);

        $activityRes = $this->getJson('/api/mobile/reports/activity-logs?limit=10');
        $activityRes->assertOk();
        $this->assertNotEmpty($activityRes->json('data'));

        // ==========================================
        // 11. Step 11: In-App Version / OTA Update Check
        // ==========================================
        $updateRes = $this->getJson('/api/mobile/app/check-update?version_code=1');
        $updateRes->assertOk()
            ->assertJsonPath('data.update_available', true);
    }
}
