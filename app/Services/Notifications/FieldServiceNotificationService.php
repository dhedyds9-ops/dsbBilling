<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Events\FieldService\InstallationOperationalEvent;
use App\Events\FieldService\MaintenanceOperationalEvent;
use App\Events\FieldService\TicketOperationalEvent;
use App\Events\FieldService\WorkOrderOperationalEvent;
use App\Jobs\Notifications\SendTelegramMessageJob;
use App\Jobs\Notifications\SendWaMessageJob;
use App\Models\CRM\Customer;
use App\Models\Notification\Notification;
use App\Models\Setting;
use App\Models\User;
use App\Services\Notifications\Telegram\TelegramService;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use App\Services\Notifications\WhatsApp\WaTemplateEngine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FieldServiceNotificationService
{
    public function __construct(
        protected WaTemplateEngine $waTemplate,
        protected TelegramService $telegramService,
    ) {}

    // =========================================================================
    // 1. EVENT DISPATCHERS & HANDLERS
    // =========================================================================

    public function handleTicketEvent(TicketOperationalEvent $event): void
    {
        $ticket = $event->ticket;
        $ticket->loadMissing(['customer', 'assignedTo', 'branch']);
        $action = $event->action;

        match ($action) {
            'created' => $this->onTicketCreated($ticket, $event->actorId),
            'assigned', 'reassigned' => $this->onTicketAssigned($ticket, $action === 'reassigned'),
            'resolved' => $this->onTicketResolved($ticket, $event->actorId),
            'cancelled' => $this->onTicketCancelled($ticket, $event->extra['reason'] ?? ''),
            default => null,
        };
    }

    public function handleWorkOrderEvent(WorkOrderOperationalEvent $event): void
    {
        $wo = $event->workOrder;
        $wo->loadMissing(['customer', 'technician', 'branch']);
        $action = $event->action;

        match ($action) {
            'created', 'assigned' => $this->onWorkOrderAssigned($wo),
            'started' => $this->onWorkOrderStarted($wo),
            'completed' => $this->onWorkOrderCompleted($wo),
            'approved' => $this->onWorkOrderApproved($wo, $event->actorId),
            'rejected' => $this->onWorkOrderRejected($wo, $event->extra['reason'] ?? '', $event->actorId),
            default => null,
        };
    }

    public function handleInstallationEvent(InstallationOperationalEvent $event): void
    {
        $inst = $event->installation;
        $inst->loadMissing(['customer', 'technician', 'branch']);
        $action = $event->action;

        match ($action) {
            'scheduled' => $this->onInstallationScheduled($inst),
            'assigned' => $this->onInstallationAssigned($inst),
            'completed' => $this->onInstallationCompleted($inst),
            'activated' => $this->onActivationSuccess($inst, $event->extra),
            'activation_failed' => $this->onActivationFailed($inst, $event->extra['error'] ?? 'Terjadi kegagalan provisioning'),
            default => null,
        };
    }

    public function handleMaintenanceEvent(MaintenanceOperationalEvent $event): void
    {
        $maint = $event->maintenance;
        $maint->loadMissing(['technician', 'branch']);
        $action = $event->action;

        match ($action) {
            'created', 'assigned' => $this->onMaintenanceAssigned($maint),
            'started' => $this->onMaintenanceStarted($maint),
            'completed' => $this->onMaintenanceCompleted($maint),
            'material_shortage' => $this->onMaintenanceMaterialShortage($maint, $event->extra),
            default => null,
        };
    }

    // =========================================================================
    // 2. SPECIFIC BUSINESS SCENARIOS
    // =========================================================================

    // --- Ticket Handlers ---

    protected function onTicketCreated($ticket, ?int $actorId): void
    {
        $supervisors = $this->getSupervisorsForBranch($ticket->branch_id);
        $custName = $ticket->customer?->name ?? 'Pelanggan';

        // 1. Notifikasi In-App & WA ke Admin / NOC / Manager
        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: 'Tiket Support Baru #' . $ticket->id,
                message: "Tiket keluhan baru dari {$custName}: \"{$ticket->title}\"",
                data: [
                    'url' => route('tickets.direct', $ticket->id),
                    'icon' => 'support_agent',
                    'color' => 'blue',
                    'ticket_id' => $ticket->id,
                ],
                event: 'ticket_created',
                entityType: 'ticket',
                entityId: $ticket->id
            );

            if ($phone = $this->normalizePhone($supervisor->whatsapp ?? $supervisor->phone)) {
                $durasiStr = method_exists($ticket, 'formatEstimatedDuration') ? $ticket->formatEstimatedDuration() : null;
                $slaDueStr = !empty($ticket->sla_due_at) ? (is_string($ticket->sla_due_at) ? $ticket->sla_due_at : $ticket->sla_due_at->format('d/m/Y H:i')) : null;

                $text = "🎫 *TIKET SUPPORT BARU #{$ticket->id}*\n\n"
                    . "*Pelapor:* {$custName}\n"
                    . "*Judul:* {$ticket->title}\n"
                    . "*Kategori:* " . ucfirst($ticket->category ?? 'Umum') . "\n"
                    . "*Prioritas:* " . strtoupper($ticket->priority ?? 'Normal') . "\n"
                    . ($durasiStr ? "*Estimasi Pengerjaan:* {$durasiStr}\n" : "")
                    . ($slaDueStr ? "*Target Selesai (SLA):* {$slaDueStr} WIB\n" : "")
                    . "*Waktu:* " . now()->format('d/m/Y H:i') . " WIB\n\n"
                    . "Buka Tiket: " . route('tickets.direct', $ticket->id);

                $this->sendWhatsApp(
                    phone: $phone,
                    text: $text,
                    category: 'support',
                    context: ['ticket_id' => $ticket->id, 'type' => 'ticket_created_admin'],
                    idempotencyKey: "wa_ticket_created_{$ticket->id}_{$supervisor->id}"
                );
            }
        }

        // 2. Konfirmasi WA ke Pelanggan jika tiket punya customer_id
        if ($custPhone = $this->normalizePhone($ticket->customer?->phone)) {
            $durasiStr = method_exists($ticket, 'formatEstimatedDuration') ? $ticket->formatEstimatedDuration() : null;
            $slaDueStr = !empty($ticket->sla_due_at) ? (is_string($ticket->sla_due_at) ? $ticket->sla_due_at : $ticket->sla_due_at->format('d/m/Y H:i')) : null;

            $custText = "🎫 *TIKET ANDA BERHASIL DIBUAT*\n\n"
                . "Halo *{$custName}*,\n\n"
                . "Laporan/keluhan Anda telah kami terima:\n"
                . "• No. Tiket: *#{$ticket->id}*\n"
                . "• Perihal: {$ticket->title}\n"
                . ($durasiStr ? "• Estimasi Penanganan: *{$durasiStr}*\n" : "")
                . ($slaDueStr ? "• Target Selesai (SLA): *{$slaDueStr} WIB*\n" : "")
                . "• Status: Menunggu Penanganan\n\n"
                . "Tim teknis kami akan segera menindaklanjuti. Terima kasih telah menghubungi kami 🙏\n— dsBilling Support";

            $this->sendWhatsApp(
                phone: $custPhone,
                text: $custText,
                category: 'support',
                context: ['ticket_id' => $ticket->id, 'type' => 'ticket_ack_customer'],
                idempotencyKey: "wa_ticket_ack_{$ticket->id}"
            );
        }

        // 3. Telegram Siaran ke Grup NOC
        $tgText = "🎫 <b>TIKET SUPPORT BARU #{$ticket->id}</b>\n\n"
            . "👤 <b>Pelapor:</b> " . htmlspecialchars($custName) . "\n"
            . "📌 <b>Judul:</b> " . htmlspecialchars($ticket->title) . "\n"
            . "⚡ <b>Prioritas:</b> " . strtoupper($ticket->priority ?? 'Normal') . "\n"
            . "🏢 <b>Cabang:</b> " . htmlspecialchars($ticket->branch?->name ?? 'Pusat') . "\n"
            . "⏰ <b>Waktu:</b> " . now()->format('d/m/Y H:i') . " WIB";

        $this->sendTelegramBroadcast('ticket', $tgText, "tg_ticket_created_{$ticket->id}");
    }

    protected function onTicketAssigned($ticket, bool $isReassigned): void
    {
        $techs = $ticket->assignedTechnicians();
        if ($techs->isEmpty() && $ticket->assigned_to) {
            $t = User::find($ticket->assigned_to);
            if ($t) $techs = collect([$t]);
        }

        $heading = $isReassigned ? 'PENGALIHAN TIKET' : 'PENUGASAN TIKET';
        $custName = $ticket->customer?->name ?? 'Pelanggan';

        foreach ($techs as $tech) {
            $this->sendInApp(
                recipientId: $tech->id,
                title: "{$heading} #{$ticket->id}",
                message: "Anda ditugaskan menangani tiket: {$ticket->title} ({$custName})",
                data: [
                    'url' => route('technician.my-jobs.index'),
                    'icon' => 'build',
                    'color' => 'amber',
                    'ticket_id' => $ticket->id,
                ],
                event: $isReassigned ? 'ticket_reassigned' : 'ticket_assigned',
                entityType: 'ticket',
                entityId: $ticket->id
            );

            if ($phone = $this->normalizePhone($tech->whatsapp ?? $tech->phone)) {
                $durasiStr = method_exists($ticket, 'formatEstimatedDuration') ? $ticket->formatEstimatedDuration() : null;
                $slaDueStr = !empty($ticket->sla_due_at) ? (is_string($ticket->sla_due_at) ? $ticket->sla_due_at : $ticket->sla_due_at->format('d/m/Y H:i')) : null;

                $text = "🛠️ *{$heading} #{$ticket->id}*\n\n"
                    . "Halo *{$tech->name}*,\n"
                    . "Anda ditugaskan untuk menangani tiket berikut:\n\n"
                    . "*Pelanggan:* {$custName}\n"
                    . "*Keluhan:* {$ticket->title}\n"
                    . "*Prioritas:* " . strtoupper($ticket->priority ?? 'Normal') . "\n"
                    . ($durasiStr ? "*Estimasi Pengerjaan:* {$durasiStr}\n" : "")
                    . ($slaDueStr ? "*Target Selesai (SLA):* {$slaDueStr} WIB\n" : "")
                    . "*Catatan:* " . ($ticket->description ?: '-') . "\n\n"
                    . "Buka Tugas Saya: " . route('technician.my-jobs.index');

                $this->sendWhatsApp(
                    phone: $phone,
                    text: $text,
                    category: 'workforce',
                    context: ['ticket_id' => $ticket->id, 'type' => 'ticket_assigned_tech'],
                    idempotencyKey: "wa_ticket_assigned_{$ticket->id}_{$tech->id}"
                );
            }

            // Push Notification FCM ke Android Teknisi
            $this->sendFcmPush(
                recipientId: $tech->id,
                title: "{$heading} #{$ticket->id}",
                body: "Keluhan: {$ticket->title} ({$custName})",
                data: [
                    'type'        => 'ticket_assigned',
                    'entity_type' => 'ticket',
                    'entity_id'   => $ticket->id,
                    'deep_link'   => 'dsbilling://tickets/' . $ticket->id,
                ],
                channelId: 'channel_tickets',
                idempotencyKey: $this->buildIdempotencyKey('fcm', 'ticket_assigned', 'ticket', $ticket->id, $tech->id)
            );
        }
    }

    protected function onTicketResolved($ticket, ?int $actorId): void
    {
        $custName = $ticket->customer?->name ?? 'Pelanggan';

        // Konfirmasi ke Pelanggan
        if ($custPhone = $this->normalizePhone($ticket->customer?->phone)) {
            $text = "✅ *TIKET KELUHAN SELESAI*\n\n"
                . "Halo *{$custName}*,\n\n"
                . "Tiket keluhan Anda *#{$ticket->id}* telah selesai ditangani oleh tim teknisi kami.\n\n"
                . "*Catatan Penyelesaian:* " . ($ticket->resolution_notes ?: 'Pekerjaan perbaikan selesai dilaksanakan') . "\n\n"
                . "Jika koneksi Anda masih bermasalah, silakan hubungi tim support kami kembali.\nTerima kasih 🙏\n— dsBilling Support";

            $this->sendWhatsApp(
                phone: $custPhone,
                text: $text,
                category: 'support',
                context: ['ticket_id' => $ticket->id, 'type' => 'ticket_resolved_customer'],
                idempotencyKey: "wa_ticket_resolved_{$ticket->id}"
            );
        }
    }

    protected function onTicketCancelled($ticket, string $reason): void
    {
        if ($custPhone = $this->normalizePhone($ticket->customer?->phone)) {
            $text = "ℹ️ *PEMBERITAHUAN TIKET DIBATALKAN*\n\n"
                . "Halo *{$ticket->customer?->name}*,\n"
                . "Tiket #{$ticket->id} ({$ticket->title}) telah dibatalkan.\n"
                . "*Alasan:* " . ($reason ?: 'Dibatalkan oleh petugas') . "\n\n"
                . "— dsBilling Support";

            $this->sendWhatsApp(
                phone: $custPhone,
                text: $text,
                category: 'support',
                context: ['ticket_id' => $ticket->id, 'type' => 'ticket_cancelled'],
                idempotencyKey: "wa_ticket_cancelled_{$ticket->id}"
            );
        }
    }

    // --- Work Order Handlers ---

    protected function onWorkOrderAssigned($wo): void
    {
        if (!$wo->assigned_to) return;
        $tech = User::find($wo->assigned_to);
        if (!$tech) return;

        $custName = $wo->customer?->name ?? 'Pelanggan';

        $this->sendInApp(
            recipientId: $tech->id,
            title: 'Penugasan Work Order #' . ($wo->uuid ?? $wo->id),
            message: "Tugas baru: {$wo->title} ({$custName})",
            data: [
                'url' => route('technician.my-jobs.show', $wo->id),
                'icon' => 'engineering',
                'color' => 'blue',
                'work_order_id' => $wo->id,
            ],
            event: 'wo_assigned',
            entityType: 'work_order',
            entityId: $wo->id
        );

        if ($phone = $this->normalizePhone($tech->whatsapp ?? $tech->phone)) {
            $durasiStr = method_exists($wo, 'formatEstimatedDuration') ? $wo->formatEstimatedDuration() : null;
            $slaDueStr = !empty($wo->sla_due_at) ? (is_string($wo->sla_due_at) ? $wo->sla_due_at : $wo->sla_due_at->format('d/m/Y H:i')) : null;

            $text = "📋 *PENUGASAN WORK ORDER*\n\n"
                . "Halo *{$tech->name}*,\n"
                . "Anda mendapatkan tugas kerja lapangan baru:\n\n"
                . "*SPK:* #" . ($wo->uuid ?? $wo->id) . "\n"
                . "*Judul:* {$wo->title}\n"
                . "*Pelanggan:* {$custName}\n"
                . "*Alamat:* " . ($wo->customer?->address ?: '-') . "\n"
                . "*Jadwal:* " . ($wo->scheduled_at ? $wo->scheduled_at->format('d/m/Y H:i') . ' WIB' : 'Segera') . "\n"
                . ($durasiStr ? "*Estimasi Pengerjaan:* {$durasiStr}\n" : "")
                . ($slaDueStr ? "*Target Selesai (SLA):* {$slaDueStr} WIB\n" : "") . "\n"
                . "Detail Pekerjaan: " . route('technician.my-jobs.show', $wo->id);

            $this->sendWhatsApp(
                phone: $phone,
                text: $text,
                category: 'workforce',
                context: ['work_order_id' => $wo->id, 'type' => 'wo_assigned'],
                idempotencyKey: "wa_wo_assigned_{$wo->id}_{$tech->id}"
            );
        }

        // Push Notification FCM ke Android Teknisi
        $this->sendFcmPush(
            recipientId: $tech->id,
            title: 'Penugasan Work Order #' . ($wo->uuid ? strtoupper(substr(str_replace('-', '', $wo->uuid), 0, 6)) : $wo->id),
            body: "Tugas baru: {$wo->title} ({$custName})",
            data: [
                'type'        => 'work_order_assigned',
                'entity_type' => 'work_order',
                'entity_id'   => $wo->id,
                'deep_link'   => 'dsbilling://work-orders/' . $wo->id,
            ],
            channelId: 'channel_installations',
            idempotencyKey: $this->buildIdempotencyKey('fcm', 'wo_assigned', 'work_order', $wo->id, $tech->id)
        );
    }

    protected function onWorkOrderStarted($wo): void
    {
        $supervisors = $this->getSupervisorsForBranch($wo->branch_id);
        $techName = $wo->technician?->name ?? 'Teknisi';

        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: "Pekerjaan Dimulai: SPK #{$wo->id}",
                message: "Teknisi {$techName} telah memulai pengerjaan: {$wo->title}",
                data: [
                    'url' => route('support.work-orders.history'),
                    'icon' => 'play_circle',
                    'color' => 'indigo',
                    'work_order_id' => $wo->id,
                ],
                event: 'wo_started',
                entityType: 'work_order',
                entityId: $wo->id
            );
        }

        // Kabari Pelanggan bahwa teknisi sudah bekerja
        if ($custPhone = $this->normalizePhone($wo->customer?->phone)) {
            $text = "🚀 *TEKNISI MULAI BEKERJA*\n\n"
                . "Halo *{$wo->customer?->name}*,\n"
                . "Teknisi kami (*{$techName}*) saat ini telah memulai pekerjaan di lokasi Anda untuk tugas: *{$wo->title}*.\n\n"
                . "Mohon kesediaan Anda mendampingi proses pekerjaan teknis jika diperlukan. Terima kasih 🙏\n— dsBilling Field Service";

            $this->sendWhatsApp(
                phone: $custPhone,
                text: $text,
                category: 'workforce',
                context: ['work_order_id' => $wo->id, 'type' => 'wo_started_customer'],
                idempotencyKey: "wa_wo_started_{$wo->id}"
            );
        }
    }

    protected function onWorkOrderCompleted($wo): void
    {
        $supervisors = $this->getSupervisorsForBranch($wo->branch_id);
        $techName = $wo->technician?->name ?? 'Teknisi';

        // Notif Admin / NOC untuk Approval / Verifikasi
        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: "Menunggu Approval: SPK #{$wo->id}",
                message: "Pekerjaan telah diselesaikan oleh {$techName}. Menunggu verifikasi / persetujuan.",
                data: [
                    'url' => route('support.work-orders.history'),
                    'icon' => 'verified',
                    'color' => 'amber',
                    'work_order_id' => $wo->id,
                ],
                event: 'wo_completed',
                entityType: 'work_order',
                entityId: $wo->id
            );
        }

        $tgText = "✅ <b>SPK SELESAI — MENUNGGU VERIFIKASI</b>\n\n"
            . "📋 <b>SPK:</b> #" . ($wo->uuid ?? $wo->id) . "\n"
            . "👤 <b>Pelanggan:</b> " . htmlspecialchars($wo->customer?->name ?? '-') . "\n"
            . "🛠️ <b>Teknisi:</b> " . htmlspecialchars($techName) . "\n"
            . "📶 <b>Redaman:</b> " . ($wo->optical_rx_power ? $wo->optical_rx_power . ' dBm' : '-') . "\n"
            . "⏰ <b>Waktu Selesai:</b> " . now()->format('d/m/Y H:i') . " WIB";

        $this->sendTelegramBroadcast('workorder', $tgText, "tg_wo_completed_{$wo->id}");
    }

    protected function onWorkOrderApproved($wo, ?int $approverId): void
    {
        if ($tech = $wo->technician) {
            $this->sendInApp(
                recipientId: $tech->id,
                title: "SPK Disetujui: #{$wo->id}",
                message: "Pekerjaan '{$wo->title}' telah diverifikasi & disetujui oleh pengawas.",
                data: [
                    'url' => route('technician.my-jobs.show', $wo->id),
                    'icon' => 'check_circle',
                    'color' => 'emerald',
                    'work_order_id' => $wo->id,
                ],
                event: 'wo_approved',
                entityType: 'work_order',
                entityId: $wo->id
            );

            // Push Notification FCM
            $this->sendFcmPush(
                recipientId: $tech->id,
                title: "QC Disetujui: SPK #{$wo->id}",
                body: "Pekerjaan '{$wo->title}' telah disetujui oleh pengawas.",
                data: [
                    'type'        => 'qc_approved',
                    'entity_type' => 'work_order',
                    'entity_id'   => $wo->id,
                    'deep_link'   => 'dsbilling://work-orders/' . $wo->id,
                ],
                channelId: 'channel_qc',
                idempotencyKey: $this->buildIdempotencyKey('fcm', 'qc_approved', 'work_order', $wo->id, $tech->id)
            );
        }
    }

    protected function onWorkOrderRejected($wo, string $reason, ?int $rejectorId): void
    {
        if ($tech = $wo->technician) {
            $this->sendInApp(
                recipientId: $tech->id,
                title: "QC Ditolak: SPK #{$wo->id}",
                message: "Pekerjaan '{$wo->title}' ditolak untuk perbaikan. Catatan: {$reason}",
                data: [
                    'url' => route('technician.my-jobs.show', $wo->id),
                    'icon' => 'cancel',
                    'color' => 'rose',
                    'work_order_id' => $wo->id,
                ],
                event: 'wo_rejected',
                entityType: 'work_order',
                entityId: $wo->id
            );

            if ($phone = $this->normalizePhone($tech->whatsapp ?? $tech->phone)) {
                $text = "⚠️ *HASIL PEKERJAAN DIKEMBALIKAN (QC DITOLAK)*\n\n"
                    . "Halo *{$tech->name}*,\n"
                    . "Pekerjaan SPK #{$wo->id} ({$wo->title}) dikembalikan oleh pengawas untuk dilakukan perbaikan:\n\n"
                    . "*Catatan Penolakan:* " . ($reason ?: 'Mohon periksa kembali kerapian instalasi dan redaman kabel.') . "\n\n"
                    . "Silakan tinjau kembali tugas Anda di Portal Teknisi:\n" . route('technician.my-jobs.show', $wo->id);

                $this->sendWhatsApp(
                    phone: $phone,
                    text: $text,
                    category: 'workforce',
                    context: ['work_order_id' => $wo->id, 'type' => 'wo_rejected_tech'],
                    idempotencyKey: "wa_wo_rejected_{$wo->id}_{$tech->id}"
                );
            }

            // Push Notification FCM ke Android Teknisi
            $this->sendFcmPush(
                recipientId: $tech->id,
                title: "QC Ditolak: SPK #{$wo->id}",
                body: "Pekerjaan '{$wo->title}' dikembalikan untuk perbaikan: {$reason}",
                data: [
                    'type'        => 'qc_rejected',
                    'entity_type' => 'work_order',
                    'entity_id'   => $wo->id,
                    'reason'      => $reason,
                    'deep_link'   => 'dsbilling://work-orders/' . $wo->id,
                ],
                channelId: 'channel_qc',
                idempotencyKey: $this->buildIdempotencyKey('fcm', 'qc_rejected', 'work_order', $wo->id, $tech->id)
            );
        }
    }

    // --- Installation & Activation Handlers ---

    protected function onInstallationScheduled($inst): void
    {
        $custName = $inst->customer_name ?: ($inst->customer?->name ?? 'Pelanggan');
        $date = $inst->scheduled_date
            ? (is_string($inst->scheduled_date) ? $inst->scheduled_date : $inst->scheduled_date->format('d/m/Y'))
            : '-';
        $time = $inst->scheduled_time ? substr((string) $inst->scheduled_time, 0, 5) : '09:00';
        $durasiStr = method_exists($inst, 'formatEstimatedDuration') ? $inst->formatEstimatedDuration() : null;
        $slaDueStr = !empty($inst->sla_due_at) ? (is_string($inst->sla_due_at) ? $inst->sla_due_at : $inst->sla_due_at->format('d/m/Y H:i')) : null;

        if ($custPhone = $this->normalizePhone($inst->customer_phone ?: ($inst->customer?->phone ?? null))) {
            $text = "📅 *JADWAL INSTALASI INTERNET PASANG BARU (PSB)*\n\n"
                . "Halo *{$custName}*,\n"
                . "Pemasangan internet baru Anda telah dijadwalkan pada:\n\n"
                . "• *Tanggal:* {$date}\n"
                . "• *Waktu:* {$time} WIB\n"
                . ($durasiStr ? "• *Estimasi Pemasangan:* {$durasiStr}\n" : "")
                . ($slaDueStr ? "• *Target Selesai (SLA):* {$slaDueStr} WIB\n" : "")
                . "• *Alamat:* " . ($inst->installation_address ?: '-') . "\n\n"
                . "Pastikan ada orang di lokasi saat teknisi kami tiba. Terima kasih 🙏\n— dsBilling Operations";

            $this->sendWhatsApp(
                phone: $custPhone,
                text: $text,
                category: 'installation',
                context: ['installation_id' => $inst->id, 'type' => 'inst_scheduled_customer'],
                idempotencyKey: "wa_inst_scheduled_{$inst->id}"
            );
        }
    }

    protected function onInstallationAssigned($inst): void
    {
        // Panggil notifier resmi InstallationTechnicianNotifier
        try {
            app(\App\Services\CRM\InstallationTechnicianNotifier::class)->notifyAssigned($inst, 'assigned');
        } catch (\Throwable $e) {
            Log::warning('[FieldServiceNotificationService] Gagal panggil InstallationTechnicianNotifier: ' . $e->getMessage());
        }
    }

    protected function onInstallationCompleted($inst): void
    {
        $supervisors = $this->getSupervisorsForBranch($inst->branch_id);
        $custName = $inst->customer_name ?: ($inst->customer?->name ?? 'Pelanggan');

        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: "Instalasi Fisik Selesai: #{$inst->id}",
                message: "Instalasi kabel & ONU untuk {$custName} telah selesai. Siap aktivasi.",
                data: [
                    'url' => route('support.installation'),
                    'icon' => 'router',
                    'color' => 'blue',
                    'installation_id' => $inst->id,
                ],
                event: 'installation_completed',
                entityType: 'installation',
                entityId: $inst->id
            );
        }
    }

    protected function onActivationSuccess($inst, array $extra): void
    {
        $customer = $inst->customer;
        $custName = $customer?->name ?? ($inst->customer_name ?: 'Pelanggan');

        if ($custPhone = $this->normalizePhone($customer?->phone ?: $inst->customer_phone)) {
            $text = "🎉 *LAYANAN INTERNET ANDA TELAH AKTIF!*\n\n"
                . "Halo *{$custName}*,\n"
                . "Selamat! Pemasangan dan aktivasi layanan internet Anda telah berhasil dan aktif sepenuhnya.\n\n"
                . "• Paket: " . ($inst->package_name ?: 'Internet Broadband') . "\n"
                . "• Tanggal Aktif: " . now()->format('d/m/Y H:i') . " WIB\n\n"
                . "Selamat menikmati layanan internet kami. Jika butuh bantuan, hubungi call center kami.\n— dsBilling Operations";

            $this->sendWhatsApp(
                phone: $custPhone,
                text: $text,
                category: 'activation',
                context: ['installation_id' => $inst->id, 'type' => 'activation_success_customer'],
                idempotencyKey: "wa_act_success_{$inst->id}"
            );
        }
    }

    protected function onActivationFailed($inst, string $errorMessage): void
    {
        $supervisors = $this->getSupervisorsForBranch($inst->branch_id);
        $custName = $inst->customer_name ?: ($inst->customer?->name ?? 'Pelanggan');

        // Alert ke NOC & Admin
        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: "PERINGATAN: Aktivasi Gagal #{$inst->id}",
                message: "Aktivasi layanan untuk {$custName} gagal: {$errorMessage}",
                data: [
                    'url' => route('support.installation'),
                    'icon' => 'warning',
                    'color' => 'rose',
                    'installation_id' => $inst->id,
                ],
                event: 'activation_failed',
                entityType: 'installation',
                entityId: $inst->id
            );
        }

        $tgText = "🚨 <b>PERINGATAN: AKTIVASI PSB GAGAL</b>\n\n"
            . "📋 <b>Instalasi:</b> #{$inst->id}\n"
            . "👤 <b>Pelanggan:</b> " . htmlspecialchars($custName) . "\n"
            . "❌ <b>Penyebab Kegagalan:</b> " . htmlspecialchars($errorMessage) . "\n"
            . "⏰ <b>Waktu:</b> " . now()->format('d/m/Y H:i') . " WIB";

        $this->sendTelegramBroadcast('alarm', $tgText, "tg_act_failed_{$inst->id}");
    }

    // --- Maintenance Handlers ---

    protected function onMaintenanceAssigned($maint): void
    {
        if (!$maint->technician_id) return;
        $tech = User::find($maint->technician_id);
        if (!$tech) return;

        $this->sendInApp(
            recipientId: $tech->id,
            title: "Tugas Maintenance: #{$maint->id}",
            message: "Tugas maintenance: {$maint->title} di " . ($maint->location_name ?? ($maint->location ?? '-')),
            data: [
                'url' => route('technician.my-jobs.maintenance'),
                'icon' => 'handyman',
                'color' => 'purple',
                'maintenance_id' => $maint->id,
            ],
            event: 'maintenance_assigned',
            entityType: 'maintenance',
            entityId: $maint->id
        );

        if ($phone = $this->normalizePhone($tech->whatsapp ?? $tech->phone)) {
            $durasiStr = method_exists($maint, 'formatEstimatedDuration') ? $maint->formatEstimatedDuration() : null;
            $slaDueStr = !empty($maint->sla_due_at) ? (is_string($maint->sla_due_at) ? $maint->sla_due_at : $maint->sla_due_at->format('d/m/Y H:i')) : null;

            $waText = $this->waTemplate->maintenanceAssigned([
                'maintenance_id'    => $maint->id,
                'technician_name'   => $tech->name,
                'title'             => $maint->title,
                'type'              => $maint->type ?? 'preventive',
                'priority'          => $maint->priority ?? 'medium',
                'device_name'       => $maint->device_name ?? ($maint->device_type ?? '-'),
                'location_name'     => $maint->location_name ?? ($maint->location ?? '-'),
                'scheduled_date'    => $maint->scheduled_date ? (is_string($maint->scheduled_date) ? $maint->scheduled_date : $maint->scheduled_date->format('d/m/Y')) : '-',
                'scheduled_time'    => $maint->scheduled_time ? substr((string) $maint->scheduled_time, 0, 5) : '09:00',
                'estimated_duration' => $durasiStr,
                'sla_due_at'        => $slaDueStr,
                'notes'             => $maint->notes ?? '',
            ]);

            $this->sendWhatsApp(
                phone: $phone,
                text: $waText,
                category: 'workforce',
                context: ['maintenance_id' => $maint->id, 'type' => 'maint_assigned_tech'],
                idempotencyKey: "wa_maint_assigned_{$maint->id}_{$tech->id}"
            );
        }
    }

    protected function onMaintenanceStarted($maint): void
    {
        $supervisors = $this->getSupervisorsForBranch($maint->branch_id);
        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: "Maintenance Dimulai: #{$maint->id}",
                message: "Maintenance {$maint->title} telah dimulai di {$maint->location}",
                data: [
                    'url' => route('support.maintenance'),
                    'icon' => 'play_circle',
                    'color' => 'indigo',
                    'maintenance_id' => $maint->id,
                ],
                event: 'maintenance_started',
                entityType: 'maintenance',
                entityId: $maint->id
            );
        }
    }

    protected function onMaintenanceCompleted($maint): void
    {
        $supervisors = $this->getSupervisorsForBranch($maint->branch_id);
        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: "Maintenance Selesai: #{$maint->id}",
                message: "Maintenance {$maint->title} di {$maint->location} telah selesai.",
                data: [
                    'url' => route('support.maintenance'),
                    'icon' => 'check_circle',
                    'color' => 'emerald',
                    'maintenance_id' => $maint->id,
                ],
                event: 'maintenance_completed',
                entityType: 'maintenance',
                entityId: $maint->id
            );
        }
    }

    protected function onMaintenanceMaterialShortage($maint, array $extra): void
    {
        $supervisors = $this->getSupervisorsForBranch($maint->branch_id);
        $matName = $extra['material_name'] ?? 'Material';
        $qty = $extra['qty'] ?? 1;

        foreach ($supervisors as $supervisor) {
            $this->sendInApp(
                recipientId: $supervisor->id,
                title: "Kekurangan Material Maintenance #{$maint->id}",
                message: "Teknisi membutuhkan {$qty}x {$matName} untuk pemeliharaan di {$maint->location}",
                data: [
                    'url' => route('support.maintenance'),
                    'icon' => 'inventory_2',
                    'color' => 'rose',
                    'maintenance_id' => $maint->id,
                ],
                event: 'maintenance_shortage',
                entityType: 'maintenance',
                entityId: $maint->id
            );
        }
    }

    // =========================================================================
    // 3. LOW-LEVEL ATOMIC CHANNELS (IN-APP, WA, TELEGRAM) & IDEMPOTENCY
    // =========================================================================

    public function sendInApp(
        int $recipientId,
        string $title,
        string $message,
        array $data = [],
        string $event = 'notification',
        string $entityType = '',
        int|string $entityId = ''
    ): ?Notification {
        try {
            return Notification::create([
                'uuid' => (string) Str::uuid(),
                'recipient_id' => $recipientId,
                'title' => $title,
                'message' => $message,
                'type' => 'in_app',
                'status' => 'pending',
                'sent_at' => now(),
                'data' => array_merge($data, [
                    'event' => $event,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                ]),
                'created_by' => Auth::id() ?: null,
            ]);
        } catch (\Throwable $e) {
            Log::warning("[FieldServiceNotification] Gagal simpan in-app: {$e->getMessage()}");
            return null;
        }
    }

    public function sendWhatsApp(
        string $phone,
        string $text,
        string $category = 'general',
        array $context = [],
        string $idempotencyKey = ''
    ): void {
        $cleanPhone = $this->normalizePhone($phone);
        if (!$cleanPhone) {
            return;
        }

        // Idempotency check: Jangan kirim duplikat dalam 30 menit
        if ($idempotencyKey && $this->isDuplicate('wa', $idempotencyKey)) {
            Log::info("[FieldServiceNotification] Skip WA duplikat: {$idempotencyKey}");
            return;
        }

        $msg = new WaOutgoingMessage(
            toPhone: $cleanPhone,
            text: $text,
            category: $category,
            priorityHigh: true,
            idempotencyKey: $idempotencyKey ?: null,
            context: $context,
        );

        // Eksekusi WA strictly after database commit
        DB::afterCommit(function () use ($msg) {
            try {
                SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
            } catch (\Throwable $e) {
                Log::warning("[FieldServiceNotification] Gagal dispatch SendWaMessageJob: {$e->getMessage()}");
            }
        });
    }

    public function sendTelegramBroadcast(string $eventType, string $text, string $idempotencyKey = ''): void
    {
        if ($idempotencyKey && $this->isDuplicate('tg', $idempotencyKey)) {
            Log::info("[FieldServiceNotification] Skip Telegram duplikat: {$idempotencyKey}");
            return;
        }

        DB::afterCommit(function () use ($eventType, $text) {
            try {
                $this->telegramService->broadcastToChannels($eventType, $text);
            } catch (\Throwable $e) {
                Log::warning("[FieldServiceNotification] Gagal broadcast Telegram: {$e->getMessage()}");
            }
        });
    }

    /**
     * Kirim notifikasi push native Android via FCM HTTP v1 strictly after commit.
     */
    public function sendFcmPush(
        int $recipientId,
        string $title,
        string $body,
        array $data = [],
        string $channelId = 'channel_general',
        string $idempotencyKey = ''
    ): void {
        if ($idempotencyKey && $this->isDuplicate('fcm', $idempotencyKey)) {
            Log::info("[FieldServiceNotification] Skip FCM duplikat: {$idempotencyKey}");
            return;
        }

        DB::afterCommit(function () use ($recipientId, $title, $body, $data, $channelId, $idempotencyKey) {
            try {
                \App\Jobs\Notifications\SendFcmNotificationJob::dispatch(
                    recipientId: $recipientId,
                    title: $title,
                    body: $body,
                    data: $data,
                    channelId: $channelId,
                    idempotencyKey: $idempotencyKey ?: null
                )->onQueue('notifications-fcm');
            } catch (\Throwable $e) {
                Log::warning("[FieldServiceNotification] Gagal dispatch SendFcmNotificationJob: {$e->getMessage()}");
            }
        });
    }

    // =========================================================================
    // 4. HELPERS, SCOPING & DEDUPLICATION
    // =========================================================================

    public function buildIdempotencyKey(string $channel, string $event, string $entityType, int|string $entityId, int|string $recipientId = ''): string
    {
        return "{$channel}:{$event}:{$entityType}:{$entityId}:{$recipientId}";
    }

    public function isDuplicate(string $channel, string $key): bool
    {
        $cacheKey = "dsb_notif_dedup_" . md5("{$channel}:{$key}");
        if (Cache::has($cacheKey)) {
            return true;
        }
        Cache::put($cacheKey, now()->toIso8601String(), 1800);
        return false;
    }

    public function normalizePhone(?string $raw): ?string
    {
        if (!$raw) return null;
        $p = preg_replace('/\D/', '', $raw);
        if ($p === '') return null;
        if (str_starts_with($p, '0')) {
            $p = '62' . substr($p, 1);
        } elseif (!str_starts_with($p, '62')) {
            $p = '62' . $p;
        }
        return strlen($p) >= 10 ? $p : null;
    }

    /**
     * Dapatkan user pengawas (NOC, Manager, Admin) yang relevan untuk cabang tertentu.
     */
    public function getSupervisorsForBranch(?int $branchId): Collection
    {
        return User::query()
            ->withoutGlobalScopes()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereHas('roles', fn($r) => $r->whereIn('name', ['administrator', 'superadmin', 'admin', 'noc', 'manager', 'owner', 'root']))
                  ->orWhereIn('job_function', ['NOC', 'MANAGER', 'ADMINISTRATOR']);
            })
            ->when($branchId, function ($q) use ($branchId) {
                $q->where(function ($sub) use ($branchId) {
                    $sub->where('branch_id', $branchId)
                        ->orWhereNull('branch_id');
                });
            })
            ->limit(20)
            ->get();
    }

    // =========================================================================
    // 5. SLA WARNING & ESCALATION NOTIFICATIONS (PHASE 4.2)
    // =========================================================================

    /**
     * Kirim peringatan SLA saat pekerjaan mencapai ambang batas 80%.
     */
    public function notifySlaWarning($job, array $metrics): void
    {
        try {
            $entityType = match (true) {
                $job instanceof WorkOrder => 'work_order',
                $job instanceof Ticket => 'ticket',
                $job instanceof Installation => 'installation',
                default => 'job',
            };
            $entityId = $job->id;

            $code = !empty($job->uuid) 
                ? (strtoupper(substr($entityType, 0, 3)) . '-' . strtoupper(substr(str_replace('-', '', $job->uuid), 0, 6))) 
                : ("#" . $job->id);

            $custName = $job->customer?->name ?? ($job->customer_name ?? 'Pelanggan');
            $title = $job->title ?: ($job->description ?: 'Pekerjaan Lapangan');
            $branchName = $job->branch?->name ?? 'Pusat';
            $priority = strtoupper((string) ($job->priority ?? 'NORMAL'));
            $remainingText = $metrics['remaining_formatted'] ?? 'Segera berakhir';
            $percent = $metrics['percentage_used'] ?? 80;

            $actionUrl = match ($entityType) {
                'ticket' => route('tickets.direct', $job->id),
                default => route('support.work-orders.history'),
            };

            // 1. Notifikasi ke Teknisi Lapangan (In-App + WA)
            $tech = $job->technician ?? ($job->assignedTo ?? null);
            if (!$tech && !empty($job->assigned_to)) {
                $tech = User::withoutGlobalScopes()->find($job->assigned_to);
            }

            if ($tech) {
                $this->sendInApp(
                    recipientId: $tech->id,
                    title: "⚠️ Peringatan SLA: {$code} Mencapai {$percent}%",
                    message: "Pekerjaan \"{$title}\" ({$custName}) tersisa {$remainingText}. Harap selesaikan sebelum batas waktu SLA.",
                    data: [
                        'url' => route('technician.my-jobs.index'),
                        'icon' => 'warning',
                        'color' => 'amber',
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                    ],
                    event: 'sla_warning',
                    entityType: $entityType,
                    entityId: $entityId
                );

                if ($phone = $this->normalizePhone($tech->whatsapp ?? $tech->phone)) {
                    $waText = "⚠️ *PERINGATAN SLA OPERASIONAL (80%)*\n\n"
                        . "Halo *{$tech->name}*,\n"
                        . "Pekerjaan berikut mendekati batas waktu SLA:\n"
                        . "• No: *{$code}*\n"
                        . "• Pelanggan: {$custName}\n"
                        . "• Perihal: {$title}\n"
                        . "• Prioritas: *{$priority}*\n"
                        . "• Terpakai: *{$percent}%*\n"
                        . "• Sisa Waktu: *{$remainingText}*\n\n"
                        . "Harap segera selesaikan dan upload eviden sebelum batas waktu.";

                    $idempotencyKey = $this->buildIdempotencyKey('wa', 'sla_warning', $entityType, $entityId, $tech->id);
                    $this->sendWhatsApp($phone, $waText, 'sla_warning', ['job_id' => $entityId], $idempotencyKey);
                }

                // Push Notification FCM ke Android Teknisi
                $fcmTitle = "⚠️ Peringatan SLA: {$code} ({$percent}%)";
                $fcmBody = "Pekerjaan \"{$title}\" ({$custName}) tersisa {$remainingText}. Harap selesaikan segera.";
                $this->sendFcmPush(
                    recipientId: $tech->id,
                    title: $fcmTitle,
                    body: $fcmBody,
                    data: [
                        'type' => 'sla_warning',
                        'channel_id' => 'channel_sla',
                        'entity_type' => $entityType,
                        'entity_id' => (string) $entityId,
                        'work_order_id' => $entityType === 'work_order' ? (string) $entityId : '',
                        'deep_link' => $entityType === 'work_order' ? 'dsbilling://work-orders/' . $entityId : 'dsbilling://tickets/' . $entityId,
                    ],
                    channelId: 'channel_sla',
                    idempotencyKey: $this->buildIdempotencyKey('fcm', 'sla_warning', $entityType, $entityId, $tech->id)
                );
            }

            // 2. Notifikasi ke Pengawas / NOC / Admin Cabang
            $supervisors = $this->getSupervisorsForBranch($job->branch_id ?? null);
            foreach ($supervisors as $supervisor) {
                $this->sendInApp(
                    recipientId: $supervisor->id,
                    title: "⚠️ Peringatan SLA: {$code} ({$percent}%)",
                    message: "Pekerjaan \"{$title}\" ({$custName}) tersisa {$remainingText}. Teknisi: " . ($tech?->name ?? 'Belum Ditugaskan'),
                    data: [
                        'url' => $actionUrl,
                        'icon' => 'warning',
                        'color' => 'amber',
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                    ],
                    event: 'sla_warning_supervisor',
                    entityType: $entityType,
                    entityId: $entityId
                );
            }

            // 3. Telegram Broadcast ke NOC
            $tgText = "⚠️ <b>PERINGATAN SLA OPERASIONAL (80%)</b>\n\n"
                . "📋 <b>No Pekerjaan:</b> {$code}\n"
                . "👤 <b>Pelanggan:</b> " . htmlspecialchars($custName) . "\n"
                . "📌 <b>Perihal:</b> " . htmlspecialchars($title) . "\n"
                . "⚡ <b>Prioritas:</b> {$priority}\n"
                . "🏢 <b>Cabang:</b> " . htmlspecialchars($branchName) . "\n"
                . "👷 <b>Teknisi:</b> " . htmlspecialchars($tech?->name ?? 'Belum Ditugaskan') . "\n"
                . "⏳ <b>Sisa Waktu:</b> {$remainingText} ({$percent}% terpakai)\n"
                . "⏰ <b>Target SLA:</b> " . ($metrics['due_at']?->format('d/m/Y H:i') ?? '-');

            $tgKey = $this->buildIdempotencyKey('tg', 'sla_warning', $entityType, $entityId);
            $this->sendTelegramBroadcast('sla_warning', $tgText, $tgKey);

        } catch (\Throwable $e) {
            Log::warning("[FieldServiceNotification] notifySlaWarning gagal: {$e->getMessage()}");
        }
    }

    /**
     * Kirim eskalasi otomatis saat pekerjaan melewati batas SLA (Breached / Terlambat).
     */
    public function notifySlaBreached($job, array $metrics): void
    {
        try {
            $entityType = match (true) {
                $job instanceof WorkOrder => 'work_order',
                $job instanceof Ticket => 'ticket',
                $job instanceof Installation => 'installation',
                default => 'job',
            };
            $entityId = $job->id;

            $code = !empty($job->uuid) 
                ? (strtoupper(substr($entityType, 0, 3)) . '-' . strtoupper(substr(str_replace('-', '', $job->uuid), 0, 6))) 
                : ("#" . $job->id);

            $custName = $job->customer?->name ?? ($job->customer_name ?? 'Pelanggan');
            $title = $job->title ?: ($job->description ?: 'Pekerjaan Lapangan');
            $branchName = $job->branch?->name ?? 'Pusat';
            $priority = strtoupper((string) ($job->priority ?? 'NORMAL'));
            $overdueText = $metrics['remaining_formatted'] ?? 'Telah melewati batas waktu';

            $actionUrl = match ($entityType) {
                'ticket' => route('tickets.direct', $job->id),
                default => route('support.work-orders.history'),
            };

            $tech = $job->technician ?? ($job->assignedTo ?? null);
            if (!$tech && !empty($job->assigned_to)) {
                $tech = User::withoutGlobalScopes()->find($job->assigned_to);
            }

            // 1. In-App & WA ke Teknisi
            if ($tech) {
                $this->sendInApp(
                    recipientId: $tech->id,
                    title: "🚨 ESKALASI SLA: {$code} Telah Terlambat!",
                    message: "Pekerjaan \"{$title}\" ({$custName}) {$overdueText}! Segera tangani dan koordinasikan dengan tim supervisor.",
                    data: [
                        'url' => route('technician.my-jobs.index'),
                        'icon' => 'error',
                        'color' => 'rose',
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                    ],
                    event: 'sla_breached',
                    entityType: $entityType,
                    entityId: $entityId
                );

                if ($phone = $this->normalizePhone($tech->whatsapp ?? $tech->phone)) {
                    $waText = "🚨 *ESKALASI SLA OPERASIONAL (TERLAMBAT)*\n\n"
                        . "Halo *{$tech->name}*,\n"
                        . "Pekerjaan berikut telah *MELEWATI BATAS WAKTU SLA*:\n"
                        . "• No: *{$code}*\n"
                        . "• Pelanggan: {$custName}\n"
                        . "• Perihal: {$title}\n"
                        . "• Prioritas: *{$priority}*\n"
                        . "• Keterlambatan: *{$overdueText}*\n\n"
                        . "Pekerjaan ini telah dieskalasi ke NOC/Manager. Harap segera berikan progres.";

                    $idempotencyKey = $this->buildIdempotencyKey('wa', 'sla_breached', $entityType, $entityId, $tech->id);
                    $this->sendWhatsApp($phone, $waText, 'sla_breached', ['job_id' => $entityId], $idempotencyKey);
                }

                // Push Notification FCM ke Android Teknisi
                $fcmTitle = "🚨 ESKALASI SLA: {$code} Breached!";
                $fcmBody = "Pekerjaan \"{$title}\" ({$custName}) {$overdueText}! Segera tangani.";
                $this->sendFcmPush(
                    recipientId: $tech->id,
                    title: $fcmTitle,
                    body: $fcmBody,
                    data: [
                        'type' => 'sla_breached',
                        'channel_id' => 'channel_sla',
                        'entity_type' => $entityType,
                        'entity_id' => (string) $entityId,
                        'work_order_id' => $entityType === 'work_order' ? (string) $entityId : '',
                        'deep_link' => $entityType === 'work_order' ? 'dsbilling://work-orders/' . $entityId : 'dsbilling://tickets/' . $entityId,
                    ],
                    channelId: 'channel_sla',
                    idempotencyKey: $this->buildIdempotencyKey('fcm', 'sla_breached', $entityType, $entityId, $tech->id)
                );
            }

            // 2. Eskalasi ke Supervisor / NOC / Manager (In-App + WA jika nomor tersedia)
            $supervisors = $this->getSupervisorsForBranch($job->branch_id ?? null);
            foreach ($supervisors as $supervisor) {
                $this->sendInApp(
                    recipientId: $supervisor->id,
                    title: "🚨 ESKALASI SLA: {$code} Breached!",
                    message: "Pekerjaan \"{$title}\" ({$custName}) {$overdueText}. Teknisi: " . ($tech?->name ?? 'Belum Ditugaskan') . ". Cabang: {$branchName}",
                    data: [
                        'url' => $actionUrl,
                        'icon' => 'error',
                        'color' => 'rose',
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                    ],
                    event: 'sla_escalation_supervisor',
                    entityType: $entityType,
                    entityId: $entityId
                );

                if ($phone = $this->normalizePhone($supervisor->whatsapp ?? $supervisor->phone)) {
                    $waText = "🚨 *ESKALASI SLA PELANGGARAN WAKTU*\n\n"
                        . "Kepada Yth. *{$supervisor->name}*,\n"
                        . "Pekerjaan lapangan telah melewati target SLA:\n"
                        . "• No: *{$code}*\n"
                        . "• Pelanggan: {$custName}\n"
                        . "• Perihal: {$title}\n"
                        . "• Prioritas: *{$priority}*\n"
                        . "• Teknisi: " . ($tech?->name ?? 'Belum Ditugaskan') . "\n"
                        . "• Cabang: {$branchName}\n"
                        . "• Keterlambatan: *{$overdueText}*\n\n"
                        . "Tinjau Sekarang: " . $actionUrl;

                    $idempotencyKey = $this->buildIdempotencyKey('wa', 'sla_escalation', $entityType, $entityId, $supervisor->id);
                    $this->sendWhatsApp($phone, $waText, 'sla_escalation', ['job_id' => $entityId], $idempotencyKey);
                }
            }

            // 3. Telegram Urgent Alarm ke NOC Broadcast
            $tgText = "🚨 <b>ESKALASI SLA OPERASIONAL (BREACHED)</b>\n\n"
                . "📋 <b>No Pekerjaan:</b> {$code}\n"
                . "👤 <b>Pelanggan:</b> " . htmlspecialchars($custName) . "\n"
                . "📌 <b>Perihal:</b> " . htmlspecialchars($title) . "\n"
                . "⚡ <b>Prioritas:</b> {$priority}\n"
                . "🏢 <b>Cabang:</b> " . htmlspecialchars($branchName) . "\n"
                . "👷 <b>Teknisi:</b> " . htmlspecialchars($tech?->name ?? 'Belum Ditugaskan') . "\n"
                . "⚠️ <b>Status:</b> <b>{$overdueText}</b>\n"
                . "⏰ <b>Target SLA:</b> " . ($metrics['due_at']?->format('d/m/Y H:i') ?? '-') . "\n\n"
                . "🔗 <a href=\"{$actionUrl}\">Lihat Detail Pekerjaan</a>";

            $tgKey = $this->buildIdempotencyKey('tg', 'sla_breached', $entityType, $entityId);
            $this->sendTelegramBroadcast('sla_breached', $tgText, $tgKey);

        } catch (\Throwable $e) {
            Log::warning("[FieldServiceNotification] notifySlaBreached gagal: {$e->getMessage()}");
        }
    }
}
