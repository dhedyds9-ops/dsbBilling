<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MstoreAttendanceSyncService
{
    /**
     * Tentukan apakah sinkronisasi absensi ke MStore (ms.mstore.id) aktif untuk request/tenant saat ini.
     * Subdomain winets.mstore.id adalah subdomain mandiri dan TIDAK dikaitkan ke ms.mstore.id.
     * Hanya mstore.mstore.id yang dikaitkan ke ms.mstore.id.
     */
    public static function shouldSync(?Attendance $attendance = null): bool
    {
        // 0. Cegah sinkronisasi saat testing / unit test dijalankan
        if (app()->environment('testing') || app()->runningUnitTests()) {
            return false;
        }

        // 1. Cek konfigurasi jika secara eksplisit diset
        if (config('services.mstore.sync_enabled') !== null) {
            return filter_var(config('services.mstore.sync_enabled'), FILTER_VALIDATE_BOOLEAN);
        }
        if (env('MSTORE_SYNC_ENABLED') !== null) {
            return filter_var(env('MSTORE_SYNC_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        }

        // Jangan sync ke production otomatis jika berjalan di environment local kecuali eksplisit diizinkan
        if (app()->environment('local') && ! env('MSTORE_SYNC_ENABLED')) {
            return false;
        }

        // 2. Kumpulkan seluruh kandidat host / domain dari request dan environment
        $candidates = [];
        try {
            if (request()) {
                if ($h = request()->header('X-Forwarded-Host')) $candidates[] = $h;
                if ($h = request()->header('Host')) $candidates[] = $h;
                if ($h = request()->getHost()) $candidates[] = $h;
            }
        } catch (\Throwable) {}

        if (! empty($_SERVER['HTTP_HOST'])) {
            $candidates[] = $_SERVER['HTTP_HOST'];
        }
        if (! empty($_SERVER['SERVER_NAME'])) {
            $candidates[] = $_SERVER['SERVER_NAME'];
        }
        if (env('APP_URL')) {
            $parsed = parse_url(env('APP_URL'), PHP_URL_HOST);
            if ($parsed) $candidates[] = $parsed;
        }

        foreach ($candidates as $candidate) {
            if (str_contains(strtolower((string) $candidate), 'winets')) {
                return false;
            }
        }

        // 3. Deteksi Tenant Name dari Server / Request
        $tenants = [];
        if (! empty($_SERVER['TENANT_NAME'])) {
            $tenants[] = $_SERVER['TENANT_NAME'];
        }
        try {
            if (request()) {
                if ($t = request()->header('X-Tenant')) $tenants[] = $t;
                if ($t = request()->input('tenant')) $tenants[] = $t;
            }
        } catch (\Throwable) {}

        foreach ($tenants as $tenant) {
            if (strtolower(trim((string) $tenant)) === 'winets' || str_contains(strtolower((string) $tenant), 'winets')) {
                return false;
            }
        }

        // 4. Cek database connection active tenant
        try {
            $defaultConn = config('database.default');
            $activeDb = config("database.connections.{$defaultConn}.database", '');
            if (is_string($activeDb) && str_contains(strtolower($activeDb), 'winets')) {
                return false;
            }
        } catch (\Throwable) {}

        // 5. Cek nama perusahaan jika diset 'winets'
        try {
            $companyName = strtolower((string) \App\Models\Setting::getValue('company.name', ''));
            $isMstoreHost = false;
            foreach ($candidates as $candidate) {
                if (str_contains(strtolower((string) $candidate), 'mstore.mstore.id')) {
                    $isMstoreHost = true;
                    break;
                }
            }
            if (str_contains($companyName, 'winets') && ! $isMstoreHost) {
                return false;
            }
        } catch (\Throwable) {}

        return true;
    }

    /**
     * Kirim data absensi ke aplikasi MStore (https://ms.mstore.id).
     */
    public static function sync(Attendance $attendance, bool $force = false): bool
    {
        try {
            // Bypass sinkronisasi jika tenant/subdomain adalah winets (hanya mstore.mstore.id yang dikaitkan)
            if (! $force && ! static::shouldSync($attendance)) {
                Log::info('Skip syncing attendance to MStore: winets.mstore.id is configured as an independent tenant.', [
                    'attendance_id' => $attendance->id ?? null,
                    'user_id' => $attendance->technician_id ?? null,
                ]);
                return false;
            }

            $mstoreUrl = env('MSTORE_ATTENDANCE_SYNC_URL', config('services.mstore.sync_url', 'https://ms.mstore.id/api/v1/attendance/sync'));
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
