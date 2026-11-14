<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workforce\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Src\Domain\Workforce\Enums\AttendanceStatus;
use Tests\TestCase;

class SyncAttendanceToMstoreCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_command_sends_attendance_to_mstore(): void
    {
        Http::fake([
            'https://ms.mstore.id/*' => Http::response(['success' => true, 'message' => 'Synced'], 200),
        ]);

        $user = User::factory()->create([
            'name' => 'Teknisi Budi',
            'email' => 'budi@mstore.id',
            'whatsapp' => '081234567890',
            'job_function' => 'TECHNICIAN',
        ]);

        $attendance = Attendance::create([
            'technician_id' => $user->id,
            'date' => now()->toDateString(),
            'checked_in_at' => now(),
            'status' => AttendanceStatus::CHECKED_IN->value,
            'notes' => 'Hadir tepat waktu',
        ]);

        $this->artisan('attendance:sync-mstore', ['--force' => true])
            ->expectsOutputToContain('Hasil Sinkronisasi ke MStore:')
            ->expectsOutputToContain('Berhasil terkirim: 1')
            ->assertExitCode(0);

        Http::assertSent(function ($request) use ($user) {
            return str_contains($request->url(), 'ms.mstore.id')
                && $request['email'] === $user->email
                && $request['name'] === $user->name;
        });
    }

    public function test_sync_command_supports_custom_date(): void
    {
        Http::fake([
            'https://ms.mstore.id/*' => Http::response(['success' => true, 'message' => 'Synced'], 200),
        ]);

        $user = User::factory()->create([
            'name' => 'Teknisi Andi',
            'email' => 'andi@mstore.id',
            'job_function' => 'TECHNICIAN',
        ]);

        $pastDate = now()->subDays(2)->toDateString();
        Attendance::create([
            'technician_id' => $user->id,
            'date' => $pastDate,
            'checked_in_at' => now()->subDays(2),
            'status' => AttendanceStatus::CHECKED_IN->value,
        ]);

        $this->artisan('attendance:sync-mstore', [
            '--date' => $pastDate,
            '--force' => true,
        ])
            ->expectsOutputToContain('Berhasil terkirim: 1')
            ->assertExitCode(0);
    }
}
