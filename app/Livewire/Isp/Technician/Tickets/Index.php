<?php

namespace App\Livewire\Isp\Technician\Tickets;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Support\Ticket;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'open'; // open, resolved

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function markResolved($id)
    {
        $ticket = Ticket::where('assigned_to', Auth::id())->findOrFail($id);
        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
        
        \Illuminate\Support\Facades\Event::dispatch(
            \Src\Domain\Support\Events\TicketResolvedEvent::create(
                (string)$ticket->id,
                (string)Auth::id(),
                'Diselesaikan oleh teknisi'
            )
        );

        session()->flash('success', 'Tiket berhasil diselesaikan.');
    }

    public function render()
    {
        $query = Ticket::with(['customer'])
            ->where('assigned_to', Auth::id());

        if ($this->activeTab === 'open') {
            $query->whereIn('status', ['open', 'in_progress', 'pending_customer']);
        } else {
            $query->whereIn('status', ['resolved', 'closed']);
        }

        $tickets = $query->latest('created_at')->paginate(10);

        return view('livewire.isp.technician.tickets.index', compact('tickets'));
    }
}
