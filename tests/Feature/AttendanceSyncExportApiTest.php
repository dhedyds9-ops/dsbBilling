<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workforce\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Tests\TestCase;

class AttendanceSyncExportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pull_api_rejects_unauthorized_token(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])
            ->getJson('/api/v1/attendance/pull?token=wrong-token');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized: Token autentikasi sinkronisasi absensi tidak valid.',
            ]);
    }

    public function test_pull_api_rejects_winets_tenant(): void
    {
        $response = $this->withHeaders([
            'X-DSBILLING-TOKEN' => 'mstore-sync-secret-2026',
            'X-Forwarded-Host' => 'winets.mstore.id',
        ])->getJson('/api/v1/attendance/pull');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_pull_api_returns_attendances_for_date(): void
    {
        $user = User::factory()->create([
            'name' => 'Teknisi Doni',
            'email' => 'doni@mstore.id',
            'whatsapp' => '081234567899',
            'job_function' => 'TECHNICIAN',
        ]);

        $targetDate = '2026-11-14';

        Attendance::create([
            'technician_id' => $user->id,
            'date' => $targetDate,
            'checked_in_at' => $targetDate . ' 08:00:00',
            'checked_out_at' => $targetDate . ' 17:00:00',
            'status' => AttendanceStatus::CHECKED_IN->value,
            'notes' => 'Hadir tepat waktu di kantor',
        ]);

        $response = $this->withHeaders([
            'X-DSBILLING-TOKEN' => 'mstore-sync-secret-2026',
            'Host' => 'mstore.mstore.id',
        ])->getJson('/api/v1/attendance/pull?date=' . $targetDate);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
            ])
            ->assertJsonPath('records.0.name', 'Teknisi Doni')
            ->assertJsonPath('records.0.email', 'doni@mstore.id')
            ->assertJsonPath('records.0.work_date', $targetDate);
    }
}
