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

        $techs = $wo->assignedTechnicians();
        if ($techs->isEmpty()) {
            $techId = $wo->assigned_to ?: $wo->technician_id;
            if ($techId) {
                $user = User::find($techId);
                if ($user) {
                    $techs = collect([$user]);
                }
            }
        }

        if ($techs->isEmpty()) {
            return $result;
        }

        $wo->loadMissing(['customer', 'survey.prospect']);
        $customer = $wo->customer;
        $survey = $wo->survey;

        $customerName = $customer?->name ?? ($wo->customer_name ?: 'Pelanggan');
        $address = $customer?->address ?: ($survey?->address ?: ($survey?->prospect?->address ?: '-'));
        $lat = $customer?->latitude ?: ($survey?->latitude ?: ($survey?->prospect?->latitude ?: null));
        $lng = $customer?->longitude ?: ($survey?->longitude ?: ($survey?->prospect?->longitude ?: null));
        $phone = $customer?->phone ?: ($survey?->prospect?->phone ?: '-');
        $date = $wo->scheduled_date
            ? (is_string($wo->scheduled_date) ? $wo->scheduled_date : $wo->scheduled_date->translatedFormat('d M Y'))
            : '-';
        $time = $wo->scheduled_time ? substr((string) $wo->scheduled_time, 0, 5) : '09:00';
        $priority = strtoupper((string) ($wo->priority ?? 'medium'));
        $portalUrl = route('technician.my-jobs.index', ['jobType' => 'installation']);

        $heading = $event === 'reassigned' ? 'PENUGASAN ULANG INSTALASI' : 'TUGAS BARU INSTALASI';

        $latNum = is_numeric($lat) ? (float) $lat : null;
        $lngNum = is_numeric($lng) ? (float) $lng : null;
        $mapsLine = ($latNum !== null && $lngNum !== null && ($latNum != 0.0 || $lngNum != 0.0))
            ? "\n*Lokasi GPS:* https://www.google.com/maps/search/?api=1&query={$latNum},{$lngNum}"
            : "";

        $allNames = $techs->pluck('name')->join(' & ');
        $teamLine = $techs->count() > 1 ? "\n*Tim Teknisi:* {$allNames}" : "";

        $inAppSuccess = false;
        $waSuccess = false;
        $waErrors = [];

        foreach ($techs as $technician) {
            // 1. In-app notification (Portal Teknisi)
            try {
                Notification::create([
                    'uuid' => (string) Str::uuid(),
                    'recipient_id' => $technician->id,
                    'title' => ucwords(strtolower($heading)) . ' #' . $wo->id,
                    'message' => "{$customerName} — {$date} {$time} WIB. Alamat: {$address}" . ($techs->count() > 1 ? " (Tim: {$allNames})" : ""),
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
                $inAppSuccess = true;
            } catch (\Throwable $e) {
                Log::warning('[InstallationNotifier] Gagal membuat notifikasi in-app: ' . $e->getMessage(), ['installation_id' => $wo->id, 'tech_id' => $technician->id]);
            }

            // 2. WhatsApp ke teknisi
            $techPhone = $this->normalizePhone(
                $technician->whatsapp ?: ($technician->phone ?: ($technician->employee?->phone ?? null))
            );

            if (!$techPhone) {
                $waErrors[] = "{$technician->name}: Nomor WhatsApp belum diisi";
                Log::info('[InstallationNotifier] Teknisi tanpa nomor WhatsApp', ['user_id' => $technician->id]);
                continue;
            }

            $text = "*[{$heading}]*\n\n"
                . "Halo {$technician->name},\n"
                . "Anda ditugaskan untuk pekerjaan instalasi berikut:\n\n"
                . "*WO:* #{$wo->id}\n"
                . "*Pelanggan:* {$customerName}\n"
                . "*No. HP:* {$phone}\n"
                . "*Alamat:* {$address}"
                . $mapsLine . "\n"
                . "*Jadwal:* {$date} {$time} WIB\n"
                . "*Prioritas:* {$priority}\n"
                . "*Catatan:* " . ($wo->notes ?: '-')
                . $teamLine . "\n\n"
                . "Buka Portal Teknisi: {$portalUrl}";

            try {
                $send = WhatsAppDirectSender::send($techPhone, $text);
                if (!empty($send['success'])) {
                    $waSuccess = true;
                } else {
                    $waErrors[] = "{$technician->name}: " . ($send['error'] ?? 'Gagal');
                }
            } catch (\Throwable $e) {
                $waErrors[] = "{$technician->name}: " . $e->getMessage();
                Log::warning('[InstallationNotifier] Gagal kirim WhatsApp teknisi: ' . $e->getMessage(), ['installation_id' => $wo->id, 'tech_id' => $technician->id]);
            }
        }

        $result['in_app'] = $inAppSuccess;
        $result['whatsapp'] = $waSuccess;
        $result['whatsapp_error'] = !empty($waErrors) ? implode(', ', $waErrors) : null;

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
