<?php

namespace Tests\Feature;

use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileWorkEvidenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician1;
    protected User $technician2;
    protected User $customer;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);
        $this->branch = Branch::firstOrCreate(['code' => 'BR-EVID'], ['name' => 'Cabang Evidence']);

        $this->technician1 = User::factory()->create([
            'name' => 'Hadi Teknisi 1',
            'email' => 'hadi@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician1->roles()->attach($techRole->id);

        $this->technician2 = User::factory()->create([
            'name' => 'Dedi Teknisi 2',
            'email' => 'dedi@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician2->roles()->attach($techRole->id);

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Rumah Ibu Rina',
            'email' => 'rina@dsbilling.test',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_technician_can_add_and_toggle_checklists(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'title' => 'Pasang Baru Drop Core 2 Core',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'in_progress',
        ]);

        // 1. Add checklist
        $addRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/checklists", [
                'item' => 'Splicing core di ODP port 4',
            ]);

        $addRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.item', 'Splicing core di ODP port 4');

        $checklistId = $addRes->json('data.id');

        // 2. Toggle checklist to true
        $toggleRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/checklists/{$checklistId}/toggle", [
                'completed' => true,
            ]);

        $toggleRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.completed', true);

        $this->assertDatabaseHas('work_order_checklists', [
            'id' => $checklistId,
            'completed' => 1,
        ]);
    }

    public function test_technician_can_record_optical_measurements(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'title' => 'Uji Redaman Selesai Pasang',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'in_progress',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/measurements", [
                'optical_rx_power' => -18.75,
                'optical_tx_power' => 2.40,
                'notes' => 'Redaman bersih menggunakan OPM Joinwit',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'optical_rx_power' => -18.75,
                    'optical_tx_power' => 2.40,
                    'quality' => 'Optimal (Bagus)',
                ],
            ]);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'optical_rx_power' => -18.75,
        ]);
    }

    public function test_technician_can_upload_evidence_photo(): void
    {
        Storage::fake('public');
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'title' => 'Dokumentasi Foto Pemasangan',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->image('modem_terpasang.jpg', 800, 600);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/evidence", [
                'photo' => $file,
                'type' => 'after',
                'caption' => 'Modem ZTE terpasang rapi di ruang tamu',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.count', 1);

        $fresh = $wo->fresh();
        $this->assertIsArray($fresh->evidence_photos);
        $this->assertCount(1, $fresh->evidence_photos);
        $this->assertEquals('after', $fresh->evidence_photos[0]['type']);
    }

    public function test_technician_can_save_customer_signature(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'title' => 'Tanda Tangan Berita Acara',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'in_progress',
        ]);

        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/sign", [
                'signature' => $fakeBase64,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'customer_signature' => $fakeBase64,
        ]);
    }

    public function test_technician_cannot_modify_evidence_on_another_technicians_spk(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        // Assigned to technician2
        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'title' => 'SPK Milik Teknisi 2',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician2->id,
            'status' => 'in_progress',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/measurements", [
                'optical_rx_power' => -20.0,
            ]);

        $response->assertStatus(403);
    }
}
