<?php

namespace App\Livewire\CustomerPortal\Support;

use App\Services\CustomerPortal\CustomerSupportService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.customer-app')]
class TicketList extends Component
{
    use WithPagination;

    public function render(CustomerSupportService $supportService)
    {
        $customerId = auth()->user()->customer?->id ?? 0;
        $tickets = $supportService->getPaginatedTickets($customerId);
        return view('livewire.customer-portal.support.ticket-list', compact('tickets'));
    }
}
