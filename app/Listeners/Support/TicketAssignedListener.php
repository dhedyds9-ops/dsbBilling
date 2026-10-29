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

            // ── 1. WhatsApp Notification ke Teknisi ──────────────────
            try {
                $techPhone = $this->normalizePhone($technician->phone ?? $technician->whatsapp ?? null);
                if ($techPhone) {
                    $text = $this->template->ticketAssigned([
                        'ticket_id'        => $ticket->id,
                        'title'            => $ticket->title,
                        'category'         => $ticket->category ?? '-',
                        'priority'         => $ticket->priority ?? 'medium',
                        'description'      => $ticket->description ?? '',
                        'customer_name'    => $customer?->name ?? 'Pelanggan',
                        'customer_address' => $customer?->address ?? '',
                        'customer_phone'   => $this->normalizePhone($customer?->phone ?? '') ?: '',
                        'technician_name'  => $technician->name,
                    ]);

                    $msg = new WaOutgoingMessage(
                        toPhone: $techPhone,
                        text: $text,
                        category: 'support',
                        priorityHigh: true,
                        context: ['ticket_id' => $ticket->id, 'type' => 'ticket_assigned'],
                    );
                    SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
                }

                // In-App Database Notification ke Teknisi
                \App\Models\Notification\Notification::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_id' => $technician->id,
                    'title' => 'Tiket Ditugaskan',
                    'message' => "Anda telah ditugaskan untuk menangani Tiket #{$ticket->id} (Pelanggan: " . ($ticket->customer?->name ?? 'Pelanggan') . ")",
                    'type' => 'in_app',
                    'status' => 'pending',
                    'data' => [
                        'type' => 'ticket_assigned',
                        'ticket_id' => $ticket->id,
                        'url' => route('technician.my-jobs.troubleshooting'),
                        'icon' => 'build',
                        'color' => 'yellow'
                    ]
                ]);

                Log::info('[TicketAssignedListener] Notifikasi terkirim ke teknisi', [
                    'ticket_id'    => $ticket->id,
                    'technician'   => $technician->name,
                    'tech_phone'   => $techPhone ?? 'tidak ada',
                ]);
            } catch (\Throwable $te) {
                Log::warning('[TicketAssignedListener] Gagal notif teknisi: ' . $te->getMessage());
            }

            // ── 2. WhatsApp Notification ke Grup WA Tiket / NOC ───────
            try {
                $waConn = \App\Models\Setting::getValue('whatsapp.connection', []);
                $ticketGroupId = trim((string)($waConn['ticket_group_id'] ?? ''));
                if ($ticketGroupId !== '') {
                    $groupText = "🔧 *TIKET DITUGASKAN*\n\n"
                        . "No. Tiket : *#{$ticket->id}*\n"
                        . "Judul     : {$ticket->title}\n"
                        . "Pelanggan : " . ($customer?->name ?? 'Pelanggan') . "\n"
                        . "👨‍🔧 Teknisi  : *{$technician->name}*\n"
                        . "Prioritas : " . strtoupper($ticket->priority ?? 'medium') . "\n\n"
                        . "Teknisi telah ditugaskan untuk menangani tiket ini.";

                    $msg = new WaOutgoingMessage(
                        toPhone: $ticketGroupId,
                        text: $groupText,
                        category: 'support',
                        priorityHigh: true,
                        context: ['ticket_id' => $ticket->id, 'type' => 'ticket_assigned_group'],
                    );
                    SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');

                    Log::info('[TicketAssignedListener] Notifikasi tiket assigned ke Grup WA', [
                        'ticket_id' => $ticket->id,
                        'group_id'  => $ticketGroupId,
                    ]);
                }
            } catch (\Throwable $ge) {
                Log::error('[TicketAssignedListener] Gagal kirim ke Grup WA: ' . $ge->getMessage(), [
                    'ticket_id' => $ticket->id,
                ]);
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
