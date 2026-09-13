<?php

namespace App\Listeners\Support;

use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\Notifications\WhatsApp\WaTemplateEngine;
use App\Jobs\Notifications\SendWaMessageJob;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use Illuminate\Support\Facades\Log;
use Src\Domain\Support\Events\TicketCreatedEvent;

class TicketCreatedListener
{
    public function __construct(
        private readonly WaTemplateEngine $template,
    ) {}

    public function handle(TicketCreatedEvent $event): void
    {
        try {
            $ticket = Ticket::with(['customer'])->find((int) $event->ticketId);
            if (!$ticket) return;

            $customer = $ticket->customer;

            // ── 1. Kirim notifikasi ke semua Admin/NOC ────────────────
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'noc']);
            })->get();

            foreach ($admins as $admin) {
                // In-App notification
                \App\Models\Notification\Notification::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_id' => $admin->id,
                    'title' => 'Tiket Support Baru',
                    'message' => "Tiket #{$ticket->id} dari {$ticket->customer->name} telah dibuat.",
                    'type' => 'in_app',
                    'status' => 'pending',
                    'data' => [
                        'type' => 'ticket_created',
                        'ticket_id' => $ticket->id,
                        'url' => route('support.ticket.show', $ticket->id),
                        'icon' => 'support_agent',
                        'color' => 'blue'
                    ]
                ]);

                // WA ke admin jika punya nomor
                $adminPhone = $this->normalizePhone($admin->phone ?? $admin->whatsapp ?? null);
                if ($adminPhone) {
                    $text = $this->template->ticketCreated([
                        'ticket_id'     => $ticket->id,
                        'title'         => $ticket->title,
                        'category'      => $ticket->category ?? '-',
                        'priority'      => $ticket->priority ?? 'medium',
                        'customer_name' => $customer?->name ?? 'Pelanggan',
                        'created_by'    => 'Portal Pelanggan',
                    ]);
                    $msg = new WaOutgoingMessage(
                        to: $adminPhone,
                        text: $text,
                        context: ['ticket_id' => $ticket->id, 'type' => 'ticket_created'],
                    );
                    SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
                }
            }

            // ── 2. Konfirmasi WA ke Pelanggan ─────────────────────────
            $custPhone = $this->normalizePhone($customer?->phone ?? null);
            if ($custPhone && $customer) {
                $text = "🎫 *TIKET ANDA DITERIMA*\n\n"
                    . "Halo *{$customer->name}*,\n\n"
                    . "Tiket support Anda telah berhasil dibuat:\n\n"
                    . "No. Tiket : *#{$ticket->id}*\n"
                    . "Judul     : {$ticket->title}\n\n"
                    . "Tim kami akan segera menangani keluhan Anda.\n"
                    . "Terima kasih 🙏\n— dsBilling Support";
                $msg = new WaOutgoingMessage(
                    to: $custPhone,
                    text: $text,
                    context: ['ticket_id' => $ticket->id, 'type' => 'ticket_ack'],
                );
                SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
            }

            Log::info('[TicketCreatedListener] Notifikasi tiket baru terkirim', ['ticket_id' => $ticket->id]);

        } catch (\Throwable $e) {
            Log::error('[TicketCreatedListener] Gagal: ' . $e->getMessage(), [
                'ticket_id' => $event->ticketId,
            ]);
        }
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
        if (!str_starts_with($phone, '62')) $phone = '62' . $phone;
        return strlen($phone) >= 10 ? $phone : null;
    }
}
