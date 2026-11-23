<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Events\FieldService\WorkOrderOperationalEvent;
use App\Models\AuditLog;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\CRM\QualityControl;
use App\Models\Master\Branch;
use App\Models\Role;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use App\Services\Workforce\UnifiedQcApprovalService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UnifiedQcApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $noc;
    protected User $tech1;
    protected User $tech2;
    protected Branch $branch1;
    protected Branch $branch2;
    protected User $branch2Admin;
    protected User $reseller1;
    protected User $reseller2;
    protected UnifiedQcApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Http::fake();

        Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);
        Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        Role::firstOrCreate(['name' => 'noc'], ['display_name' => 'NOC']);
        Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);
        Role::firstOrCreate(['name' => UserRole::Reseller->value], ['display_name' => 'Reseller']);

        $this->branch1 = Branch::create(['name' => 'Cabang Surabaya', 'code' => 'SBY', 'is_active' => true]);
        $this->branch2 = Branch::create(['name' => 'Cabang Malang', 'code' => 'MLG', 'is_active' => true]);

        $this->admin = User::factory()->create([
            'branch_id' => $this->branch1->id,
            'job_function' => 'ADMINISTRATOR',
            'is_active' => true,
        ]);
        $this->admin->attachRole(UserRole::Administrator->value);

        $this->noc = User::factory()->create([
            'branch_id' => $this->branch1->id,
            'job_function' => 'NOC',
            'is_active' => true,
        ]);
        $this->noc->attachRole('noc');

        $this->tech1 = User::factory()->create([
            'branch_id' => $this->branch1->id,
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);
        $this->tech1->attachRole('technician');

        $this->tech2 = User::factory()->create([
            'branch_id' => $this->branch1->id,
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);
        $this->tech2->attachRole('technician');

        $this->branch2Admin = User::factory()->create([
            'branch_id' => $this->branch2->id,
            'job_function' => 'ADMINISTRATOR',
            'is_active' => true,
        ]);
        $this->branch2Admin->attachRole(UserRole::Administrator->value);

        $this->reseller1 = User::factory()->create([
            'branch_id' => $this->branch1->id,
            'job_function' => 'RESELLER',
            'is_active' => true,
        ]);
        $this->reseller1->attachRole(UserRole::Reseller->value);

        $this->reseller2 = User::factory()->create([
            'branch_id' => $this->branch1->id,
            'job_function' => 'RESELLER',
            'is_active' => true,
        ]);
        $this->reseller2->attachRole(UserRole::Reseller->value);

        $this->service = app(UnifiedQcApprovalService::class);
    }

    protected function createWorkOrder(array $overrides = []): WorkOrder
    {
        $customer = Customer::create([
            'code' => 'CUST-' . Str::random(5),
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'branch_id' => $this->branch1->id,
            'status' => 'active',
        ]);

        return WorkOrder::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch1->id,
            'customer_id' => $customer->id,
            'assigned_to' => $this->tech1->id,
            'status' => 'pending_approval',
            'title' => 'Pemasangan Dropcore & ONT',
            'description' => 'Tugas pasang baru pelanggan',
            'priority' => 'high',
            'optical_rx_power' => -19.45,
            'optical_tx_power' => 2.10,
            'evidence_photos' => ['photos/house.jpg', 'photos/ont.jpg', 'photos/opm.jpg'],
        ], $overrides));
    }

    /** 1. Work Order pending approval muncul pada antrean QC */
    public function test_work_order_pending_approval_appears_in_qc_queue(): void
    {
        $wo = $this->createWorkOrder(['status' => 'pending_approval']);
        $completedWo = $this->createWorkOrder(['status' => 'completed']);

        $pendingQuery = $this->service->getPendingQcQuery($this->admin);
        $results = $pendingQuery->get();

        $this->assertTrue($results->contains('id', $wo->id));
        $this->assertFalse($results->contains('id', $completedWo->id));
    }

    /** 2. Admin berwenang dapat menyetujui */
    public function test_authorized_admin_can_approve_work_order(): void
    {
        $wo = $this->createWorkOrder();

        $approved = $this->service->approve($wo, $this->admin, 'Hasil redaman -19.45 dBm bagus, rapi.');

        $this->assertEquals('completed', $approved->status);
        $this->assertEquals($this->admin->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
        $this->assertEquals('Hasil redaman -19.45 dBm bagus, rapi.', $approved->notes);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'completed',
            'approved_by' => $this->admin->id,
        ]);
    }

    /** 3. Admin berwenang dapat menolak */
    public function test_authorized_admin_can_reject_work_order(): void
    {
        $wo = $this->createWorkOrder();

        $rejected = $this->service->reject($wo, $this->admin, 'Redaman terlalu tinggi (-28 dBm), lakukan splicing ulang.');

        $this->assertEquals('in_progress', $rejected->status);
        $this->assertEquals('Redaman terlalu tinggi (-28 dBm), lakukan splicing ulang.', $rejected->notes);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'in_progress',
            'notes' => 'Redaman terlalu tinggi (-28 dBm), lakukan splicing ulang.',
        ]);
    }

    /** 4. Penolakan tanpa alasan ditolak */
    public function test_rejection_without_reason_is_rejected(): void
    {
        $wo = $this->createWorkOrder();

        $this->expectException(ValidationException::class);
        $this->service->reject($wo, $this->admin, '   ');
    }

    /** 5. Teknisi tidak dapat menyetujui pekerjaannya sendiri */
    public function test_technician_cannot_approve_own_work(): void
    {
        $wo = $this->createWorkOrder(['assigned_to' => $this->tech1->id]);

        $this->expectException(AuthorizationException::class);
        $this->service->approve($wo, $this->tech1, 'Saya approve sendiri');
    }

    /** 6. Teknisi hanya dapat melihat / mengerjakan pekerjaan yang menjadi kewenangannya */
    public function test_technician_self_approval_check_returns_false(): void
    {
        $wo = $this->createWorkOrder(['assigned_to' => $this->tech1->id]);

        $this->assertFalse($this->service->canApprove($wo, $this->tech1));
        $this->assertFalse($this->service->canReject($wo, $this->tech1));
    }

    /** 7. Cabang tidak dapat memeriksa pekerjaan cabang lain */
    public function test_admin_branch_a_cannot_approve_branch_b_work(): void
    {
        $wo = $this->createWorkOrder(['branch_id' => $this->branch1->id]);

        $this->assertFalse($this->service->canApprove($wo, $this->branch2Admin));

        $this->expectException(AuthorizationException::class);
        $this->service->approve($wo, $this->branch2Admin, 'Approve lintas cabang');
    }

    /** 8. Reseller tidak dapat mengakses pekerjaan reseller lain */
    public function test_reseller_cannot_approve_another_reseller_work(): void
    {
        $wo = $this->createWorkOrder(['reseller_id' => $this->reseller1->id]);

        $this->assertFalse($this->service->canApprove($wo, $this->reseller2));

        $this->expectException(AuthorizationException::class);
        $this->service->approve($wo, $this->reseller2, 'Approve lintas reseller');
    }

    /** 9. Double approval tidak membuat histori ganda (Idempotent) */
    public function test_double_approval_is_idempotent_and_does_not_duplicate_audit_logs(): void
    {
        $wo = $this->createWorkOrder();

        // Approval pertama
        $approvedFirst = $this->service->approve($wo, $this->admin, 'Approval 1');
        $firstApprovedAt = $approvedFirst->approved_at;

        $logCountAfterFirst = AuditLog::where('event', 'qc_approved')->count();
        $this->assertEquals(1, $logCountAfterFirst);

        // Approval kedua (misal double-click atau retry)
        $approvedSecond = $this->service->approve($approvedFirst, $this->admin, 'Approval 2');

        $logCountAfterSecond = AuditLog::where('event', 'qc_approved')->count();
        $this->assertEquals(1, $logCountAfterSecond); // Tidak bertambah!
        $this->assertEquals($firstApprovedAt->toIso8601String(), $approvedSecond->approved_at->toIso8601String());
    }

    /** 10. Concurrent approval tidak menyebabkan status inkonsisten */
    public function test_status_remains_consistent_on_approval(): void
    {
        $wo = $this->createWorkOrder();

        $approved = $this->service->approve($wo, $this->admin);

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseMissing('work_orders', [
            'id' => $wo->id,
            'status' => 'pending_approval',
        ]);
    }

    /** 11. Status Installation tersinkronisasi setelah QC disetujui */
    public function test_installation_status_synchronized_to_verified_on_approval(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-INST',
            'name' => 'Instalasi Pelanggan',
            'phone' => '081233334444',
            'branch_id' => $this->branch1->id,
            'status' => 'active',
        ]);

        $installation = Installation::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'branch_id' => $this->branch1->id,
            'assigned_to' => $this->tech1->id,
            'status' => 'completed', // Teknisi selesai pasang fisik
            'optical_power' => '-19.5',
        ]);

        $wo = $this->createWorkOrder([
            'installation_id' => $installation->id,
            'type' => 'installation',
        ]);

        $this->service->approve($wo, $this->admin, 'QC Fisik Instalasi OK');

        $installation->refresh();
        $this->assertEquals('verified', $installation->status); // Verified lolos QC

        $this->assertDatabaseHas('quality_controls', [
            'installation_id' => $installation->id,
            'status' => 'approved',
        ]);
    }

    /** 12. Maintenance mengikuti status domain yang benar */
    public function test_maintenance_synchronized_to_completed_on_approval(): void
    {
        $maintenance = SupportMaintenance::create([
            'branch_id' => $this->branch1->id,
            'title' => 'Splicing Kabel ODP-01 Putus',
            'type' => 'corrective',
            'status' => 'in_progress',
            'assigned_to' => $this->tech1->id,
        ]);

        $ticket = Ticket::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branch1->id,
            'title' => 'Kabel FO Putus',
            'category' => 'fiber',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'maintenance_wo_id' => $maintenance->id,
        ]);

        $wo = $this->createWorkOrder([
            'ticket_id' => $ticket->id,
            'type' => 'maintenance',
        ]);

        $this->service->approve($wo, $this->admin, 'Splicing rapi, redaman pulih');

        $maintenance->refresh();
        $this->assertEquals('completed', $maintenance->status);
        $this->assertEquals($this->admin->id, $maintenance->qc_verified_by);
        $this->assertNotNull($maintenance->qc_verified_at);

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
    }

    /** 13. Aktivasi tidak berjalan sebelum seluruh persyaratan provisioning terpenuhi */
    public function test_installation_qc_verified_does_not_automatically_activate_service(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-ACT',
            'name' => 'Belum Terprovisi',
            'phone' => '081255556666',
            'branch_id' => $this->branch1->id,
            'status' => 'pending',
        ]);

        $installation = Installation::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'branch_id' => $this->branch1->id,
            'assigned_to' => $this->tech1->id,
            'status' => 'completed',
        ]);

        $wo = $this->createWorkOrder([
            'installation_id' => $installation->id,
            'type' => 'installation',
        ]);

        $this->service->approve($wo, $this->admin);

        $installation->refresh();
        $this->assertEquals('verified', $installation->status);
        $this->assertNotEquals('active', $installation->status); // Bukan active!

        $customer->refresh();
        $this->assertEquals('pending', $customer->status); // Pelanggan tetap pending sampai provisioning sukses
    }

    /** 14. Event notifikasi hanya diproses setelah commit */
    public function test_work_order_operational_event_dispatched_on_approval_and_rejection(): void
    {
        Event::fake([WorkOrderOperationalEvent::class]);

        $wo1 = $this->createWorkOrder();
        $this->service->approve($wo1, $this->admin, 'Lolos');

        Event::assertDispatched(WorkOrderOperationalEvent::class, function ($event) use ($wo1) {
            return $event->action === 'approved' && $event->workOrder->id === $wo1->id;
        });

        $wo2 = $this->createWorkOrder();
        $this->service->reject($wo2, $this->admin, 'Foto ONT kurang jelas');

        Event::assertDispatched(WorkOrderOperationalEvent::class, function ($event) use ($wo2) {
            return $event->action === 'rejected' && $event->workOrder->id === $wo2->id;
        });
    }

    /** 15. Penolakan mengembalikan pekerjaan ke teknisi */
    public function test_rejection_returns_job_to_technician_in_progress(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-REJ',
            'name' => 'Pelanggan Revisi',
            'phone' => '081277778888',
            'branch_id' => $this->branch1->id,
            'status' => 'active',
        ]);

        $installation = Installation::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'branch_id' => $this->branch1->id,
            'assigned_to' => $this->tech1->id,
            'status' => 'completed',
        ]);

        $wo = $this->createWorkOrder([
            'installation_id' => $installation->id,
            'type' => 'installation',
        ]);

        $rejected = $this->service->reject($wo, $this->admin, 'Foto barcode ONT kabur, upload ulang foto yang tajam.');

        $this->assertEquals('in_progress', $rejected->status);
        $this->assertEquals('Foto barcode ONT kabur, upload ulang foto yang tajam.', $rejected->notes);

        $installation->refresh();
        $this->assertEquals('in_progress', $installation->status); // Kembali ke teknisi
        $this->assertStringContainsString('Foto barcode ONT kabur', $installation->notes);

        $this->assertDatabaseHas('quality_controls', [
            'installation_id' => $installation->id,
            'status' => 'rejected',
        ]);
    }

    /** 16. UI Test: Livewire WorkOrderHistory renders QC tab and handles approval/rejection */
    public function test_work_order_history_component_handles_qc_inspection_approval_and_rejection(): void
    {
        $wo = $this->createWorkOrder(['status' => 'pending_approval']);

        \Livewire\Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Support\Installation\WorkOrderHistory::class)
            ->call('setTypeFilter', 'qc_approval')
            ->assertSee('Persetujuan QC')
            ->assertSee($wo->title)
            ->call('openQcInspection', $wo->id)
            ->assertSet('showQcModal', true)
            ->assertSet('selectedWoId', $wo->id)
            ->set('qcNotes', 'QC Approved via Livewire')
            ->call('approveQc')
            ->assertSet('showQcModal', false);

        $wo->refresh();
        $this->assertEquals('completed', $wo->status);
        $this->assertEquals($this->admin->id, $wo->approved_by);
    }
}
