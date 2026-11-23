<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Notification\Notification;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Services\Notifications\FieldServiceNotificationService;
use App\Services\Support\SlaTrackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class SlaMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminBranch1;
    protected User $adminBranch2;
    protected User $tech1;
    protected User $tech2;
    protected Branch $branch1;
    protected Branch $branch2;
    protected Customer $customer1;
    protected Customer $customer2;
    protected SlaTrackingService $slaService;
    protected FieldServiceNotificationService $notificationService;

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

        $this->adminBranch1 = User::factory()->create([
            'branch_id'    => $this->branch1->id,
            'is_active'    => true,
            'job_function' => 'ADMINISTRATOR',
            'whatsapp'     => '08111111111',
        ]);
        $this->adminBranch1->attachRole(UserRole::Administrator->value);

        $this->adminBranch2 = User::factory()->create([
            'branch_id'    => $this->branch2->id,
            'is_active'    => true,
            'job_function' => 'ADMINISTRATOR',
            'whatsapp'     => '08222222222',
        ]);
        $this->adminBranch2->attachRole(UserRole::Administrator->value);

        $this->tech1 = User::factory()->create([
            'branch_id'    => $this->branch1->id,
            'is_active'    => true,
            'job_function' => 'TECHNICIAN',
            'whatsapp'     => '08333333333',
        ]);
        $this->tech1->attachRole('technician');

        $this->tech2 = User::factory()->create([
            'branch_id'    => $this->branch2->id,
            'is_active'    => true,
            'job_function' => 'TECHNICIAN',
            'whatsapp'     => '08444444444',
        ]);
        $this->tech2->attachRole('technician');

        $this->customer1 = Customer::create([
            'name'      => 'Pelanggan Surabaya 1',
            'phone'     => '08123456789',
            'address'   => 'Jl. Pahlawan No. 1, Surabaya',
            'branch_id' => $this->branch1->id,
            'status'    => 'active',
        ]);

        $this->customer2 = Customer::create([
            'name'      => 'Pelanggan Malang 2',
            'phone'     => '08987654321',
            'address'   => 'Jl. Ijen No. 2, Malang',
            'branch_id' => $this->branch2->id,
            'status'    => 'active',
        ]);

        $this->slaService = app(SlaTrackingService::class);
        $this->notificationService = app(FieldServiceNotificationService::class);
    }

    /**
     * 1. Target SLA Urgent adalah 2 jam (120 menit).
     */
    public function test_sla_target_urgent_is_2_hours(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0);

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Gangguan FO Putus Urgent',
            'priority'     => 'urgent',
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'customer_id'  => $this->customer1->id,
            'assigned_to'  => $this->tech1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $wo->refresh();

        $this->assertEquals(120, $wo->sla_target_minutes);
        $this->assertEquals('2026-11-23 12:00:00', $wo->sla_due_at->toDateTimeString());
    }

    /**
     * 2. Target SLA High adalah 8 jam (480 menit).
     */
    public function test_sla_target_high_is_8_hours(): void
    {
        $start = Carbon::create(2026, 11, 23, 8, 0, 0);

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Redaman Drop High',
            'priority'     => 'high',
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'customer_id'  => $this->customer1->id,
            'assigned_to'  => $this->tech1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $wo->refresh();

        $this->assertEquals(480, $wo->sla_target_minutes);
        $this->assertEquals('2026-11-23 16:00:00', $wo->sla_due_at->toDateTimeString());
    }

    /**
     * 3. Target SLA Medium/Normal adalah 24 jam (1440 menit).
     */
    public function test_sla_target_medium_is_24_hours(): void
    {
        $start = Carbon::create(2026, 11, 23, 9, 0, 0);

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Ganti Router Medium',
            'priority'     => 'medium',
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'customer_id'  => $this->customer1->id,
            'assigned_to'  => $this->tech1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $wo->refresh();

        $this->assertEquals(1440, $wo->sla_target_minutes);
        $this->assertEquals('2026-11-24 09:00:00', $wo->sla_due_at->toDateTimeString());
    }

    /**
     * 4. Target SLA Low adalah 48 jam (2880 menit).
     */
    public function test_sla_target_low_is_48_hours(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0);

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Pengecekan Rutin Low',
            'priority'     => 'low',
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'customer_id'  => $this->customer1->id,
            'assigned_to'  => $this->tech1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $wo->refresh();

        $this->assertEquals(2880, $wo->sla_target_minutes);
        $this->assertEquals('2026-11-25 10:00:00', $wo->sla_due_at->toDateTimeString());
    }

    /**
     * 5. Penghitungan persentase SLA dan ambang 80%.
     */
    public function test_80_percent_threshold_calculation(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0); // target 2 jam (120 min) -> due 12:00
        $now = Carbon::create(2026, 11, 23, 11, 36, 0);  // 96 min dari 120 min = tepat 80.0%

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Testing Ambang 80%',
            'priority'     => 'urgent',
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $metrics = $this->slaService->calculateMetrics($wo, $now);

        $this->assertEquals(80.0, $metrics['percentage_used']);
        $this->assertEquals('warning', $metrics['status']);
        $this->assertEquals('amber', $metrics['badge_color']);
        $this->assertEquals(24, $metrics['remaining_minutes']);
    }

    /**
     * 6. Status warning saat pemakaian waktu mencapai atau melewati 80% tetapi belum terlambat.
     */
    public function test_status_warning_at_80_percent_and_above(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0); // target 2 jam (120 min)
        $now = Carbon::create(2026, 11, 23, 11, 45, 0);  // 105 min = 87.5%

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Status Warning Test',
            'priority'     => 'urgent',
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $metrics = $this->slaService->calculateMetrics($wo, $now);

        $this->assertEquals('warning', $metrics['status']);
        $this->assertEquals(87.5, $metrics['percentage_used']);
    }

    /**
     * 7. Status breached ketika waktu sekarang melewati sla_due_at.
     */
    public function test_status_breached_when_overdue(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0); // target 2 jam -> due 12:00
        $now = Carbon::create(2026, 11, 23, 12, 15, 0);  // 15 menit terlambat

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Breached Test',
            'priority'     => 'urgent',
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $metrics = $this->slaService->calculateMetrics($wo, $now);

        $this->assertEquals('breached', $metrics['status']);
        $this->assertEquals('rose', $metrics['badge_color']);
        $this->assertLessThan(0, $metrics['remaining_minutes']);
        $this->assertStringContainsString('Terlambat', $metrics['remaining_formatted']);
    }

    /**
     * 8. Pekerjaan yang sudah selesai tidak dihitung sebagai aktif dan tidak ikut dieskalasi.
     */
    public function test_completed_jobs_are_not_escalated(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0);

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Pekerjaan Selesai',
            'priority'     => 'urgent',
            'status'       => 'completed',
            'branch_id'    => $this->branch1->id,
            'started_at'   => $start,
            'completed_at' => $start->copy()->addMinutes(60), // selesai dalam 1 jam (tepat waktu)
        ]);

        $this->slaService->initializeSla($wo, $start);
        $activeJobs = $this->slaService->getUniqueActiveJobs();

        // Tidak boleh muncul di active jobs
        $this->assertFalse($activeJobs->contains('entity_id', $wo->id));

        $metrics = $this->slaService->calculateMetrics($wo);
        $this->assertEquals('completed', $metrics['status']);
        $this->assertTrue($metrics['is_completed']);
    }

    /**
     * 9. Status pending_approval tetap dihitung sebagai SLA berjalan sampai QC disetujui (Phase 4.1).
     */
    public function test_pending_approval_continues_sla_counting(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0); // target 2 jam -> due 12:00
        $now = Carbon::create(2026, 11, 23, 12, 10, 0);  // 10 menit lewat due date

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Menunggu QC',
            'priority'     => 'urgent',
            'status'       => 'pending_approval',
            'branch_id'    => $this->branch1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $metrics = $this->slaService->calculateMetrics($wo, $now);

        // Karena pending_approval belum final completed, maka statusnya terhitung breached jika lewat due date
        $this->assertEquals('breached', $metrics['status']);
        $this->assertFalse($metrics['is_completed']);
    }

    /**
     * 10. Perubahan prioritas tidak merusak snapshot SLA historis (Immutable Snapshot).
     */
    public function test_priority_change_does_not_corrupt_historical_snapshot(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0);

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Priority Immutable Test',
            'priority'     => 'urgent', // 2 jam = 120 menit
            'status'       => 'in_progress',
            'branch_id'    => $this->branch1->id,
            'started_at'   => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $wo->refresh();
        $originalDueAt = $wo->sla_due_at->toDateTimeString();

        // Ubah prioritas field menjadi low (48 jam)
        $wo->priority = 'low';
        $wo->save();

        // Inisialisasi ulang tanpa force
        $this->slaService->initializeSla($wo);
        $wo->refresh();

        // Target menit dan due date snapshot historis tidak boleh berubah
        $this->assertEquals(120, $wo->sla_target_minutes);
        $this->assertEquals($originalDueAt, $wo->sla_due_at->toDateTimeString());
    }

    /**
     * 11. Ticket dan WorkOrder yang terhubung tidak menghasilkan eskalasi ganda (SSOT Deduplication).
     */
    public function test_linked_ticket_and_work_order_do_not_produce_double_escalation(): void
    {
        $ticket = Ticket::create([
            'uuid'        => (string) Str::uuid(),
            'title'       => 'Tiket Gangguan Terhubung WO',
            'status'      => 'in_progress',
            'priority'    => 'urgent',
            'branch_id'   => $this->branch1->id,
            'customer_id' => $this->customer1->id,
            'assigned_to' => $this->tech1->id,
        ]);

        $wo = WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'ticket_id'   => $ticket->id,
            'title'       => 'WO Lapangan untuk Tiket #' . $ticket->id,
            'status'      => 'in_progress',
            'priority'    => 'urgent',
            'branch_id'   => $this->branch1->id,
            'customer_id' => $this->customer1->id,
            'assigned_to' => $this->tech1->id,
        ]);

        $activeJobs = $this->slaService->getUniqueActiveJobs();

        // Hanya WorkOrder yang masuk sebagai pekerjaan operasional aktif
        $this->assertTrue($activeJobs->contains('operational_key', "wo_{$wo->id}"));
        // Tiket yang terhubung tidak boleh masuk terpisah sebagai item independen
        $this->assertFalse($activeJobs->contains('operational_key', "ticket_{$ticket->id}"));
    }

    /**
     * 12. Command dsbilling:monitor-sla dapat dijalankan berulang tanpa notifikasi duplikat (Idempotent).
     */
    public function test_command_is_idempotent_on_repeated_runs(): void
    {
        $pastStart = now()->subHours(5); // Urgent 2 jam dibuat 5 jam lalu -> sudah breached

        $wo = WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'title'       => 'Pekerjaan Breached Idempotent',
            'priority'    => 'urgent',
            'status'      => 'in_progress',
            'branch_id'   => $this->branch1->id,
            'customer_id' => $this->customer1->id,
            'assigned_to' => $this->tech1->id,
            'started_at'  => $pastStart,
        ]);

        $this->slaService->initializeSla($wo, $pastStart);

        // Jalankan command pertama kali
        $exitCode1 = Artisan::call('dsbilling:monitor-sla');
        $this->assertEquals(0, $exitCode1);

        $wo->refresh();
        $this->assertNotNull($wo->sla_breached_sent_at);
        $firstSentAt = $wo->sla_breached_sent_at->toDateTimeString();

        $initialNotifCount = Notification::where('data->entity_id', $wo->id)->count();
        $this->assertGreaterThan(0, $initialNotifCount);

        // Jalankan command kedua kali (harus idempotent)
        $exitCode2 = Artisan::call('dsbilling:monitor-sla');
        $this->assertEquals(0, $exitCode2);

        $wo->refresh();
        $this->assertEquals($firstSentAt, $wo->sla_breached_sent_at->toDateTimeString());

        $secondNotifCount = Notification::where('data->entity_id', $wo->id)->count();
        $this->assertEquals($initialNotifCount, $secondNotifCount, 'Tidak boleh ada duplikasi notifikasi saat command dijalankan ulang.');
    }

    /**
     * 13. Eskalasi hanya masuk kepada penerima yang berwenang (anti kebocoran lintas pengawas).
     */
    public function test_escalation_only_sent_to_authorized_branch_supervisors(): void
    {
        $pastStart = now()->subHours(4);

        $wo = WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'title'       => 'Pekerjaan Cabang Surabaya Breached',
            'priority'    => 'urgent',
            'status'      => 'in_progress',
            'branch_id'   => $this->branch1->id,
            'customer_id' => $this->customer1->id,
            'assigned_to' => $this->tech1->id,
            'started_at'  => $pastStart,
        ]);

        $this->slaService->initializeSla($wo, $pastStart);
        Artisan::call('dsbilling:monitor-sla');

        // Admin Cabang 1 (Surabaya) harus menerima notifikasi
        $admin1Notifs = Notification::where('recipient_id', $this->adminBranch1->id)
            ->where('data->entity_id', $wo->id)
            ->count();
        $this->assertGreaterThan(0, $admin1Notifs);

        // Admin Cabang 2 (Malang) TIDAK BOLEH menerima notifikasi cabang Surabaya
        $admin2Notifs = Notification::where('recipient_id', $this->adminBranch2->id)
            ->where('data->entity_id', $wo->id)
            ->count();
        $this->assertEquals(0, $admin2Notifs, 'Admin Cabang 2 tidak boleh menerima notifikasi pelanggaran SLA dari Cabang 1.');
    }

    /**
     * 14. Isolasi Company, Branch, dan Reseller dalam query dan laporan SLA.
     */
    public function test_company_branch_and_reseller_isolation_in_reports(): void
    {
        $now = now();

        // WO Cabang 1
        WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'title'       => 'WO Cabang Surabaya',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'branch_id'   => $this->branch1->id,
            'created_at'  => $now,
        ]);

        // WO Cabang 2
        WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'title'       => 'WO Cabang Malang',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'branch_id'   => $this->branch2->id,
            'created_at'  => $now,
        ]);

        // Laporan saat diakses oleh Admin Cabang 1 (Surabaya)
        $reportBranch1 = $this->slaService->getSlaReport([], $this->adminBranch1);
        $this->assertEquals(1, $reportBranch1['total_jobs']);
        $this->assertArrayHasKey('Cabang Surabaya', $reportBranch1['by_branch']);
        $this->assertArrayNotHasKey('Cabang Malang', $reportBranch1['by_branch']);

        // Laporan saat diakses oleh Admin Cabang 2 (Malang)
        $reportBranch2 = $this->slaService->getSlaReport([], $this->adminBranch2);
        $this->assertEquals(1, $reportBranch2['total_jobs']);
        $this->assertArrayHasKey('Cabang Malang', $reportBranch2['by_branch']);
        $this->assertArrayNotHasKey('Cabang Surabaya', $reportBranch2['by_branch']);
    }

    /**
     * 15. Countdown menggunakan timestamp backend (server_now_unix, due_at_unix, started_at_unix).
     */
    public function test_countdown_metrics_use_backend_timestamps(): void
    {
        $start = Carbon::create(2026, 11, 23, 10, 0, 0);
        $now = Carbon::create(2026, 11, 23, 10, 30, 0);

        $wo = WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'title'       => 'Test Countdown Timestamps',
            'priority'    => 'urgent',
            'status'      => 'in_progress',
            'branch_id'   => $this->branch1->id,
            'started_at'  => $start,
        ]);

        $this->slaService->initializeSla($wo, $start);
        $metrics = $this->slaService->calculateMetrics($wo, $now);

        $this->assertArrayHasKey('server_now_unix', $metrics);
        $this->assertArrayHasKey('due_at_unix', $metrics);
        $this->assertArrayHasKey('started_at_unix', $metrics);

        $this->assertEquals($now->timestamp, $metrics['server_now_unix']);
        $this->assertEquals($start->copy()->addMinutes(120)->timestamp, $metrics['due_at_unix']);
        $this->assertEquals($start->timestamp, $metrics['started_at_unix']);
    }

    /**
     * 16. Kegagalan Telegram/WhatsApp tidak menggagalkan proses SLA monitoring.
     */
    public function test_provider_failure_does_not_halt_sla_command(): void
    {
        $pastStart = now()->subHours(6);

        $wo = WorkOrder::create([
            'uuid'        => (string) Str::uuid(),
            'title'       => 'Resilience Test Against Provider Failure',
            'priority'    => 'urgent',
            'status'      => 'in_progress',
            'branch_id'   => $this->branch1->id,
            'customer_id' => $this->customer1->id,
            'assigned_to' => $this->tech1->id,
            'started_at'  => $pastStart,
        ]);

        $this->slaService->initializeSla($wo, $pastStart);

        // Simulasi network error pada HTTP provider
        Http::fake([
            '*' => Http::response('Gateway Timeout', 504),
        ]);

        // Command tetap harus berjalan mulus dan return Command::SUCCESS (0)
        $exitCode = Artisan::call('dsbilling:monitor-sla');
        $this->assertEquals(0, $exitCode);

        $wo->refresh();
        $this->assertNotNull($wo->sla_breached_sent_at);
        $this->assertEquals('breached', $wo->sla_status);
    }
}
