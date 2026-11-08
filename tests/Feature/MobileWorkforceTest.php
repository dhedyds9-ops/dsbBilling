<?php

namespace Tests\Feature;

use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileWorkforceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $technician1;
    protected User $technician2;
    protected User $customer;
    protected Branch $branchA;
    protected Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);
        $mgrRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager', 'guard_name' => 'web']);
        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->branchA = Branch::firstOrCreate(['code' => 'BR-A'], ['name' => 'Cabang Utama']);
        $this->branchB = Branch::firstOrCreate(['code' => 'BR-B'], ['name' => 'Cabang Barat']);

        // Admin
        $this->admin = User::factory()->create([
            'email' => 'admin_test@dsbilling.test',
            'job_function' => 'ADMINISTRATOR',
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id);

        // Manager Branch A
        $this->manager = User::factory()->create([
            'email' => 'manager_test@dsbilling.test',
            'job_function' => 'MANAGER',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);
        $this->manager->roles()->attach($mgrRole->id);

        // Technician 1 (Branch A)
        $this->technician1 = User::factory()->create([
            'name' => 'Budi Teknisi',
            'email' => 'tech1@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);
        $this->technician1->roles()->attach($techRole->id);

        // Technician 2 (Branch A)
        $this->technician2 = User::factory()->create([
            'name' => 'Agus Teknisi',
            'email' => 'tech2@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);
        $this->technician2->roles()->attach($techRole->id);

        // Customer
        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Rumah Pak Joko',
            'email' => 'customer_joko@dsbilling.test',
            'whatsapp' => '081234567890',
            'address' => 'Jl. Merdeka No. 45, Jakarta',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);
    }

    public function test_technician_can_list_assigned_work_orders_with_filters(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo1 = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'Pasang Baru Fiber Optic Pak Joko',
            'description' => 'Pasang drop core 150m ke ODP-01',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $wo2 = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'Perbaikan Redaman Drop',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'completed',
            'priority' => 'low',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mobile/work-orders?status=in_progress');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Pasang Baru Fiber Optic Pak Joko']);
    }

    public function test_technician_can_view_work_order_detail_with_checklists(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'Instalasi ONT Huawei HG8245H5',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'assigned',
            'priority' => 'urgent',
        ]);

        WorkOrderChecklist::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $wo->id,
            'item' => 'Tarik kabel drop core dari ODP',
            'completed' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/mobile/work-orders/{$wo->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $wo->id,
                    'title' => 'Instalasi ONT Huawei HG8245H5',
                    'customer' => [
                        'name' => 'Pelanggan Rumah Pak Joko',
                        'phone' => '081234567890',
                    ],
                ],
            ]);
    }

    public function test_manager_can_assign_work_order_to_technician(): void
    {
        $token = $this->manager->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'SPK Baru Belum Ditugaskan',
            'customer_id' => $this->customer->id,
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/work-orders/{$wo->id}/assign", [
                'technician_id' => $this->technician1->id,
                'notes' => 'Tolong prioritaskan pagi ini.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'assigned',
        ]);
    }

    public function test_technician_cannot_assign_work_orders(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'SPK Percobaan',
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/work-orders/{$wo->id}/assign", [
                'technician_id' => $this->technician2->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_technician_status_transition_flow(): void
    {
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'SPK Alur Pekerjaan',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'assigned',
        ]);

        // 1. Technician starts work
        $startRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/work-orders/{$wo->id}/status", [
                'status' => 'in_progress',
                'notes' => 'Teknisi tiba di lokasi pelanggan.',
            ]);
        $startRes->assertStatus(200);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'in_progress',
        ]);

        // 2. Technician finishes field work and requests approval
        $pendingApprovalRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/work-orders/{$wo->id}/status", [
                'status' => 'pending_approval',
                'notes' => 'Pemasangan selesai redaman -18 dBm. Menunggu approval.',
            ]);
        $pendingApprovalRes->assertStatus(200);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'pending_approval',
        ]);
    }

    public function test_manager_can_approve_and_complete_work_order(): void
    {
        $token = $this->manager->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchA->id,
            'title' => 'SPK Menunggu Approval',
            'customer_id' => $this->customer->id,
            'assigned_to' => $this->technician1->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/work-orders/{$wo->id}/status", [
                'status' => 'completed',
                'notes' => 'Disetujui, bukti lengkap.',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'completed',
            'approved_by' => $this->manager->id,
        ]);
    }

    public function test_branch_isolation_on_work_orders(): void
    {
        // Technician 1 is in branch A
        $token = $this->technician1->createToken('test-mobile-token')->plainTextToken;

        // Work order is in branch B
        $woBranchB = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchB->id,
            'title' => 'SPK Cabang Lain',
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/mobile/work-orders/{$woBranchB->id}");

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Akses ditolak: SPK ini milik cabang lain.']);
    }

    public function test_manager_can_list_technicians(): void
    {
        $token = $this->manager->createToken('test-mobile-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mobile/workforce/technicians');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['name' => 'Budi Teknisi'])
            ->assertJsonFragment(['name' => 'Agus Teknisi']);
    }
}
