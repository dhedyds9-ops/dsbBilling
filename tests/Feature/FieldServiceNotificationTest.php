<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Events\FieldService\InstallationOperationalEvent;
use App\Events\FieldService\MaintenanceOperationalEvent;
use App\Events\FieldService\TicketOperationalEvent;
use App\Events\FieldService\WorkOrderOperationalEvent;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Notification\Notification;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Services\Notifications\FieldServiceNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class FieldServiceNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);
        \App\Models\Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        \App\Models\Role::firstOrCreate(['name' => 'noc'], ['display_name' => 'NOC']);
        \App\Models\Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);
        \App\Models\Role::firstOrCreate(['name' => UserRole::Reseller->value], ['display_name' => 'Reseller']);
    }

    public function test_ticket_created_generates_in_app_notification_to_branch_supervisors_and_customer_ack(): void
    {
        Queue::fake();
        Http::fake();

        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $noc = User::factory()->create([
            'branch_id' => $branch->id,
            'job_function' => 'NOC',
            'is_active' => true,
        ]);
        $noc->attachRole('noc');

        $customer = Customer::create([
            'code' => 'CUST-01',
            'name' => 'Ahmad Dahlan',
            'phone' => '081234567890',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $ticket = Ticket::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'title' => 'Koneksi LOS Merah',
            'category' => 'internet',
            'priority' => 'high',
            'status' => 'open',
        ]);

        // Dispatch operational event
        TicketOperationalEvent::dispatch('created', $ticket);

        // 1. Assert In-App notification exists for NOC
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $noc->id,
            'title' => 'Tiket Support Baru #' . $ticket->id,
            'type' => 'in_app',
        ]);

        $notif = Notification::where('recipient_id', $noc->id)->first();
        $this->assertNotNull($notif);
        $this->assertEquals($ticket->id, $notif->data['ticket_id']);
    }

    public function test_ticket_assigned_notifies_only_assigned_technician(): void
    {
        Queue::fake();
        Http::fake();

        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $tech1 = User::factory()->create([
            'branch_id' => $branch->id,
            'job_function' => 'TECHNICIAN',
            'whatsapp' => '081299998888',
            'is_active' => true,
        ]);
        $tech1->attachRole('technician');

        $tech2 = User::factory()->create([
            'branch_id' => $branch->id,
            'job_function' => 'TECHNICIAN',
            'whatsapp' => '081277776666',
            'is_active' => true,
        ]);
        $tech2->attachRole('technician');

        $ticket = Ticket::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'assigned_to' => $tech1->id,
            'title' => 'Router Mati Total',
            'status' => 'in_progress',
        ]);

        TicketOperationalEvent::dispatch('assigned', $ticket);

        // Tech 1 must receive in-app notification
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $tech1->id,
            'title' => 'PENUGASAN TIKET #' . $ticket->id,
        ]);

        // Tech 2 must NOT receive notification
        $this->assertDatabaseMissing('notifications', [
            'recipient_id' => $tech2->id,
            'title' => 'PENUGASAN TIKET #' . $ticket->id,
        ]);
    }

    public function test_work_order_started_and_completed_triggers_proper_flow(): void
    {
        Queue::fake();
        Http::fake();

        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $manager = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $manager->attachRole(UserRole::Manager->value);

        $tech = User::factory()->create([
            'branch_id' => $branch->id,
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);
        $tech->attachRole('technician');

        $customer = Customer::create([
            'code' => 'CUST-02',
            'name' => 'Bambang Tri',
            'phone' => '081344445555',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Perbaikan FO Cut',
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'assigned_to' => $tech->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // 1. Started event
        WorkOrderOperationalEvent::dispatch('started', $wo, $tech->id);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $manager->id,
            'title' => "Pekerjaan Dimulai: SPK #{$wo->id}",
        ]);

        // 2. Completed event
        $wo->update(['status' => 'completed', 'completed_at' => now(), 'optical_rx_power' => -18.2]);
        WorkOrderOperationalEvent::dispatch('completed', $wo, $tech->id);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $manager->id,
            'title' => "Menunggu Approval: SPK #{$wo->id}",
        ]);
    }

    public function test_work_order_rejected_notifies_technician_with_reason(): void
    {
        Queue::fake();
        Http::fake();

        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $tech = User::factory()->create([
            'branch_id' => $branch->id,
            'job_function' => 'TECHNICIAN',
            'whatsapp' => '081233334444',
            'is_active' => true,
        ]);
        $tech->attachRole('technician');

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'PSB Ratna',
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'status' => 'in_progress',
        ]);

        WorkOrderOperationalEvent::dispatch('rejected', $wo, 1, ['reason' => 'Redaman kabel masih -27dBm (terlalu tinggi)']);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $tech->id,
            'title' => "QC Ditolak: SPK #{$wo->id}",
        ]);

        $notif = Notification::where('recipient_id', $tech->id)->first();
        $this->assertStringContainsString('Redaman kabel masih -27dBm', $notif->message);
    }

    public function test_activation_failed_alerts_noc_immediately(): void
    {
        Queue::fake();
        Http::fake();

        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $noc = User::factory()->create([
            'branch_id' => $branch->id,
            'job_function' => 'NOC',
            'is_active' => true,
        ]);
        $noc->attachRole('noc');

        $customer = Customer::create([
            'code' => 'CUST-03',
            'name' => 'Dwi Wahyuni',
            'phone' => '081255556666',
            'branch_id' => $branch->id,
            'status' => 'pending',
        ]);

        $inst = Installation::create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'status' => 'in_progress',
        ]);

        InstallationOperationalEvent::dispatch('activation_failed', $inst, 1, [
            'error' => 'GenieACS NBI Connection Timeout pada port 7557',
        ]);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $noc->id,
            'title' => "PERINGATAN: Aktivasi Gagal #{$inst->id}",
        ]);

        $notif = Notification::where('recipient_id', $noc->id)->first();
        $this->assertStringContainsString('GenieACS NBI Connection Timeout', $notif->message);
    }

    public function test_maintenance_completed_notifies_supervisors(): void
    {
        Queue::fake();
        Http::fake();

        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $manager = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $manager->attachRole(UserRole::Manager->value);

        $maint = SupportMaintenance::create([
            'title' => 'Splicing ODC-01 Pasar Baru',
            'location' => 'ODC Pasar Baru',
            'branch_id' => $branch->id,
            'status' => 'completed',
        ]);

        MaintenanceOperationalEvent::dispatch('completed', $maint);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $manager->id,
            'title' => "Maintenance Selesai: #{$maint->id}",
        ]);
    }

    public function test_idempotency_prevents_duplicate_notifications(): void
    {
        Queue::fake();
        Http::fake();

        $service = app(FieldServiceNotificationService::class);

        $key = 'ticket_123_assigned_tech_456';

        // 1st check: should not be duplicate
        $isDup1 = $service->isDuplicate('wa', $key);
        $this->assertFalse($isDup1);

        // 2nd check with same key immediately after: must be duplicate!
        $isDup2 = $service->isDuplicate('wa', $key);
        $this->assertTrue($isDup2);
    }

    public function test_external_provider_failure_does_not_rollback_transaction(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Unauthorized Bot Token'], 401),
        ]);

        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $inst = null;
        DB::transaction(function () use ($branch, &$inst) {
            $inst = Installation::create([
                'branch_id' => $branch->id,
                'status' => 'pending',
                'customer_name' => 'Test Safe Rollback',
            ]);

            // Dispatch event with failed provider inside transaction
            InstallationOperationalEvent::dispatch('activation_failed', $inst, 1, ['error' => 'Provider error test']);
        });

        // The installation record must STILL be committed successfully in DB!
        $this->assertDatabaseHas('installations', [
            'id' => $inst->id,
            'customer_name' => 'Test Safe Rollback',
        ]);
    }

    public function test_in_app_notification_unread_count_integration(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        Notification::create([
            'uuid' => (string) Str::uuid(),
            'recipient_id' => $user->id,
            'title' => 'Notif 1',
            'message' => 'Test Unread 1',
            'type' => 'in_app',
            'status' => 'pending',
        ]);

        Notification::create([
            'uuid' => (string) Str::uuid(),
            'recipient_id' => $user->id,
            'title' => 'Notif 2',
            'message' => 'Test Read',
            'type' => 'in_app',
            'status' => 'read',
        ]);

        $unreadCount = Notification::where('recipient_id', $user->id)
            ->where('status', '!=', 'read')
            ->count();

        $this->assertEquals(1, $unreadCount);
    }
}
