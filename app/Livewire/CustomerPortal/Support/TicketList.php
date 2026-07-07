<?php

namespace App\Livewire\CustomerPortal\Support;

use App\Services\CustomerPortal\CustomerSupportService;
use Livewire\Component;
use Livewire\WithPagination;

class TicketList extends Component
{
    use WithPagination;

    public function render(CustomerSupportService $supportService)
    {
        $tickets = $supportService->getPaginatedTickets(auth()->id());
        return view('livewire.customer-portal.support.ticket-list', compact('tickets'));
    }
}
