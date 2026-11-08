<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
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
     * Check in for daily shift.
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

        $now = Carbon::now();
        $isLate = $now->format('H:i') > '08:30';
        $status = $isLate ? AttendanceStatus::LATE : AttendanceStatus::CHECKED_IN;

        $notePrefix = !empty($validated['is_field_assignment']) ? '[Tugas Lapangan] ' : '';
        $notes = $notePrefix . ($validated['notes'] ?? 'Absen melalui dsBilling Staff Mobile');

        $attendance = Attendance::create([
            'technician_id' => $user->id,
            'date' => $today,
            'status' => $status,
            'check_in_latitude' => $validated['latitude'],
            'check_in_longitude' => $validated['longitude'],
            'checked_in_at' => $now,
            'notes' => $notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil dicatat. Selamat bertugas!',
            'data' => [
                'id' => $attendance->id,
                'status' => $status->value,
                'checked_in_at' => $now->toIso8601String(),
                'checked_in_time' => $now->format('H:i'),
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'is_late' => $isLate,
            ],
        ]);
    }

    /**
     * Check out from daily shift.
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
}
