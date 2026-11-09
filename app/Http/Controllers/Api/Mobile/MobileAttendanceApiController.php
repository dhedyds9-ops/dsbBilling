<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Master\Branch;
use App\Models\Workforce\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Domain\Workforce\Enums\AttendanceStatus;

class MobileAttendanceApiController extends Controller
{
    /**
     * Get today's attendance record for the authenticated technician.
     *
     * GET /api/mobile/attendance/today
     */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Attendance::where('technician_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $hasCheckedIn = $attendance && !empty($attendance->checked_in_at);
        $hasCheckedOut = $attendance && !empty($attendance->checked_out_at);

        return response()->json([
            'success' => true,
            'data' => [
                'has_checked_in' => $hasCheckedIn,
                'has_checked_out' => $hasCheckedOut,
                'is_active_shift' => $hasCheckedIn && !$hasCheckedOut,
                'date' => $today->toDateString(),
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'status' => $attendance->status ? $attendance->status->value : 'absent',
                    'checked_in_at' => $attendance->checked_in_at ? $attendance->checked_in_at->toIso8601String() : null,
                    'checked_in_time' => $attendance->checked_in_at ? $attendance->checked_in_at->format('H:i') : null,
                    'checked_out_at' => $attendance->checked_out_at ? $attendance->checked_out_at->toIso8601String() : null,
                    'checked_out_time' => $attendance->checked_out_at ? $attendance->checked_out_at->format('H:i') : null,
                    'check_in_latitude' => $attendance->check_in_latitude,
                    'check_in_longitude' => $attendance->check_in_longitude,
                    'check_out_latitude' => $attendance->check_out_latitude,
                    'check_out_longitude' => $attendance->check_out_longitude,
                    'notes' => $attendance->notes,
                ] : null,
            ],
        ]);
    }

    /**
     * Get attendance history for the authenticated user (last 30 records).
     *
     * GET /api/mobile/attendance/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $attendances = Attendance::where('technician_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'date' => $att->date ? (is_string($att->date) ? $att->date : $att->date->toDateString()) : null,
                    'status' => $att->status ? ($att->status instanceof AttendanceStatus ? $att->status->value : (string) $att->status) : 'absent',
                    'checked_in_at' => $att->checked_in_at ? $att->checked_in_at->toIso8601String() : null,
                    'checked_in_time' => $att->checked_in_at ? $att->checked_in_at->format('H:i') : null,
                    'checked_out_at' => $att->checked_out_at ? $att->checked_out_at->toIso8601String() : null,
                    'checked_out_time' => $att->checked_out_at ? $att->checked_out_at->format('H:i') : null,
                    'notes' => $att->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    /**
     * Check in for daily shift with multi-branch geofencing & field assignment override.
     *
     * POST /api/mobile/attendance/check-in
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string|max:500',
            'is_field_assignment' => 'nullable|boolean',
        ]);

        $today = Carbon::today();

        $existing = Attendance::where('technician_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && !empty($existing->checked_in_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini.',
                'data' => $existing,
            ], 422);
        }

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $isFieldAssignment = !empty($validated['is_field_assignment']);
        $isAdmin = $user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin']);

        // Multi-branch Smart Geofencing (Parity with Web Portal)
        $branches = Branch::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $homeBranch = $user->branch;
        if ($homeBranch && !empty($homeBranch->latitude) && !empty($homeBranch->longitude)) {
            if (!$branches->contains('id', $homeBranch->id)) {
                $branches->push($homeBranch);
            }
        }

        $nearestBranch = null;
        $minDistance = null;
        $maxAllowedRadius = 150; // Toleransi radius 150 meter dari titik kantor cabang

        if ($lat != 0.0 || $lng != 0.0) {
            foreach ($branches as $br) {
                $dist = $this->calculateDistanceInMeters($lat, $lng, (float) $br->latitude, (float) $br->longitude);
                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                    $nearestBranch = $br;
                }
            }
        }

        $matchedBranch = ($nearestBranch && $minDistance !== null && $minDistance <= $maxAllowedRadius) ? $nearestBranch : null;

        // Jika berada di luar radius kantor dan bukan admin/supervisor:
        if (!$isAdmin && !$matchedBranch) {
            if (!$isFieldAssignment) {
                $nearestText = $nearestBranch
                    ? " Cabang terdekat: {$nearestBranch->name} (jarak: {$minDistance}m, batas toleransi: {$maxAllowedRadius}m)."
                    : " Belum ada titik koordinat cabang terdaftar.";
                return response()->json([
                    'success' => false,
                    'message' => "Gagal Check-In: Anda berada di luar radius kantor perusahaan.{$nearestText} Jika ditugaskan langsung ke lapangan/pelanggan, centang 'Tugas Luar / Lapangan' dan cantumkan keterangannya.",
                ], 422);
            }
        }

        $now = Carbon::now();
        $isLate = $now->format('H:i') > '09:00';
        $status = $isLate ? AttendanceStatus::LATE : AttendanceStatus::CHECKED_IN;

        $notes = trim($validated['notes'] ?? '');
        if ($matchedBranch) {
            $isCross = $homeBranch && $matchedBranch->id !== $homeBranch->id;
            $locationNote = $isCross
                ? "Lokasi: Lintas Cabang {$matchedBranch->name} (Home: {$homeBranch->name}) · {$minDistance}m"
                : "Lokasi: {$matchedBranch->name} · {$minDistance}m";
            $notes = ($notes ? $notes . ' | ' : '') . $locationNote;
        } elseif ($isFieldAssignment) {
            $notes = ($notes ? $notes . ' | ' : '') . "Mode: Penugasan Lapangan / Tugas Luar";
        }

        $attendance = Attendance::create([
            'technician_id' => $user->id,
            'date' => $today,
            'status' => $status,
            'check_in_latitude' => $lat,
            'check_in_longitude' => $lng,
            'checked_in_at' => $now,
            'notes' => $notes ?: 'Absen melalui dsBilling Staff Mobile',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil dicatat. Selamat bertugas!',
            'data' => [
                'id' => $attendance->id,
                'status' => $status->value,
                'checked_in_at' => $now->toIso8601String(),
                'checked_in_time' => $now->format('H:i'),
                'latitude' => $lat,
                'longitude' => $lng,
                'is_late' => $isLate,
                'branch' => $matchedBranch ? $matchedBranch->name : null,
            ],
        ]);
    }

    /**
     * Check out from daily shift with accidental prevention time lock.
     *
     * POST /api/mobile/attendance/check-out
     */
    public function checkOut(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string|max:500',
        ]);

        $today = Carbon::today();

        $attendance = Attendance::where('technician_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || empty($attendance->checked_in_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absen masuk hari ini.',
            ], 422);
        }

        if (!empty($attendance->checked_out_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen pulang hari ini.',
                'data' => $attendance,
            ], 422);
        }

        // TIME-LOCK SAFEGUARD: Minimal 15 menit berdinas untuk mencegah salah pencet setelah check-in
        $minutesWorked = $attendance->checked_in_at ? Carbon::parse($attendance->checked_in_at)->diffInMinutes(Carbon::now()) : 0;
        $isAdmin = $user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin']);

        if (!app()->environment('testing') && !$isAdmin && $minutesWorked < 15) {
            $remaining = 15 - $minutesWorked;
            return response()->json([
                'success' => false,
                'message' => "Pencegahan Salah Pencet: Anda baru saja Check-In ({$minutesWorked} menit lalu). Tombol Check-Out baru dapat digunakan minimal 15 menit setelah Check-In (tersisa {$remaining} menit lagi).",
            ], 422);
        }

        $now = Carbon::now();
        $checkoutNotes = $attendance->notes ? ($attendance->notes . "\n[Pulang]: " . ($validated['notes'] ?? '-')) : $validated['notes'];

        $attendance->update([
            'status' => AttendanceStatus::CHECKED_OUT,
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'checked_out_at' => $now,
            'notes' => $checkoutNotes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil dicatat. Terima kasih atas dedikasi Anda!',
            'data' => [
                'id' => $attendance->id,
                'status' => AttendanceStatus::CHECKED_OUT->value,
                'checked_out_at' => $now->toIso8601String(),
                'checked_out_time' => $now->format('H:i'),
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
        ]);
    }

    /**
     * Hitung jarak lurus (Haversine distance) antara 2 titik koordinat dalam meter.
     */
    private function calculateDistanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int) round($earthRadius * $c);
    }
}
