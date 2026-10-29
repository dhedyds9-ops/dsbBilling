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

            // ── 1. WA Konfirmasi ke Pelanggan ─────────────────────────────
            try {
                $custPhone = $this->normalizePhone($customer?->phone ?? null);
                if ($custPhone && $customer) {
                    $text = $this->template->ticketResolved([
                        'ticket_id'       => $ticket->id,
                        'title'           => $ticket->title,
                        'customer_name'   => $customer->name,
                        'technician_name' => $technician?->name ?? 'Tim Teknis',
                    ]);
                    $msg = new WaOutgoingMessage(
                        toPhone: $custPhone,
                        text: $text,
                        category: 'support',
                        priorityHigh: true,
                        context: ['ticket_id' => $ticket->id, 'type' => 'ticket_resolved'],
                    );
                    SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
                }
            } catch (\Throwable $ce) {
                Log::warning('[TicketResolvedListener] Gagal notif pelanggan: ' . $ce->getMessage());
            }

            // ── 2. In-App Notif ke teknisi (konfirmasi selesai) ───────────
            if ($technician) {
                try {
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
                } catch (\Throwable $ne) {
                    Log::warning('[TicketResolvedListener] Gagal in-app notif: ' . $ne->getMessage());
                }
            }

            // ── 3. WhatsApp Notification ke Grup WA Tiket / NOC ───────
            try {
                $waConn = \App\Models\Setting::getValue('whatsapp.connection', []);
                $ticketGroupId = trim((string)($waConn['ticket_group_id'] ?? ''));
                if ($ticketGroupId !== '') {
                    $groupText = "✅ *TIKET SELESAI (RESOLVED)*\n\n"
                        . "No. Tiket : *#{$ticket->id}*\n"
                        . "Judul     : {$ticket->title}\n"
                        . "Pelanggan : " . ($customer?->name ?? 'Pelanggan') . "\n"
                        . "👨‍🔧 Teknisi  : " . ($technician?->name ?? 'Tim Teknis') . "\n\n"
                        . "Tiket telah diselesaikan dengan sukses.";

                    $msg = new WaOutgoingMessage(
                        toPhone: $ticketGroupId,
                        text: $groupText,
                        category: 'support',
                        priorityHigh: true,
                        context: ['ticket_id' => $ticket->id, 'type' => 'ticket_resolved_group'],
                    );
                    SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
                }
            } catch (\Throwable $ge) {
                Log::error('[TicketResolvedListener] Gagal kirim ke Grup WA: ' . $ge->getMessage());
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
