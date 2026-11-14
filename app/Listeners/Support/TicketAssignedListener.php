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
            $ticket = Ticket::with(['customer', 'assignedTo'])->find((int) $event->ticketId);
            if (!$ticket) return;

            $technician = User::find((int) $event->assigneeId);
            if (!$technician) return;

            $customer = $ticket->customer;
            $customerName = $customer?->name 
                ?? ($ticket->installation ? ('PSB: ' . ($ticket->installation->customer_name ?: 'Calon Pelanggan')) : 'Pelanggan / Objek');

            // ── 1. In-App Database Notification ke Teknisi (Portal Teknisi & Android) ──
            try {
                \App\Models\Notification\Notification::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_id' => $technician->id,
                    'title' => 'Tugas Baru Tiket #' . $ticket->id,
                    'message' => "Anda ditugaskan menangani tiket: {$ticket->title} ({$customerName})",
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
                    $code = !empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id);
                    $text = $this->template->ticketAssigned([
                        'ticket_code'      => $code,
                        'ticket_id'        => $ticket->id,
                        'title'            => $ticket->title,
                        'category'         => $ticket->category ?? 'internet',
                        'priority'         => $ticket->priority ?? 'medium',
                        'description'      => $ticket->description ?? '',
                        'customer_name'    => $customerName,
                        'customer_address' => $customer?->address ?? '-',
                        'customer_phone'   => $this->normalizePhone($customer?->phone ?? '') ?: '',
                        'technician_name'  => $technician->name,
                    ]);

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
                        $code = !empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id);
                        $groupText = $this->template->ticketAssigned([
                            'ticket_code'      => $code,
                            'ticket_id'        => $ticket->id,
                            'title'            => $ticket->title,
                            'category'         => $ticket->category ?? 'internet',
                            'priority'         => $ticket->priority ?? 'medium',
                            'customer_name'    => $customer?->name ?? 'Pelanggan',
                            'customer_address' => $customer?->address ?? '-',
                            'technician_name'  => $technician->name,
                        ]);

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
                        $code = !empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id);
                        $telegram->sendTicketNotification([
                            'ticket_code'      => $code,
                            'ticket_id'        => $ticket->id,
                            'title'            => $ticket->title,
                            'customer_name'    => $customer?->name ?? 'Pelanggan',
                            'customer_address' => $customer?->address ?? '-',
                            'category'         => $ticket->category ?? 'internet',
                            'priority'         => $ticket->priority ?? 'medium',
                            'status'           => $ticket->status ?? 'in_progress',
                            'technician_name'  => $technician->name,
                            'updated_by'       => 'Sistem Penugasan',
                            'note'             => "Tiket ditugaskan ke {$technician->name}",
                        ], 'updated');
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
