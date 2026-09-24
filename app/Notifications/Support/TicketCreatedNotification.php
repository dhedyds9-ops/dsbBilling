<?php

namespace App\Notifications\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $prio = $this->ticket->priority ?? 'medium';
        $prioEmoji = match($prio) { 'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', default => '🟢' };
        return [
            'type'        => 'ticket_created',
            'ticket_id'   => $this->ticket->id,
            'title'       => $this->ticket->title,
            'priority'    => $prio,
            'category'    => $this->ticket->category ?? '-',
            'customer_id' => $this->ticket->customer_id,
            'message'     => "{$prioEmoji} Tiket baru #{$this->ticket->id}: {$this->ticket->title}",
            'url'         => route('support.ticket'),
            'icon'        => 'ticket',
            'color'       => 'amber',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
