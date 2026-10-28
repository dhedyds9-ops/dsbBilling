<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MstoreAttendanceSyncService
{
    /**
     * Kirim data absensi ke aplikasi MStore (https://ms.mstore.id).
     */
    public static function sync(Attendance $attendance): bool
    {
        try {
            $mstoreUrl = env('MSTORE_ATTENDANCE_SYNC_URL', 'https://ms.mstore.id/api/v1/attendance/sync');
            $apiToken = trim((string) (
                env('MSTORE_SYNC_TOKEN') 
                ?: env('DSBILLING_SYNC_TOKEN') 
                ?: config('services.mstore.sync_token') 
                ?: 'mstore-sync-secret-2026'
            ));

            $attendance->loadMissing(['technician.employee']);
            $user = $attendance->technician;
            $employee = $user?->employee;

            $nik = $employee?->nik ?? $user?->identity_number;
            $name = $employee?->name ?? $user?->name;
            $email = $employee?->email ?? $user?->email;
            $phone = $employee?->phone ?? $user?->whatsapp ?? $user?->phone;

            if (! $nik && ! $email && ! $phone && ! $name) {
                Log::warning('Skip syncing attendance to MStore: No user information found', [
                    'attendance_id' => $attendance->id ?? null,
                    'user_id' => $user?->id,
                ]);
                return false;
            }

            // Pemetaan status dsBilling ke mStore:
            $rawStatus = is_object($attendance->status) 
                ? ($attendance->status->value ?? (string) $attendance->status) 
                : (string) $attendance->status;

            $status = match (strtolower(trim($rawStatus))) {
                'late' => 'late',
                'permission' => 'permit',
                'sick' => 'sick',
                'absent' => 'alpha',
                default => 'present',
            };

            $workDate = $attendance->date ? Carbon::parse($attendance->date)->format('Y-m-d') : now()->format('Y-m-d');
            $clockIn = $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->format('Y-m-d H:i:s') : null;
            $clockOut = $attendance->checked_out_at ? Carbon::parse($attendance->checked_out_at)->format('Y-m-d H:i:s') : null;

            $payload = [
                'token' => $apiToken,
                'sync_token' => $apiToken,
                'api_token' => $apiToken,
                'nik' => $nik,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'work_date' => $workDate,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'lat_clock_in' => $attendance->check_in_latitude,
                'lng_clock_in' => $attendance->check_in_longitude,
                'lat_clock_out' => $attendance->check_out_latitude,
                'lng_clock_out' => $attendance->check_out_longitude,
                'status' => $status,
                'notes' => $attendance->notes,
                'source' => 'dsbilling',
            ];

            $response = Http::timeout(5)
                ->withHeaders([
                    'X-DSBILLING-TOKEN' => $apiToken,
                    'X-MSTORE-TOKEN' => $apiToken,
                    'Accept' => 'application/json',
                ])
                ->post($mstoreUrl, $payload);

            if ($response->successful()) {
                Log::info('Attendance synced to MStore successfully', [
                    'nik' => $nik,
                    'name' => $name,
                    'work_date' => $workDate,
                    'status' => $status,
                ]);
                return true;
            }

            Log::warning('Failed syncing attendance to MStore', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Exception syncing attendance to MStore: ' . $e->getMessage());
            return false;
        }
    }
}
