<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class TelegramService
{
    protected ?string $botToken = null;
    protected array $chatIds = [];

    public function __construct()
    {
        $botConfig = Setting::getValue('telegram.bot', []);
        $this->botToken = !empty($botConfig['bot_token']) ? $botConfig['bot_token'] : Setting::getValue('telegram.bot_token', null);
        
        $savedChats = Setting::getValue('telegram.chatIds', []);
        if (!empty($savedChats)) {
            $this->chatIds = $savedChats;
        } else {
            $defaultChat = Setting::getValue('telegram.default_chat_id', '');
            if (!empty($defaultChat)) {
                $this->chatIds = [
                    ['id' => $defaultChat, 'name' => 'Default Group', 'enabled' => true, 'events' => 'alarm,ticket,installation']
                ];
            }
        }
    }

    public function isEnabled(): bool
    {
        return !empty($this->botToken);
    }

    public function sendMessage(string $message, ?string $chatId = null, string $parseMode = 'HTML'): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$chatId) {
            Log::warning('Telegram send failed: chatId is required');
            return false;
        }

        return $this->sendDirectOrQueue($message, $chatId, $parseMode);
    }

    public function sendDirectOrQueue(string $message, string $chatId, string $parseMode = 'HTML'): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $ok = $this->sendDirectMessage($message, $chatId, $parseMode);
            if ($ok) return true;
        } catch (\Throwable $e) {
            Log::warning('[TelegramService] Direct send failed, fallback queue: ' . $e->getMessage());
        }

        try {
            \App\Jobs\Notifications\TelegramNotificationJob::dispatch($message, $chatId, $parseMode);
            return true;
        } catch (\Throwable $qe) {
            Log::error('[TelegramService] Queue dispatch failed: ' . $qe->getMessage());
            return false;
        }
    }

    /**
     * Executes the actual HTTP POST to Telegram API directly.
     */
    public function sendDirectMessage(string $message, string $chatId, string $parseMode = 'HTML'): bool
    {
        if (!$this->isEnabled()) return false;

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            $response = Http::timeout(10)->withOptions(['force_ip_resolve' => 'v4'])->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 60);
                $retryAfter = max(10, min($retryAfter, 3600)); 
                throw new \App\Exceptions\RateLimitException("Telegram API Rate Limited", $retryAfter);
            }

            if (!$response->successful()) {
                Log::warning('Telegram send failed', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\App\Exceptions\RateLimitException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Telegram error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast message to all enabled chats matching the event type
     */
    public function broadcastEvent(string $eventType, string $message, string $parseMode = 'HTML'): void
    {
        if (!$this->isEnabled()) return;

        foreach ($this->chatIds as $chat) {
            if (empty($chat['enabled'])) continue;

            $events = strtolower((string)($chat['events'] ?? ''));
            // Cocokkan eventType atau jika chat mengaktifkan semua event
            if (
                $events === '' 
                || str_contains($events, 'all') 
                || str_contains($events, strtolower($eventType)) 
                || ($eventType === 'installation' && str_contains($events, 'ticket'))
                || ($eventType === 'attendance' && (str_contains($events, 'absen') || str_contains($events, 'workforce') || count($this->chatIds) === 1))
            ) {
                $this->sendDirectOrQueue($message, (string)$chat['id'], $parseMode);
            }
        }
    }

    /**
     * Notifikasi Tiket Support ke Bot Telegram
     */
    public function sendTicketNotification(array $data, string $action = 'created'): void
    {
        $id    = htmlspecialchars((string)($data['ticket_id'] ?? ($data['id'] ?? '-')));
        $code  = htmlspecialchars((string)($data['ticket_code'] ?? ($id ? "TKT-{$id}" : 'TKT-BARU')));
        $title = htmlspecialchars((string)($data['title'] ?? '-'));
        $cust  = htmlspecialchars((string)($data['customer_name'] ?? 'Pelanggan'));
        $catRaw = strtolower((string)($data['category'] ?? 'internet'));
        $tipe  = match($catRaw) {
            'internet' => 'PERBAIKAN',
            'installation' => 'PEMASANGAN',
            'billing' => 'BILLING',
            default => strtoupper((string)($data['category'] ?? 'PERBAIKAN'))
        };
        $prioRaw = strtolower((string)($data['priority'] ?? 'medium'));
        $prio = match($prioRaw) {
            'low' => '🟢 RENDAH',
            'medium' => '🟡 SEDANG',
            'high' => '🟠 TINGGI',
            'critical' => '🔴 KRITIS',
            default => '🟢 ' . strtoupper($prioRaw),
        };
        $tech  = htmlspecialchars((string)($data['technician_name'] ?? 'Belum Ditugaskan'));
        $addr  = htmlspecialchars((string)($data['customer_address'] ?? ($data['address'] ?? '-')));
        if (trim($addr) === '') $addr = '-';
        $note  = !empty($data['note']) ? htmlspecialchars((string)$data['note']) : '';
        $url   = $this->ticketUrl($id);

        if ($action === 'created') {
            $msg = "🎫 <b>TIKET BARU: {$code}</b>\n\n"
                . "<b>Yang ditugaskan :</b>\n"
                . "👷 <b>Teknisi:</b> {$tech}\n"
                . "📌 <b>Tipe:</b> {$tipe}\n"
                . "👤 <b>Pelanggan:</b> {$cust}\n"
                . "📝 <b>Subjek:</b> {$title}\n"
                . "⚡ <b>Prioritas:</b> {$prio}\n"
                . "📍 <b>Alamat:</b> {$addr}\n\n"
                . "🔗 <b>Detail:</b> {$url}\n\n"
                . "🚀 <i>Sistem</i>";
        } elseif ($action === 'updated') {
            $status = strtoupper(htmlspecialchars((string)($data['status'] ?? '-')));
            $msg = "✏️ <b>TIKET DIPERBARUI: {$code}</b>\n\n"
                . "<b>Yang ditugaskan :</b>\n"
                . "👷 <b>Teknisi:</b> {$tech}\n"
                . "📌 <b>Tipe:</b> {$tipe}\n"
                . "📊 <b>Status:</b> <b>{$status}</b>\n"
                . "👤 <b>Pelanggan:</b> {$cust}\n"
                . "📝 <b>Subjek:</b> {$title}\n"
                . "⚡ <b>Prioritas:</b> {$prio}\n"
                . "📍 <b>Alamat:</b> {$addr}\n"
                . ($note ? "💬 <b>Catatan:</b> {$note}\n\n" : "\n")
                . "🔗 <b>Detail:</b> {$url}\n\n"
                . "🚀 <i>Sistem</i>";
        } elseif ($action === 'resolved') {
            $msg = "✅ <b>TIKET SELESAI: {$code}</b>\n\n"
                . "<b>Yang ditugaskan :</b>\n"
                . "👷 <b>Teknisi:</b> {$tech}\n"
                . "👤 <b>Pelanggan:</b> {$cust}\n"
                . "📝 <b>Subjek:</b> {$title}\n"
                . ($note ? "💬 <b>Penyelesaian:</b> {$note}\n\n" : "\n")
                . "🔗 <b>Detail:</b> {$url}\n\n"
                . "🚀 <i>Sistem</i>";
        } else {
            $msg = "📋 <b>UPDATE TIKET #{$id}</b>\n\n<b>Judul:</b> {$title}\n<b>Pelanggan:</b> {$cust}";
        }

        $this->broadcastEvent('ticket', $msg, 'HTML');
    }

    /**
     * Notifikasi Work Order Instalasi ke Bot Telegram
     */
    public function sendInstallationNotification(array $data, string $action = 'created'): void
    {
        $no    = htmlspecialchars((string)($data['wo_id'] ?? ($data['id'] ?? '-')));
        $cust  = htmlspecialchars((string)($data['customer_name'] ?? 'Pelanggan'));
        $phone = htmlspecialchars((string)($data['customer_phone'] ?? '-'));
        $addr  = htmlspecialchars((string)($data['customer_address'] ?? '-'));
        $date  = htmlspecialchars((string)($data['scheduled_date'] ?? '-'));
        $time  = htmlspecialchars((string)($data['scheduled_time'] ?? '09:00'));
        $tech  = htmlspecialchars((string)($data['technician_name'] ?? 'Belum Ditugaskan'));
        $notes = !empty($data['notes']) ? htmlspecialchars((string)$data['notes']) : '';
        $prio  = strtoupper(htmlspecialchars((string)($data['priority'] ?? 'medium')));

        $prioEmoji = match(strtolower((string)($data['priority'] ?? 'medium'))) {
            'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', default => '🟢'
        };

        $msg = "📦 <b>WORK ORDER INSTALASI BARU</b>\n\n"
            . "<b>No. WO    :</b> #{$no}\n"
            . "<b>Pelanggan :</b> {$cust}\n"
            . "<b>No. HP    :</b> {$phone}\n"
            . "<b>Alamat    :</b> {$addr}\n"
            . "<b>Jadwal    :</b> 🗓️ {$date} pk {$time}\n"
            . "<b>Prioritas :</b> {$prioEmoji} <b>{$prio}</b>\n"
            . "<b>Teknisi   :</b> 👨‍🔧 <b>{$tech}</b>\n"
            . ($notes ? "\n<b>Catatan:</b>\n{$notes}\n" : "\n")
            . "<i>Mohon tim teknisi mempersiapkan perangkat & material.</i>";

        $this->broadcastEvent('installation', $msg, 'HTML');
    }

    /**
     * Notifikasi Absensi Staf / Teknisi ke Bot Telegram
     */
    public function sendAttendanceNotification(\App\Models\Workforce\Attendance $attendance, string $action = 'check_in'): void
    {
        $attendance->loadMissing(['technician.employee', 'technician.branch']);
        $user = $attendance->technician;
        if (!$user) return;

        $name = htmlspecialchars((string)$user->name);
        $role = htmlspecialchars(ucfirst((string)($user->job_title ?: ($user->job_function ?: ($user->roles->first()?->name ?? 'Staff')))));
        $branchName = htmlspecialchars((string)($user->branch?->name ?? 'Kantor Utama / Pusat'));

        $rawStatus = is_object($attendance->status) 
            ? ($attendance->status->value ?? (string) $attendance->status) 
            : (string) $attendance->status;

        $statusLabel = match (strtolower(trim($rawStatus))) {
            'late' => '🟡 TERLAMBAT (Late)',
            'checked_out' => '🏁 CHECK-OUT (Selesai Shift)',
            'permission' => '📋 IZIN (Permit)',
            'sick' => '🏥 SAKIT (Sick)',
            'absent' => '❌ ALPHA (Absent)',
            default => '🟢 TEPAT WAKTU (Hadir)',
        };

        $dateFormatted = $attendance->date ? \Carbon\Carbon::parse($attendance->date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y');
        $notes = !empty($attendance->notes) ? htmlspecialchars((string)$attendance->notes) : '';

        if ($action === 'check_out') {
            $timeOut = $attendance->checked_out_at ? \Carbon\Carbon::parse($attendance->checked_out_at)->format('H:i:s') . ' WIB' : now()->format('H:i:s') . ' WIB';
            $timeIn = $attendance->checked_in_at ? \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') . ' WIB' : '-';
            
            $gpsLink = '';
            if ($attendance->check_out_latitude && $attendance->check_out_longitude) {
                $gpsLink = "📍 <a href=\"https://www.google.com/maps?q={$attendance->check_out_latitude},{$attendance->check_out_longitude}\">Lihat Lokasi GPS Pulang</a>\n";
            }

            $msg = "🚪 <b>ABSENSI KELUAR (CHECK-OUT)</b>\n\n"
                . "👤 <b>Nama:</b> {$name}\n"
                . "💼 <b>Jabatan:</b> {$role}\n"
                . "🏢 <b>Cabang:</b> {$branchName}\n"
                . "🗓️ <b>Tanggal:</b> {$dateFormatted}\n"
                . "⏰ <b>Jam Masuk:</b> {$timeIn}\n"
                . "⏰ <b>Jam Pulang:</b> {$timeOut}\n"
                . "📊 <b>Status:</b> 🏁 CHECKED-OUT\n"
                . ($notes ? "💬 <b>Catatan:</b> {$notes}\n" : "")
                . ($gpsLink ? "\n{$gpsLink}" : "\n")
                . "🚀 <i>dsBilling Workforce Bot</i>";
        } else {
            $timeIn = $attendance->checked_in_at ? \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') . ' WIB' : now()->format('H:i:s') . ' WIB';

            $gpsLink = '';
            if ($attendance->check_in_latitude && $attendance->check_in_longitude) {
                $gpsLink = "📍 <a href=\"https://www.google.com/maps?q={$attendance->check_in_latitude},{$attendance->check_in_longitude}\">Lihat Lokasi GPS Masuk</a>\n";
            }

            $msg = "📍 <b>ABSENSI MASUK (CHECK-IN)</b>\n\n"
                . "👤 <b>Nama:</b> {$name}\n"
                . "💼 <b>Jabatan:</b> {$role}\n"
                . "🏢 <b>Cabang:</b> {$branchName}\n"
                . "🗓️ <b>Tanggal:</b> {$dateFormatted}\n"
                . "⏰ <b>Waktu Absen:</b> {$timeIn}\n"
                . "📊 <b>Status:</b> {$statusLabel}\n"
                . ($notes ? "💬 <b>Catatan:</b> {$notes}\n" : "")
                . ($gpsLink ? "\n{$gpsLink}" : "\n")
                . "🚀 <i>dsBilling Workforce Bot</i>";
        }

        $this->broadcastEvent('attendance', $msg, 'HTML');
    }

    public function sendAlarmNotification(string $deviceSN, string $status, string $details): void
    {
        $icon = $status === 'OFFLINE' ? '🔴' : ($status === 'WARNING' ? '⚠️' : 'ℹ️');
        
        $message = "{$icon} *ACS ALARM* {$icon}\n\n";
        $message .= "SN: `{$deviceSN}`\n";
        $message .= "Status: *{$status}*\n";
        $message .= "Detail: {$details}\n";
        $message .= "Waktu: " . now()->format('Y-m-d H:i:s');

        foreach ($this->chatIds as $chat) {
            if (!empty($chat['enabled']) && str_contains($chat['events'] ?? '', 'alarm')) {
                $this->sendMessage($message, $chat['id'], 'Markdown');
            }
        }
    }

    public function sendOnuRedamanAlert(array $data, string $chatId = null): void
    {
        $message = "dsbilling Resume: Redaman TINGGI\n";
        $message .= "---------------------------\n";
        $message .= "Nomor ID: " . ($data['customer_id'] ?? '-') . "\n";
        $message .= "ONU Status: ONLINE\n";
        $message .= "CLID: " . ($data['clid'] ?? '-') . "\n";
        $message .= "Port: " . ($data['port'] ?? '-') . "\n";
        $message .= "ONT S/N: " . ($data['serial_number'] ?? '-') . "\n";
        $message .= "OLT Rx dBm: " . ($data['olt_rx'] ?? '-') . "\n";
        $message .= "ONU Rx dBm: " . ($data['onu_rx'] ?? '-') . "\n";
        $message .= "ONU Type: " . ($data['onu_type'] ?? '-') . "\n";
        $message .= "IP Global: " . ($data['ip_address'] ?? '-') . "\n";
        $message .= "Jarak: " . ($data['distance'] ?? '-') . "\n";
        $message .= "----------------------\n";
        $message .= "Paket: " . ($data['package'] ?? '-') . "\n";
        $message .= "Profile: " . ($data['profile_up'] ?? '-') . "\n";
        $message .= "Profile: " . ($data['profile_down'] ?? '-') . "\n";
        $message .= "----------------------\n";
        $message .= "Mulai: " . ($data['start_time'] ?? '-') . "\n";
        $message .= "Selesai: " . ($data['end_time'] ?? '') . "\n";
        $message .= "Upload: " . ($data['upload'] ?? '-') . "\n";
        $message .= "Download: " . ($data['download'] ?? '-') . "\n";
        $message .= "Status Koneksi: " . ($data['connection_status'] ?? 'Online') . "\n";

        if ($chatId) {
            $this->sendMessage($message, $chatId, 'HTML');
            return;
        }

        foreach ($this->chatIds as $chat) {
            if (!empty($chat['enabled'])) {
                $this->sendMessage($message, $chat['id'], 'HTML');
            }
        }
    }

    private function ticketUrl(mixed $id): string
    {
        $base = 'https://mstore.mstore.id';
        try {
            if (request()) {
                $host = request()->getHost();
                if (!empty($host) && !in_array($host, ['localhost', '127.0.0.1'])) {
                    $scheme = request()->getScheme();
                    $base = "{$scheme}://{$host}";
                }
            }
        } catch (\Throwable) {}

        return $id ? "{$base}/tickets/{$id}" : "{$base}/support/ticket";
    }
}
