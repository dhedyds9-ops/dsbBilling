<?php

namespace App\Listeners\Support;

use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\Notifications\WhatsApp\WaTemplateEngine;
use App\Jobs\Notifications\SendWaMessageJob;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use Illuminate\Support\Facades\Log;
use Src\Domain\Support\Events\TicketResolvedEvent;

class TicketResolvedListener
{
    public function __construct(
        private readonly WaTemplateEngine $template,
    ) {}

    public function handle(TicketResolvedEvent $event): void
    {
        try {
            $ticket = Ticket::with(['customer', 'assignedTo'])->find((int) $event->ticketId);
            if (!$ticket) return;

            $customer   = $ticket->customer;
            $technician = $ticket->assignedTo;

            // ── WA Konfirmasi ke Pelanggan ─────────────────────────────
            $custPhone = $this->normalizePhone($customer?->phone ?? null);
            if ($custPhone && $customer) {
                $text = $this->template->ticketResolved([
                    'ticket_id'       => $ticket->id,
                    'title'           => $ticket->title,
                    'customer_name'   => $customer->name,
                    'technician_name' => $technician?->name ?? 'Tim Teknis',
                ]);
                $msg = new WaOutgoingMessage(
                    to: $custPhone,
                    text: $text,
                    context: ['ticket_id' => $ticket->id, 'type' => 'ticket_resolved'],
                );
                SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
            }

            // ── In-App Notif ke teknisi (konfirmasi selesai) ───────────
            if ($technician) {
                \App\Models\Notification\Notification::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_id' => $technician->id,
                    'title' => 'Tiket Selesai',
                    'message' => "Tiket #{$ticket->id} yang ditugaskan kepada Anda telah berhasil diselesaikan.",
                    'type' => 'in_app',
                    'status' => 'pending',
                    'data' => [
                        'type' => 'ticket_resolved',
                        'ticket_id' => $ticket->id,
                        'url' => route('technician.my-jobs.troubleshooting'),
                        'icon' => 'check_circle',
                        'color' => 'green'
                    ]
                ]);
            }

            Log::info('[TicketResolvedListener] Notifikasi tiket selesai terkirim', ['ticket_id' => $ticket->id]);

        } catch (\Throwable $e) {
            Log::error('[TicketResolvedListener] Gagal: ' . $e->getMessage());
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
