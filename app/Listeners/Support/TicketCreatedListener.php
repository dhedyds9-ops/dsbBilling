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

            $creator = !empty($event->userId) ? User::find((int) $event->userId) : null;
            $creatorLabel = $creator ? "Admin ({$creator->name})" : 'Portal Pelanggan';

            // ── 1. Kirim notifikasi ke semua Admin/NOC ────────────────
            try {
                $admins = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super-admin', 'admin', 'noc']);
                })->get();

                foreach ($admins as $admin) {
                    // In-App notification
                    \App\Models\Notification\Notification::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'recipient_id' => $admin->id,
                        'title' => 'Tiket Support Baru',
                        'message' => "Tiket #{$ticket->id} dari " . ($ticket->customer?->name ?? 'Pelanggan') . " telah dibuat.",
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
                            'created_by'    => $creatorLabel,
                        ]);
                        $msg = new WaOutgoingMessage(
                            toPhone: $adminPhone,
                            text: $text,
                            category: 'support',
                            priorityHigh: true,
                            context: ['ticket_id' => $ticket->id, 'type' => 'ticket_created_admin'],
                        );
                        SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
                    }
                }
            } catch (\Throwable $ae) {
                Log::warning('[TicketCreatedListener] Gagal notif admin: ' . $ae->getMessage());
            }

            // ── 2. Konfirmasi WA ke Pelanggan ─────────────────────────
            try {
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
                        toPhone: $custPhone,
                        text: $text,
                        category: 'support',
                        priorityHigh: true,
                        context: ['ticket_id' => $ticket->id, 'type' => 'ticket_ack'],
                    );
                    SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');
                }
            } catch (\Throwable $ce) {
                Log::warning('[TicketCreatedListener] Gagal notif pelanggan: ' . $ce->getMessage());
            }

            // ── 3. Kirim notifikasi ke Grup WhatsApp Tiket / NOC ───────
            try {
                $waConn = \App\Models\Setting::getValue('whatsapp.connection', []);
                $ticketGroupId = trim((string)($waConn['ticket_group_id'] ?? ''));
                if ($ticketGroupId !== '') {
                    $groupText = $this->template->ticketCreated([
                        'ticket_id'     => $ticket->id,
                        'title'         => $ticket->title,
                        'category'      => $ticket->category ?? '-',
                        'priority'      => $ticket->priority ?? 'medium',
                        'customer_name' => $customer?->name ?? 'Pelanggan',
                        'created_by'    => $creatorLabel,
                    ]);
                    $msg = new WaOutgoingMessage(
                        toPhone: $ticketGroupId,
                        text: $groupText,
                        category: 'support',
                        priorityHigh: true,
                        context: ['ticket_id' => $ticket->id, 'type' => 'ticket_created_group'],
                    );
                    SendWaMessageJob::dispatch($msg)->onQueue('notifications-wa');

                    Log::info('[TicketCreatedListener] Notifikasi tiket baru terkirim ke Grup WA', [
                        'ticket_id' => $ticket->id,
                        'group_id'  => $ticketGroupId,
                    ]);
                }
            } catch (\Throwable $ge) {
                Log::error('[TicketCreatedListener] Gagal kirim ke Grup WA: ' . $ge->getMessage(), [
                    'ticket_id' => $ticket->id,
                ]);
            }

            Log::info('[TicketCreatedListener] Notifikasi tiket baru diproses', ['ticket_id' => $ticket->id]);

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
