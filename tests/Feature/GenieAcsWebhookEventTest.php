<?php

namespace Tests\Feature;

use App\Events\ISP\Tr069StatusChanged;
use App\Jobs\ACS\ProcessGenieAcsInformEventJob;
use App\Models\ACS\ACSDevice;
use App\Models\ACS\GenieAcsWebhookEvent;
use App\Models\Customer\CustomerService;
use App\Models\ISP\DeviceCorrelation;
use App\Models\ISP\Onu;
use App\Models\ISP\Vendor;
use App\Models\Master\Branch;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GenieAcsWebhookEventTest extends TestCase
{
    use RefreshDatabase;

    protected string $secret = 'test-genieacs-secret-key-12345';

    protected function setUp(): void
    {
        parent::setUp();
        config(['genieacs.webhook_secret' => $this->secret]);
        config(['genieacs.webhook_tolerance_seconds' => 300]);
    }

    protected function makeSignedRequest(array $payload, ?string $timestamp = null, ?string $customSecret = null)
    {
        $json = json_encode($payload);
        $secret = $customSecret ?? $this->secret;
        $signature = hash_hmac('sha256', $json, $secret);
        $ts = $timestamp ?? now()->toIso8601String();

        return $this->withHeaders([
            'Content-Type' => 'application/json',
            'X-GenieACS-Signature' => "sha256={$signature}",
            'X-GenieACS-Timestamp' => $ts,
        ])->postJson('/api/webhooks/genieacs/inform', $payload);
    }

    /** 1. Inform 0 BOOTSTRAP diterima */
    public function test_inform_0_bootstrap_received(): void
    {
        Queue::fake();

        $payload = [
            'event_id' => 'evt-bootstrap-001',
            'device_id' => '000AC2-HWTC-TEST01',
            'serial_number' => 'HWTC11223344',
            'event_codes' => ['0 BOOTSTRAP'],
            'manufacturer' => 'Huawei',
            'product_class' => 'HG8245H',
            'software_version' => 'V3R017C10S100',
            'last_inform' => now()->toIso8601String(),
            'received_at' => now()->toIso8601String(),
        ];

        $response = $this->makeSignedRequest($payload);

        $response->assertStatus(202)
            ->assertJson([
                'status' => 'accepted',
                'event_id' => 'evt-bootstrap-001',
            ]);

        $this->assertDatabaseHas('genieacs_webhook_events', [
            'event_id' => 'evt-bootstrap-001',
            'device_id' => '000AC2-HWTC-TEST01',
            'serial_number' => 'HWTC11223344',
            'processing_status' => 'queued',
        ]);

        Queue::assertPushed(ProcessGenieAcsInformEventJob::class);
    }

    /** 2. Inform 1 BOOT diterima */
    public function test_inform_1_boot_received(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG01']);
        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'ONT Test Boot',
            'serial_number' => 'HWTC-BOOT-01',
            'status' => 'inactive',
        ]);

        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-boot-001',
            'device_id' => '000AC2-BOOT-01',
            'serial_number' => 'HWTC-BOOT-01',
            'event_codes' => ['1 BOOT'],
            'last_inform_at' => now(),
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        Event::fake([Tr069StatusChanged::class]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        $this->assertEquals('active', $onu->status);
        $this->assertNotNull($onu->attributes['last_boot_at'] ?? null);

        $this->assertDatabaseHas('genieacs_webhook_events', [
            'id' => $event->id,
            'processing_status' => 'processed',
        ]);

        Event::assertDispatched(Tr069StatusChanged::class);
    }

    /** 3. Inform 2 PERIODIC diterima */
    public function test_inform_2_periodic_received(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG02']);
        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'ONT Test Periodic',
            'serial_number' => 'ZTEG-PERIODIC-01',
            'status' => 'inactive',
        ]);

        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-periodic-001',
            'device_id' => 'ZTEG-PERIODIC-01',
            'serial_number' => 'ZTEG-PERIODIC-01',
            'event_codes' => ['2 PERIODIC'],
            'last_inform_at' => now(),
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        $this->assertEquals('active', $onu->status);
        $this->assertNotNull($onu->last_seen_at);
    }

    /** 4. Inform 4 VALUE CHANGE diterima */
    public function test_inform_4_value_change_received(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG03']);
        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'ONT Value Change',
            'serial_number' => 'HWTC-VC-01',
            'status' => 'active',
        ]);

        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-vc-001',
            'device_id' => 'HWTC-VC-01',
            'serial_number' => 'HWTC-VC-01',
            'event_codes' => ['4 VALUE CHANGE'],
            'software_version' => 'V5.2.0',
            'last_inform_at' => now(),
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        $this->assertEquals('V5.2.0', $onu->firmware_version);
    }

    /** 5. Inform 6 CONNECTION REQUEST diterima */
    public function test_inform_6_connection_request_received(): void
    {
        Queue::fake();

        $payload = [
            'event_id' => 'evt-cr-001',
            'device_id' => '000AC2-CONN-REQ',
            'serial_number' => 'HWTC-CR-01',
            'event_codes' => ['6 CONNECTION REQUEST'],
            'last_inform' => now()->toIso8601String(),
            'received_at' => now()->toIso8601String(),
        ];

        $response = $this->makeSignedRequest($payload);
        $response->assertStatus(202);

        $this->assertDatabaseHas('genieacs_webhook_events', [
            'event_id' => 'evt-cr-001',
        ]);
    }

    /** 6. HMAC valid diterima */
    public function test_hmac_valid_accepted(): void
    {
        $payload = [
            'event_id' => 'evt-hmac-ok',
            'device_id' => '000AC2-HMAC-OK',
            'serial_number' => 'SN-HMAC-OK',
            'event_codes' => ['2 PERIODIC'],
            'last_inform' => now()->toIso8601String(),
            'received_at' => now()->toIso8601String(),
        ];

        $response = $this->makeSignedRequest($payload);
        $response->assertStatus(202);
    }

    /** 7. HMAC invalid ditolak (401) */
    public function test_hmac_invalid_rejected(): void
    {
        $payload = [
            'event_id' => 'evt-hmac-bad',
            'device_id' => '000AC2-HMAC-BAD',
            'serial_number' => 'SN-HMAC-BAD',
            'event_codes' => ['2 PERIODIC'],
            'last_inform' => now()->toIso8601String(),
            'received_at' => now()->toIso8601String(),
        ];

        $response = $this->makeSignedRequest($payload, null, 'wrong-secret-key');
        $response->assertStatus(401)
            ->assertJson(['status' => 'error', 'message' => 'Invalid signature.']);
    }

    /** 8. Timestamp expired ditolak (400) */
    public function test_timestamp_expired_rejected(): void
    {
        $payload = [
            'event_id' => 'evt-expired',
            'device_id' => '000AC2-EXPIRED',
            'serial_number' => 'SN-EXPIRED',
            'event_codes' => ['2 PERIODIC'],
            'last_inform' => now()->subMinutes(10)->toIso8601String(),
            'received_at' => now()->subMinutes(10)->toIso8601String(),
        ];

        $response = $this->makeSignedRequest($payload, now()->subMinutes(10)->toIso8601String());
        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
    }

    /** 9. Duplicate event tidak diproses dua kali */
    public function test_duplicate_event_idempotent(): void
    {
        Queue::fake();

        $payload = [
            'event_id' => 'evt-duplicate-idempotent',
            'device_id' => '000AC2-DEDUP',
            'serial_number' => 'SN-DEDUP',
            'event_codes' => ['2 PERIODIC'],
            'last_inform' => now()->toIso8601String(),
            'received_at' => now()->toIso8601String(),
        ];

        // First attempt
        $res1 = $this->makeSignedRequest($payload);
        $res1->assertStatus(202)->assertJson(['status' => 'accepted']);

        // Second attempt with same event_id
        $res2 = $this->makeSignedRequest($payload);
        $res2->assertStatus(202)->assertJson(['status' => 'duplicate']);

        // Queue must only have been pushed once
        Queue::assertPushed(ProcessGenieAcsInformEventJob::class, 1);
    }

    /** 10. Unknown device ditangani (marked ignored, not creating fake customer/onu) */
    public function test_unknown_device_handled(): void
    {
        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-unknown-device',
            'device_id' => 'UNKNOWN-DEVICE-999',
            'serial_number' => 'SN-UNKNOWN-999',
            'event_codes' => ['2 PERIODIC'],
            'last_inform_at' => now(),
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $event->refresh();
        $this->assertEquals(GenieAcsWebhookEvent::STATUS_IGNORED, $event->processing_status);
        $this->assertStringContainsString('belum terdaftar', $event->error_message);

        // Verify NO fake Customer or ONU was created
        $this->assertDatabaseMissing('onus', ['serial_number' => 'SN-UNKNOWN-999']);
    }

    /** 11. Serial number fallback berhasil (Hex vs ASCII) */
    public function test_serial_number_fallback_hex_ascii(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG04']);
        // ONU registered as ASCII "HWTC12345678"
        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'Huawei ONT',
            'serial_number' => 'HWTC12345678',
            'status' => 'inactive',
        ]);

        // GenieACS sends HEX "4857544312345678"
        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-hex-ascii',
            'device_id' => '000AC2-4857544312345678',
            'serial_number' => '4857544312345678',
            'event_codes' => ['2 PERIODIC'],
            'last_inform_at' => now(),
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        $this->assertEquals('active', $onu->status);
        $this->assertEquals('processed', $event->refresh()->processing_status);
    }

    /** 12. MAC fallback melalui CorrelationService */
    public function test_mac_fallback_via_correlation_service(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG05']);
        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'ZTE MAC Fallback',
            'serial_number' => 'UNKNOWN-SN',
            'mac_address' => '00:11:22:33:44:55',
            'status' => 'inactive',
        ]);

        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-mac-fallback',
            'device_id' => 'ZTE-DEV-MAC-1',
            'serial_number' => 'ZTE-SN-MAC-1',
            'event_codes' => ['2 PERIODIC'],
            'payload' => ['mac_address' => '00:11:22:33:44:55'],
            'last_inform_at' => now(),
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        $this->assertEquals('active', $onu->status);
        $this->assertEquals('processed', $event->refresh()->processing_status);
    }

    /** 13. Event lama tidak menimpa Inform baru */
    public function test_stale_event_does_not_overwrite_newer_inform(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG06']);
        $newerTime = Carbon::now();
        $olderTime = Carbon::now()->subMinutes(30);

        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'ONT Timestamp Check',
            'serial_number' => 'HWTC-TIME-01',
            'status' => 'active',
            'last_seen_at' => $newerTime,
        ]);

        // Event arrived late with older Inform timestamp
        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-stale-order',
            'device_id' => 'HWTC-TIME-01',
            'serial_number' => 'HWTC-TIME-01',
            'event_codes' => ['2 PERIODIC'],
            'last_inform_at' => $olderTime,
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        // ONU's last_seen_at must remain the newer timestamp!
        $this->assertEquals($newerTime->timestamp, $onu->last_seen_at->timestamp);
        $this->assertEquals('processed', $event->refresh()->processing_status);
    }

    /** 14. Status online diperbarui */
    public function test_status_online_updated(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG07']);
        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'ONT Online Status',
            'serial_number' => 'HWTC-ONLINE-01',
            'status' => 'inactive',
        ]);

        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-online-up',
            'device_id' => 'HWTC-ONLINE-01',
            'serial_number' => 'HWTC-ONLINE-01',
            'event_codes' => ['2 PERIODIC'],
            'last_inform_at' => now(),
            'received_at' => now(),
            'processing_status' => 'queued',
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        $this->assertEquals('active', $onu->status);

        $acs = ACSDevice::where('serial_number', 'HWTC-ONLINE-01')->first();
        $this->assertNotNull($acs);
        $this->assertEquals('online', $acs->status);
    }

    /** 15. Status stale/offline berdasarkan threshold command acs:evaluate-stale-devices */
    public function test_stale_offline_evaluation_command(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'code' => 'CBG08']);
        $onu = Onu::create([
            'branch_id' => $branch->id,
            'name' => 'ONT Stale Eval',
            'serial_number' => 'HWTC-STALE-01',
            'status' => 'active',
            'last_seen_at' => now()->subMinutes(20),
        ]);

        $acs = ACSDevice::create([
            'uuid' => 'HWTC-STALE-01',
            'serial_number' => 'HWTC-STALE-01',
            'onu_id' => $onu->id,
            'status' => 'online',
            'last_inform' => now()->subMinutes(20), // > (300*2)+120s = 12 mins -> offline
        ]);

        $this->artisan('acs:evaluate-stale-devices')
            ->assertSuccessful();

        $acs->refresh();
        $this->assertEquals('offline', $acs->status);

        $onu->refresh();
        $this->assertEquals('inactive', $onu->status);
    }

    /** 16. Queue retry berjalan */
    public function test_queue_retry_mechanism(): void
    {
        $event = GenieAcsWebhookEvent::create([
            'event_id' => 'evt-retry-01',
            'device_id' => 'DEV-RETRY-01',
            'serial_number' => 'SN-RETRY-01',
            'event_codes' => ['2 PERIODIC'],
            'processing_status' => 'queued',
            'received_at' => now(),
            'retry_count' => 0,
        ]);

        $job = new ProcessGenieAcsInformEventJob($event->id);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 60, 300], $job->backoff);
    }

    /** 17. Tenant isolation (company/branch tidak boleh ditentukan dari payload luar) */
    public function test_tenant_isolation(): void
    {
        $realBranch = Branch::create(['name' => 'Cabang Asli', 'code' => 'REAL01']);
        $fakeBranch = Branch::create(['name' => 'Cabang Palsu', 'code' => 'FAKE01']);

        $onu = Onu::create([
            'branch_id' => $realBranch->id,
            'name' => 'ONT Tenant Test',
            'serial_number' => 'HWTC-TENANT-01',
            'status' => 'inactive',
        ]);

        // Attacker payload attempts to inject branch_id = fakeBranch
        $payload = [
            'event_id' => 'evt-tenant-iso',
            'device_id' => 'HWTC-TENANT-01',
            'serial_number' => 'HWTC-TENANT-01',
            'branch_id' => $fakeBranch->id,
            'company_id' => 999,
            'event_codes' => ['2 PERIODIC'],
            'last_inform' => now()->toIso8601String(),
            'received_at' => now()->toIso8601String(),
        ];

        $response = $this->makeSignedRequest($payload);
        $response->assertStatus(202);

        $event = GenieAcsWebhookEvent::where('event_id', 'evt-tenant-iso')->first();
        $job = new ProcessGenieAcsInformEventJob($event->id);
        $job->handle(app(\App\Services\ISP\CorrelationService::class), app(\App\Services\ISP\UnifiedDeviceStateEngine::class));

        $onu->refresh();
        // ONU must retain its real branch_id and NOT be hijacked!
        $this->assertEquals($realBranch->id, $onu->branch_id);
    }

    /** 18. Branch isolation pada query */
    public function test_branch_isolation(): void
    {
        $branchA = Branch::create(['name' => 'Branch A', 'code' => 'BRA01']);
        $branchB = Branch::create(['name' => 'Branch B', 'code' => 'BRB01']);

        $onuA = Onu::create(['branch_id' => $branchA->id, 'serial_number' => 'SN-BRANCH-A', 'name' => 'ONT A']);
        $onuB = Onu::create(['branch_id' => $branchB->id, 'serial_number' => 'SN-BRANCH-B', 'name' => 'ONT B']);

        $this->assertNotEquals($onuA->branch_id, $onuB->branch_id);
    }

    /** 19. Credential tidak bocor ke log */
    public function test_credentials_not_leaked_in_logs(): void
    {
        $payload = [
            'event_id' => 'evt-cred-safe',
            'device_id' => 'HWTC-SAFE-01',
            'serial_number' => 'HWTC-SAFE-01',
            'event_codes' => ['2 PERIODIC'],
            'last_inform' => now()->toIso8601String(),
            'received_at' => now()->toIso8601String(),
        ];

        $response = $this->makeSignedRequest($payload);
        $response->assertStatus(202);

        $event = GenieAcsWebhookEvent::where('event_id', 'evt-cred-safe')->first();
        $storedPayload = json_encode($event->payload);

        $this->assertStringNotContainsString('password', strtolower($storedPayload));
        $this->assertStringNotContainsString('wpa_key', strtolower($storedPayload));
    }

    /** 20. Extension resilience and failure handling */
    public function test_genieacs_resilient_to_slow_or_error_responses(): void
    {
        $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
        $script = $driver->getEventBridgeProvisionScript();
        $preset = $driver->getEventBridgePresetData();

        $this->assertStringContainsString('dsBilling_Event_Ingestion', $script);
        $this->assertStringContainsString('ext("dsbilling-webhook"', $script);
        $this->assertArrayHasKey('0 BOOTSTRAP', $preset['events']);
        $this->assertArrayHasKey('1 BOOT', $preset['events']);
        $this->assertArrayHasKey('2 PERIODIC', $preset['events']);
        $this->assertArrayHasKey('4 VALUE CHANGE', $preset['events']);
        $this->assertArrayHasKey('6 CONNECTION REQUEST', $preset['events']);
    }
}
