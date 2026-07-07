<?php

namespace App\Services\CustomerPortal;

use App\Models\Support\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerSupportService
{
    public function createTicket(int $customerId, array $data): array
    {
        $ticket = Ticket::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customerId,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
        ]);

        return ['success' => true, 'message' => 'Tiket berhasil dibuat', 'ticket_id' => $ticket->id];
    }

    public function getPaginatedTickets(int $customerId, int $perPage = 10): LengthAwarePaginator
    {
        return Ticket::where('customer_id', $customerId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getTicketDetails(int $customerId, int $ticketId): ?Ticket
    {
        return Ticket::where('customer_id', $customerId)
            ->where('id', $ticketId)
            ->first();
    }
}
