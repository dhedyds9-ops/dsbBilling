<?php

namespace App\Services\Telegram;

use App\Models\Workforce\Attendance;
use Illuminate\Support\Facades\Log;

class TelegramAttendanceNotifier
{
    public static function notify(Attendance $attendance): void
    {
        // Hindari mengirim pesan notifikasi ke Telegram saat testing
        if (app()->environment('testing') || app()->runningUnitTests()) {
            return;
        }

        try {
            $telegram = app(TelegramService::class);
            if (!$telegram->isEnabled()) {
                return;
            }

            // Tentukan tipe aksi: check_out atau check_in
            $isCheckOut = !empty($attendance->checked_out_at);

            if ($attendance->wasRecentlyCreated) {
                $action = $isCheckOut ? 'check_out' : 'check_in';
            } elseif ($attendance->wasChanged('checked_out_at')) {
                $action = 'check_out';
            } elseif ($attendance->wasChanged('checked_in_at')) {
                $action = 'check_in';
            } else {
                return;
            }

            $telegram->sendAttendanceNotification($attendance, $action);
        } catch (\Throwable $e) {
            Log::warning('[TelegramAttendanceNotifier] Gagal mengirim notifikasi absensi ke Telegram: ' . $e->getMessage());
        }
    }
}
