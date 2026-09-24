<?php

namespace App\Notifications\Support;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
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
            'type'         => 'ticket_assigned',
            'ticket_id'    => $this->ticket->id,
            'title'        => $this->ticket->title,
            'priority'     => $this->ticket->priority ?? 'medium',
            'category'     => $this->ticket->category ?? '-',
            'customer_id'  => $this->ticket->customer_id,
            'message'      => "Tiket #{$this->ticket->id} – {$this->ticket->title} ditugaskan kepada Anda.",
            'url'          => method_exists($notifiable, 'hasRole') && $notifiable->hasRole('technician') 
                                ? route('technician.my-jobs.troubleshooting') 
                                : route('support.ticket'),
            'icon'         => 'ticket',
            'color'        => 'blue',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
