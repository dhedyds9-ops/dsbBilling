<?php

namespace Tests\Feature;

use App\Models\CRM\Customer;
use App\Models\Notification\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_retrieve_notifications_and_mark_as_read(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => 'Budi Pelanggan',
            'code' => 'CUST-0099',
            'phone' => '081234567899',
            'status' => 'active',
        ]);

        $notif1 = Notification::create([
            'uuid' => (string) Str::uuid(),
            'recipient_id' => $user->id,
            'title' => 'Tagihan Internet Baru',
            'message' => 'Tagihan internet bulan November telah terbit.',
            'type' => 'billing',
            'status' => 'pending',
        ]);

        $notif2 = Notification::create([
            'uuid' => (string) Str::uuid(),
            'recipient_id' => $user->id,
            'title' => 'Tiket Diproses',
            'message' => 'Tiket aduan Anda sedang ditangani oleh teknisi.',
            'type' => 'ticket',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/customer/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 2,
            ]);

        $this->assertCount(2, $response->json('data'));

        // Mark single as read
        $readRes = $this->actingAs($user, 'sanctum')
            ->postJson("/api/customer/notifications/{$notif1->id}/read");

        $readRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('read', $notif1->fresh()->status);

        // Verify unread count is now 1
        $responseAfter = $this->actingAs($user, 'sanctum')
            ->getJson('/api/customer/notifications');

        $responseAfter->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 1,
            ]);

        // Mark all as read
        $allReadRes = $this->actingAs($user, 'sanctum')
            ->postJson('/api/customer/notifications/mark-all-read');

        $allReadRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('read', $notif2->fresh()->status);

        $responseFinal = $this->actingAs($user, 'sanctum')
            ->getJson('/api/customer/notifications');

        $responseFinal->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);
    }
}
