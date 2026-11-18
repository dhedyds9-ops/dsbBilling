<?php

namespace App\Listeners\Support;

use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\Notifications\WhatsApp\WaTemplateEngine;
use App\Jobs\Notifications\SendWaMessageJob;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use Illuminate\Support\Facades\Log;
use Src\Domain\Support\Events\TicketAssignedEvent;

class TicketAssignedListener
{
    public function __construct(
        private readonly WaTemplateEngine $template,
    ) {}

    public function handle(TicketAssignedEvent $event): void
    {
        try {
            $ticket = Ticket::with(['customer', 'assignedTo', 'installation.survey'])->find((int) $event->ticketId);
            if (!$ticket) return;

            $technician = User::find((int) $event->assigneeId);
            if (!$technician) return;

            $payload = $ticket->getNotificationPayload([
                'technician_name' => $technician->name,
            ]);

            $targetDisplayName = $payload['is_network_object']
                ? ($payload['object_name'] ?: 'Infrastruktur Jaringan')
                : ($payload['customer_name'] ?: 'Pelanggan');

            // ── 1. In-App Database Notification ke Teknisi (Portal Teknisi & Android) ──
            try {
                \App\Models\Notification\Notification::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_id' => $technician->id,
                    'title' => 'Tugas Baru Tiket #' . $ticket->id,
                    'message' => "Anda ditugaskan menangani tiket: {$ticket->title} ({$targetDisplayName})",
                    'type' => 'in_app',
                    'status' => 'pending',
                    'sent_at' => now(),
                    'data' => [
                        'type' => 'ticket_assigned',
                        'ticket_id' => $ticket->id,
                        'url' => route('technician.my-jobs.index'),
                        'icon' => 'build',
                        'color' => 'yellow'
                    ]
                ]);
            } catch (\Throwable $ne) {
                Log::warning('[TicketAssignedListener] Gagal membuat in-app notif teknisi: ' . $ne->getMessage());
            }

            // ── 2. WhatsApp Notification Pribadi ke Teknisi ──────────────────
            try {
                $techPhone = $this->normalizePhone($technician->phone ?? $technician->whatsapp ?? null);
                if ($techPhone) {
                    $text = $this->template->ticketAssigned($payload);
                    \App\Services\Notifications\WhatsApp\WhatsAppDirectSender::send($techPhone, $text);
                }
            } catch (\Throwable $te) {
                Log::warning('[TicketAssignedListener] Gagal kirim WA teknisi: ' . $te->getMessage());
            }

            // ── 2. WhatsApp Notification ke Grup WA Tiket / NOC ───────
            // Cegah duplikasi 2x pesan jika tiket baru saja dibuat (<15 detik),
            // karena TicketCreatedListener sudah menyiarkan "TIKET BARU" lengkap dengan nama teknisinya.
            $isNewlyCreated = $ticket->created_at && $ticket->created_at->diffInSeconds(now()) <= 15;

            try {
                if (!$isNewlyCreated) {
                    $waConn = \App\Models\Setting::getValue('whatsapp.connection', []);
                    $ticketGroupId = trim((string)($waConn['ticket_group_id'] ?? ''));
                    if ($ticketGroupId !== '') {
                        $groupText = $this->template->ticketAssigned($payload);

                        \App\Services\Notifications\WhatsApp\WhatsAppDirectSender::send($ticketGroupId, $groupText);
                        Log::info('[TicketAssignedListener] Notifikasi tiket assigned dikirim ke Grup WA', [
                            'ticket_id' => $ticket->id,
                            'group_id'  => $ticketGroupId,
                        ]);
                    }
                }
            } catch (\Throwable $ge) {
                Log::error('[TicketAssignedListener] Gagal kirim ke Grup WA: ' . $ge->getMessage(), [
                    'ticket_id' => $ticket->id,
                ]);
            }

            // ── 3. Telegram Bot Notification ─────────────────────────
            try {
                if (!$isNewlyCreated) {
                    $telegram = app(\App\Services\Telegram\TelegramService::class);
                    if ($telegram->isEnabled()) {
                        $telegram->sendTicketNotification(array_merge($payload, [
                            'updated_by' => 'Sistem Penugasan',
                            'note'       => "Tiket ditugaskan ke {$technician->name}",
                        ]), 'updated');
                    }
                }
            } catch (\Throwable $tge) {
                Log::warning('[TicketAssignedListener] Gagal notif Telegram: ' . $tge->getMessage());
            }

        } catch (\Throwable $e) {
            Log::error('[TicketAssignedListener] Gagal: ' . $e->getMessage(), [
                'ticket_id' => $event->ticketId,
            ]);
        }
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        return strlen($phone) >= 10 ? $phone : null;
    }
}
