<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Events\FieldService\TicketOperationalEvent;
use App\Events\FieldService\WorkOrderOperationalEvent;
use App\Jobs\Notifications\SendFcmNotificationJob;
use App\Models\CRM\Customer;
use App\Models\Master\Branch;
use App\Models\Notification\Notification;
use App\Models\Role;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\UserDeviceToken;
use App\Models\Workforce\WorkOrder;
use App\Services\Notifications\Fcm\FcmPushService;
use App\Services\Notifications\FieldServiceNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FcmPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $tech1;
    protected User $tech2;
    protected Branch $branch;
    protected Customer $customer;
    protected FieldServiceNotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);
        Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);

        $this->branch = Branch::create([
            'name'      => 'Cabang Surabaya',
            'code'      => 'SBY',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'branch_id'    => $this->branch->id,
            'is_active'    => true,
            'job_function' => 'ADMINISTRATOR',
            'whatsapp'     => '08111111111',
        ]);
        $this->admin->attachRole(UserRole::Administrator->value);

        $this->tech1 = User::factory()->create([
            'branch_id'    => $this->branch->id,
            'is_active'    => true,
            'job_function' => 'TECHNICIAN',
            'whatsapp'     => '08222222222',
        ]);
        $this->tech1->attachRole('technician');

        $this->tech2 = User::factory()->create([
            'branch_id'    => $this->branch->id,
            'is_active'    => true,
            'job_function' => 'TECHNICIAN',
            'whatsapp'     => '08333333333',
        ]);
        $this->tech2->attachRole('technician');

        $this->customer = Customer::create([
            'name'      => 'Pelanggan Uji FCM',
            'email'     => 'fcm_cust@example.com',
            'phone'     => '08555555555',
            'branch_id' => $this->branch->id,
            'status'    => 'active',
        ]);

        $this->notificationService = app(FieldServiceNotificationService::class);
    }

    public function test_authenticated_technician_can_register_device_token(): void
    {
        Sanctum::actingAs($this->tech1);

        $response = $this->postJson('/api/mobile/device-tokens/register', [
            'device_id'   => 'device-pixel-8-001',
            'fcm_token'   => 'fcm_token_sample_abc123_very_long_string_unique',
            'device_name' => 'Google Pixel 8',
            'app_version' => '1.1.26',
            'platform'    => 'android',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'device_id' => 'device-pixel-8-001',
                    'platform'  => 'android',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id'     => $this->tech1->id,
            'device_id'   => 'device-pixel-8-001',
            'fcm_token'   => 'fcm_token_sample_abc123_very_long_string_unique',
            'device_name' => 'Google Pixel 8',
            'app_version' => '1.1.26',
            'is_active'   => 1,
        ]);
    }

    public function test_fcm_token_is_hidden_from_model_serialization(): void
    {
        $token = UserDeviceToken::create([
            'user_id'     => $this->tech1->id,
            'device_id'   => 'device-secret-001',
            'fcm_token'   => 'super-secret-fcm-token-string',
            'device_name' => 'Xiaomi Redmi 12',
            'platform'    => 'android',
            'is_active'   => true,
        ]);

        $array = $token->toArray();
        $this->assertArrayNotHasKey('fcm_token', $array);
        $this->assertStringNotContainsString('super-secret-fcm-token-string', json_encode($token));
    }

    public function test_technician_can_refresh_rotated_device_token(): void
    {
        Sanctum::actingAs($this->tech1);

        // 1. Register initial token
        UserDeviceToken::create([
            'user_id'      => $this->tech1->id,
            'device_id'    => 'device-rot-001',
            'fcm_token'    => 'old-fcm-token-value-12345',
            'is_active'    => true,
            'last_seen_at' => now(),
        ]);

        // 2. Refresh token
        $response = $this->postJson('/api/mobile/device-tokens/refresh', [
            'device_id' => 'device-rot-001',
            'old_token' => 'old-fcm-token-value-12345',
            'new_token' => 'new-rotated-fcm-token-67890',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id'   => $this->tech1->id,
            'device_id' => 'device-rot-001',
            'fcm_token' => 'new-rotated-fcm-token-67890',
            'is_active' => 1,
        ]);
    }

    public function test_technician_can_unregister_device_token_on_logout(): void
    {
        Sanctum::actingAs($this->tech1);

        $token = UserDeviceToken::create([
            'user_id'      => $this->tech1->id,
            'device_id'    => 'device-logout-001',
            'fcm_token'    => 'logout-test-token-12345',
            'is_active'    => true,
            'last_seen_at' => now(),
        ]);

        $response = $this->postJson('/api/mobile/device-tokens/unregister', [
            'device_id' => 'device-logout-001',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $token->refresh();
        $this->assertFalse((bool) $token->is_active);
        $this->assertNotNull($token->revoked_at);
    }

    public function test_multi_device_support_for_single_user(): void
    {
        Sanctum::actingAs($this->tech1);

        // Register Phone
        $this->postJson('/api/mobile/device-tokens/register', [
            'device_id'   => 'device-phone-001',
            'fcm_token'   => 'token-phone-1234567890',
            'device_name' => 'Samsung S24',
        ])->assertStatus(200);

        // Register Tablet
        $this->postJson('/api/mobile/device-tokens/register', [
            'device_id'   => 'device-tablet-002',
            'fcm_token'   => 'token-tablet-0987654321',
            'device_name' => 'Samsung Tab A9',
        ])->assertStatus(200);

        $activeTokens = $this->tech1->activeDeviceTokens()->get();
        $this->assertCount(2, $activeTokens);
    }

    public function test_token_reassignment_revokes_token_from_prior_user(): void
    {
        // User 1 uses token-SHARED
        $token1 = UserDeviceToken::create([
            'user_id'   => $this->tech1->id,
            'device_id' => 'shared-hw-id',
            'fcm_token' => 'token-shared-xyz-123456',
            'is_active' => true,
        ]);

        // User 2 logs into same device with same FCM token
        Sanctum::actingAs($this->tech2);
        $response = $this->postJson('/api/mobile/device-tokens/register', [
            'device_id'   => 'shared-hw-id',
            'fcm_token'   => 'token-shared-xyz-123456',
            'device_name' => 'Shared Company Phone',
        ]);
        $response->assertStatus(200);

        // Token 1 must be revoked
        $token1->refresh();
        $this->assertFalse((bool) $token1->is_active);
        $this->assertNotNull($token1->revoked_at);

        // User 2's token must be active
        $this->assertDatabaseHas('device_tokens', [
            'user_id'   => $this->tech2->id,
            'device_id' => 'shared-hw-id',
            'fcm_token' => 'token-shared-xyz-123456',
            'is_active' => 1,
        ]);
    }

    public function test_device_token_ping_heartbeat(): void
    {
        Sanctum::actingAs($this->tech1);

        $pastTime = now()->subHours(5);
        $token = UserDeviceToken::create([
            'user_id'      => $this->tech1->id,
            'device_id'    => 'device-ping-001',
            'fcm_token'    => 'ping-test-token-12345',
            'is_active'    => true,
            'last_seen_at' => $pastTime,
        ]);

        $this->postJson('/api/mobile/device-tokens/ping', [
            'device_id' => 'device-ping-001',
        ])->assertStatus(200);

        $token->refresh();
        $this->assertTrue($token->last_seen_at->gt($pastTime));
    }

    public function test_fcm_push_service_send_to_user_uses_http_v1(): void
    {
        Config::set('services.fcm.project_id', 'dsbilling-test-project');
        Cache::put('fcm_google_access_token', 'mock_google_oauth2_access_token', 3600);

        UserDeviceToken::create([
            'user_id'      => $this->tech1->id,
            'device_id'    => 'device-fcm-test',
            'fcm_token'    => 'valid_fcm_token_device_abc',
            'is_active'    => true,
            'last_seen_at' => now(),
        ]);

        Http::fake([
            'https://fcm.googleapis.com/v1/projects/dsbilling-test-project/messages:send' => Http::response([
                'name' => 'projects/dsbilling-test-project/messages/msg_1234567890',
            ], 200),
        ]);

        $fcmService = app(FcmPushService::class);
        $results = $fcmService->sendToUser(
            userId: $this->tech1->id,
            title: 'Test Notification',
            body: 'Hello technician',
            data: ['work_order_id' => '999'],
            channelId: 'channel_installations'
        );

        $this->assertEquals(1, $results['total']);
        $this->assertEquals(1, $results['sent']);
        $this->assertEquals(0, $results['failed']);
        $this->assertEquals(0, $results['revoked']);

        // Assert HTTP payload sent to FCM
        Http::assertSent(function ($request) {
            if ($request->url() === 'https://fcm.googleapis.com/v1/projects/dsbilling-test-project/messages:send') {
                $body = $request->data();
                return isset($body['message']['token'])
                    && $body['message']['token'] === 'valid_fcm_token_device_abc'
                    && $body['message']['notification']['title'] === 'Test Notification'
                    && $body['message']['android']['notification']['channel_id'] === 'channel_installations';
            }
            return true;
        });
    }

    public function test_fcm_push_service_auto_revokes_unregistered_invalid_token(): void
    {
        Config::set('services.fcm.project_id', 'dsbilling-test-project');
        Cache::put('fcm_google_access_token', 'mock_google_oauth2_access_token', 3600);

        $deviceToken = UserDeviceToken::create([
            'user_id'      => $this->tech1->id,
            'device_id'    => 'dead-device-001',
            'fcm_token'    => 'invalid_expired_token_123',
            'is_active'    => true,
            'last_seen_at' => now(),
        ]);

        Http::fake([
            'https://fcm.googleapis.com/v1/projects/dsbilling-test-project/messages:send' => Http::response([
                'error' => [
                    'code'    => 404,
                    'message' => 'Requested entity was not found.',
                    'status'  => 'NOT_FOUND',
                    'details' => [
                        [
                            '@type'     => 'type.googleapis.com/google.firebase.fcm.v1.FcmError',
                            'errorCode' => 'UNREGISTERED',
                        ],
                    ],
                ],
            ], 404),
        ]);

        $fcmService = app(FcmPushService::class);
        $results = $fcmService->sendToUser(
            userId: $this->tech1->id,
            title: 'Test Unregistered Token',
            body: 'Should auto-revoke'
        );

        $this->assertEquals(1, $results['total']);
        $this->assertEquals(0, $results['sent']);
        $this->assertEquals(1, $results['revoked']);

        $deviceToken->refresh();
        $this->assertFalse((bool) $deviceToken->is_active);
        $this->assertNotNull($deviceToken->revoked_at);
    }

    public function test_ticket_assigned_event_dispatches_fcm_job(): void
    {
        Queue::fake();

        $ticket = Ticket::create([
            'title'       => 'Internet Drop Tiap Sore',
            'customer_id' => $this->customer->id,
            'branch_id'   => $this->branch->id,
            'status'      => 'open',
            'priority'    => 'high',
            'assigned_to' => $this->tech1->id,
        ]);

        $this->notificationService->handleTicketEvent(
            new TicketOperationalEvent('assigned', $ticket)
        );

        Queue::assertPushedOn('notifications-fcm', SendFcmNotificationJob::class, function ($job) {
            return $job->recipientId === $this->tech1->id
                && $job->channelId === 'channel_tickets'
                && ($job->data['type'] ?? '') === 'ticket_assigned';
        });
    }

    public function test_work_order_assigned_event_dispatches_fcm_job(): void
    {
        Queue::fake();

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Pemasangan Drop Cable Baru',
            'customer_id'  => $this->customer->id,
            'branch_id'    => $this->branch->id,
            'type'         => 'installation',
            'status'       => 'scheduled',
            'priority'     => 'normal',
            'assigned_to'  => $this->tech1->id,
            'technician_id'=> $this->tech1->id,
        ]);

        $this->notificationService->handleWorkOrderEvent(
            new WorkOrderOperationalEvent('assigned', $wo)
        );

        Queue::assertPushedOn('notifications-fcm', SendFcmNotificationJob::class, function ($job) {
            return $job->recipientId === $this->tech1->id
                && $job->channelId === 'channel_installations'
                && ($job->data['type'] ?? '') === 'work_order_assigned';
        });
    }

    public function test_work_order_qc_approved_and_rejected_events_dispatch_fcm_jobs(): void
    {
        Queue::fake();

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Instalasi FTTH Pelanggan 001',
            'customer_id'  => $this->customer->id,
            'branch_id'    => $this->branch->id,
            'type'         => 'installation',
            'status'       => 'pending_approval',
            'assigned_to'  => $this->tech1->id,
            'technician_id'=> $this->tech1->id,
        ]);

        // 1. QC Rejected
        $this->notificationService->handleWorkOrderEvent(
            new WorkOrderOperationalEvent('rejected', $wo, actorId: $this->admin->id, extra: [
                'reason' => 'Kabel redaman tinggi (-29dBm), mohon rapikan splicing.',
            ])
        );

        Queue::assertPushedOn('notifications-fcm', SendFcmNotificationJob::class, function ($job) {
            return $job->recipientId === $this->tech1->id
                && $job->channelId === 'channel_qc'
                && ($job->data['type'] ?? '') === 'qc_rejected';
        });

        // 2. QC Approved
        $this->notificationService->handleWorkOrderEvent(
            new WorkOrderOperationalEvent('approved', $wo, actorId: $this->admin->id)
        );

        Queue::assertPushedOn('notifications-fcm', SendFcmNotificationJob::class, function ($job) {
            return $job->recipientId === $this->tech1->id
                && $job->channelId === 'channel_qc'
                && ($job->data['type'] ?? '') === 'qc_approved';
        });
    }

    public function test_sla_warning_and_breached_events_dispatch_fcm_jobs(): void
    {
        Queue::fake();

        $wo = WorkOrder::create([
            'uuid'         => (string) Str::uuid(),
            'title'        => 'Troubleshoot FO Cut Segmen C',
            'customer_id'  => $this->customer->id,
            'branch_id'    => $this->branch->id,
            'type'         => 'troubleshoot',
            'status'       => 'in_progress',
            'assigned_to'  => $this->tech1->id,
            'technician_id'=> $this->tech1->id,
        ]);

        // 1. SLA Warning
        $this->notificationService->notifySlaWarning($wo, [
            'percent'             => 82,
            'remaining_formatted' => '25 menit tersisa',
            'due_at'              => now()->addMinutes(25),
        ]);

        Queue::assertPushedOn('notifications-fcm', SendFcmNotificationJob::class, function ($job) {
            return $job->recipientId === $this->tech1->id
                && $job->channelId === 'channel_sla'
                && ($job->data['type'] ?? '') === 'sla_warning';
        });

        // 2. SLA Breached
        $this->notificationService->notifySlaBreached($wo, [
            'remaining_formatted' => '15 menit terlambat',
            'overdue_minutes'     => 15,
        ]);

        Queue::assertPushedOn('notifications-fcm', SendFcmNotificationJob::class, function ($job) {
            return $job->recipientId === $this->tech1->id
                && $job->channelId === 'channel_sla'
                && ($job->data['type'] ?? '') === 'sla_breached';
        });
    }

    public function test_fcm_failure_resilience_does_not_break_in_app_or_wa(): void
    {
        // Broken FCM configuration
        Config::set('services.fcm.project_id', '');
        Cache::forget('fcm_google_access_token');

        $ticket = Ticket::create([
            'title'       => 'Kabel Putus Depan Rumah',
            'customer_id' => $this->customer->id,
            'branch_id'   => $this->branch->id,
            'status'      => 'open',
            'priority'    => 'critical',
            'assigned_to' => $this->tech1->id,
        ]);

        $this->notificationService->handleTicketEvent(
            new TicketOperationalEvent('assigned', $ticket)
        );

        // In-App Notification must still be created
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->tech1->id,
            'type'         => 'in_app',
        ]);
    }
}
