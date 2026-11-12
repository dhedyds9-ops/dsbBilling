<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workforce\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileWorkforceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_login_success(): void
    {
        $user = User::create([
            'name' => 'Teknisi Alpha',
            'email' => 'teknisi.alpha@test.com',
            'username' => 'tekalpha',
            'password' => Hash::make('secret123'),
            'job_function' => 'TECHNICIAN',
            'uuid' => (string) Str::uuid(),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => 'tekalpha',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => 'Teknisi Alpha',
                ],
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertNotNull($response->json('acs_config.url'));

        // Cleanup
        $user->tokens()->delete();
        $user->delete();
    }

    public function test_mobile_login_invalid_password(): void
    {
        $response = $this->postJson('/api/mobile/login', [
            'email' => 'nobody@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_mobile_work_orders_and_lifecycle(): void
    {
        $user = User::create([
            'name' => 'Teknisi Beta',
            'email' => 'teknisi.beta@test.com',
            'username' => 'tekbeta',
            'password' => Hash::make('secret123'),
            'job_function' => 'TECHNICIAN',
            'uuid' => (string) Str::uuid(),
            'is_active' => true,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        \App\Models\Workforce\Attendance::create([
            'technician_id' => $user->id,
            'date' => \Carbon\Carbon::today(),
            'checked_in_at' => \Carbon\Carbon::now()->subHour(),
            'checked_out_at' => null,
            'status' => \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN,
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'type' => 'installation',
            'title' => 'Pasang Baru Pelanggan Budi',
            'description' => 'Tarik dropcore 150m ke ODP-02',
            'status' => 'pending',
            'priority' => 'high',
            'assigned_to' => $user->id,
        ]);

        // 1. Get Work Orders
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/mobile/workforce/work-orders');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertTrue(collect($response->json('data'))->contains('id', $wo->id));

        // 2. Start Work Order
        $startRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/start");

        $startRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('in_progress', $wo->fresh()->status);

        // 3. Add Material Usage
        $matRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/materials", [
                'material_id' => '1',
                'material_name' => 'Dropcore 1 Core 100m',
                'quantity' => 1,
                'serial_number' => 'DC-001',
            ]);

        $matRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('material_usages', [
            'work_order_id' => $wo->id,
            'material_name' => 'Dropcore 1 Core 100m',
        ]);

        // 4. Complete Work Order
        $compRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/mobile/workforce/work-orders/{$wo->id}/complete");

        $compRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('pending_approval', $wo->fresh()->status);

        // 5. Log GPS
        $gpsRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/mobile/workforce/gps/log', [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]);

        $gpsRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // Cleanup
        $wo->materialUsages()->delete();
        $wo->delete();
        $user->tokens()->delete();
        $user->delete();
    }
}
