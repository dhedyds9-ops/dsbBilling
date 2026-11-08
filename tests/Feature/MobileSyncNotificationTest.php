<?php

namespace Tests\Feature;

use App\Models\Master\Branch;
use App\Models\Notification\Notification;
use App\Models\Role;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\WorkOrderChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileSyncNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);
        $this->branch = Branch::firstOrCreate(['code' => 'BR-SYNC'], ['name' => 'Cabang Offline Sync']);

        $this->technician = User::factory()->create([
            'name' => 'Bayu Teknisi Sync',
            'email' => 'bayu@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);
    }

    public function test_technician_can_get_offline_bootstrap_data(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'SPK Bootstrap Test',
            'type' => 'installation',
            'status' => 'assigned',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/sync/bootstrap');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'server_time',
                    'unread_notifications',
                    'work_orders_count',
                    'work_orders',
                    'custody_count',
                    'custody_items',
                    'odps_count',
                    'odps',
                ],
            ]);
    }

    public function test_technician_can_process_offline_batch_actions(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'SPK Offline Batch',
            'type' => 'repair',
            'status' => 'assigned',
            'assigned_to' => $this->technician->id,
            'branch_id' => $this->branch->id,
        ]);

        $chk = WorkOrderChecklist::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $wo->id,
            'item' => 'Cek Koneksi Roset',
            'completed' => false,
        ]);

        $batchPayload = [
            'device_id' => 'android-offline-device-01',
            'synced_at' => now()->toIso8601String(),
            'actions' => [
                [
                    'uuid' => (string) Str::uuid(),
                    'action_type' => 'status_update',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'status' => 'in_progress',
                        'notes' => 'Mulai pengerjaan di lokasi tanpa sinyal',
                    ],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'action_type' => 'checklist_toggle',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'checklist_id' => $chk->id,
                        'completed' => true,
                    ],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'action_type' => 'measurement',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'optical_rx_power' => -19.35,
                        'optical_tx_power' => 2.10,
                        'notes' => 'Ukur redaman optik di roset',
                    ],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'action_type' => 'signature',
                    'work_order_id' => $wo->id,
                    'payload' => [
                        'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/sync/offline-batch', $batchPayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'processed_count' => 4,
                'success_count' => 4,
                'failed_count' => 0,
            ]);

        // Verify WorkOrder updated
        $wo->refresh();
        $this->assertEquals('in_progress', $wo->status);
        $this->assertEquals(-19.35, (float) $wo->optical_rx_power);
        $this->assertNotNull($wo->customer_signature);

        // Verify Checklist toggled
        $chk->refresh();
        $this->assertTrue((bool) $chk->completed);
    }

    public function test_technician_can_list_notifications_and_mark_as_read(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        $notif = Notification::create([
            'uuid' => (string) Str::uuid(),
            'recipient_id' => $this->technician->id,
            'title' => 'Penugasan SPK Baru #44',
            'message' => 'Anda ditugaskan ke SPK #44 Pasang Baru.',
            'status' => 'pending',
            'type' => 'in_app',
        ]);

        // 1. List notifications
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 1,
            ])
            ->assertJsonFragment([
                'title' => 'Penugasan SPK Baru #44',
            ]);

        // 2. Mark as read
        $readResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/mobile/notifications/{$notif->id}/read");

        $readResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // 3. Confirm unread count is 0
        $this->assertDatabaseHas('notifications', [
            'id' => $notif->id,
            'status' => 'read',
        ]);
    }

    public function test_technician_can_mark_all_notifications_as_read(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        Notification::create([
            'uuid' => (string) Str::uuid(),
            'recipient_id' => $this->technician->id,
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'status' => 'pending',
        ]);
        Notification::create([
            'uuid' => (string) Str::uuid(),
            'recipient_id' => $this->technician->id,
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/notifications/mark-all-read');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $unread = Notification::where('recipient_id', $this->technician->id)
            ->where('status', '!=', 'read')
            ->count();
        $this->assertEquals(0, $unread);
    }

    public function test_invalid_action_type_is_handled_gracefully(): void
    {
        $token = $this->technician->createToken('test-mobile-token')->plainTextToken;

        $batchPayload = [
            'actions' => [
                [
                    'uuid' => (string) Str::uuid(),
                    'action_type' => 'unknown_invalid_action',
                    'payload' => [],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/sync/offline-batch', $batchPayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'success_count' => 0,
                'failed_count' => 1,
            ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/mobile/sync/bootstrap');
        $response->assertStatus(401);
    }
}
