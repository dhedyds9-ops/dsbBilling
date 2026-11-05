<?php

declare(strict_types=1);

namespace App\Services\CRM;

use App\Enums\JobFunction;
use App\Models\CRM\Installation;
use App\Models\Notification\Notification;
use App\Models\User;
use App\Services\Notifications\WhatsApp\WhatsAppDirectSender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Notifikasi penugasan Work Order Instalasi CRM ke teknisi:
 *  1. Notifikasi in-app (lonceng Portal Teknisi / NotificationCenter).
 *  2. WhatsApp Gateway langsung ke nomor teknisi.
 *
 * Seluruh kegagalan hanya di-log, tidak pernah menggagalkan penyimpanan instalasi.
 */
class InstallationTechnicianNotifier
{
    /**
     * Query standar daftar teknisi yang boleh ditugaskan
     * (job_function TECHNICIAN, bukan reseller/customer/administrator).
     */
    public static function technicianQuery(): Builder
    {
        return User::query()
            ->where('job_function', JobFunction::TECHNICIAN->value)
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['reseller', 'customer', 'administrator', 'superadmin']));
    }

    /**
     * @return array{in_app: bool, whatsapp: bool, whatsapp_error: ?string}
     */
    public function notifyAssigned(Installation $wo, string $event = 'created'): array
    {
        $result = ['in_app' => false, 'whatsapp' => false, 'whatsapp_error' => null];

        $techId = $wo->assigned_to ?: $wo->technician_id;
        if (!$techId) {
            return $result;
        }

        $technician = User::find($techId);
        if (!$technician) {
            return $result;
        }

        $wo->loadMissing(['customer']);
        $customer = $wo->customer;

        $customerName = $customer?->name ?? ($wo->customer_name ?: 'Pelanggan');
        $address = $customer?->address ?? '-';
        $phone = $customer?->phone ?? '-';
        $date = $wo->scheduled_date
            ? (is_string($wo->scheduled_date) ? $wo->scheduled_date : $wo->scheduled_date->translatedFormat('d M Y'))
            : '-';
        $time = $wo->scheduled_time ? substr((string) $wo->scheduled_time, 0, 5) : '09:00';
        $priority = strtoupper((string) ($wo->priority ?? 'medium'));
        $portalUrl = route('technician.my-jobs.index', ['jobType' => 'installation']);

        $heading = $event === 'reassigned' ? 'PENUGASAN ULANG INSTALASI' : 'TUGAS BARU INSTALASI';

        // 1. In-app notification (Portal Teknisi)
        try {
            Notification::create([
                'uuid' => (string) Str::uuid(),
                'recipient_id' => $technician->id,
                'title' => ucwords(strtolower($heading)) . ' #' . $wo->id,
                'message' => "{$customerName} — {$date} {$time} WIB. Alamat: {$address}",
                'type' => 'in_app',
                'status' => 'pending',
                'sent_at' => now(),
                'data' => [
                    'url' => $portalUrl,
                    'icon' => 'construction',
                    'color' => 'indigo',
                    'installation_id' => $wo->id,
                    'event' => $event,
                ],
                'created_by' => Auth::id(),
            ]);
            $result['in_app'] = true;
        } catch (\Throwable $e) {
            Log::warning('[InstallationNotifier] Gagal membuat notifikasi in-app: ' . $e->getMessage(), ['installation_id' => $wo->id]);
        }

        // 2. WhatsApp ke teknisi
        $techPhone = $this->normalizePhone(
            $technician->whatsapp ?: ($technician->phone ?: ($technician->employee?->phone ?? null))
        );

        if (!$techPhone) {
            $result['whatsapp_error'] = 'Nomor WhatsApp teknisi belum diisi pada profil user/pegawai.';
            Log::info('[InstallationNotifier] Teknisi tanpa nomor WhatsApp', ['user_id' => $technician->id]);
            return $result;
        }

        $text = "*[{$heading}]*\n\n"
            . "Halo {$technician->name},\n"
            . "Anda ditugaskan untuk pekerjaan instalasi berikut:\n\n"
            . "*WO:* #{$wo->id}\n"
            . "*Pelanggan:* {$customerName}\n"
            . "*No. HP:* {$phone}\n"
            . "*Alamat:* {$address}\n"
            . "*Jadwal:* {$date} {$time} WIB\n"
            . "*Prioritas:* {$priority}\n"
            . "*Catatan:* " . ($wo->notes ?: '-') . "\n\n"
            . "Buka Portal Teknisi: {$portalUrl}";

        try {
            $send = WhatsAppDirectSender::send($techPhone, $text);
            $result['whatsapp'] = (bool) ($send['success'] ?? false);
            $result['whatsapp_error'] = $send['error'] ?? null;
        } catch (\Throwable $e) {
            $result['whatsapp_error'] = $e->getMessage();
            Log::warning('[InstallationNotifier] Gagal kirim WhatsApp teknisi: ' . $e->getMessage(), ['installation_id' => $wo->id]);
        }

        return $result;
    }

    private function normalizePhone(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }
        $p = preg_replace('/\D/', '', $raw);
        if ($p === '') {
            return null;
        }
        if (str_starts_with($p, '0')) {
            $p = '62' . substr($p, 1);
        } elseif (!str_starts_with($p, '62')) {
            $p = '62' . $p;
        }

        return strlen($p) >= 10 ? $p : null;
    }
}
