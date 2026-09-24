<?php

namespace App\Notifications\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'ticket_resolved',
            'ticket_id'   => $this->ticket->id,
            'title'       => $this->ticket->title,
            'message'     => "✅ Tiket #{$this->ticket->id} telah diselesaikan: {$this->ticket->title}",
            'url'         => route('support.ticket'),
            'icon'        => 'check-circle',
            'color'       => 'green',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
