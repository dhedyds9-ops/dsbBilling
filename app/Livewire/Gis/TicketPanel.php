<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class TicketPanel extends Component
{
    use WithPagination;

    public string $filterStatus = 'all';
    public string $filterPriority = 'all';
    public string $filterType = 'all';
    public string $searchQuery = '';
    public bool $showAssignedOnly = false;
    
    public int $pendingCount = 0;
    public int $inProgressCount = 0;
    public int $resolvedCount = 0;

    protected $listeners = [
        'ticketCreated',
        'ticketUpdated',
        'refreshTickets',
    ];

    public function mount()
    {
        $this->loadTicketCounts();
    }

    public function getTicketsProperty()
    {
        return Cache::remember('gis_tickets', 60, function () {
            return $this->fetchTickets();
        });
    }

    private function fetchTickets(): array
    {
        return [
            [
                'id' => 'TKT-2024-001',
                'title' => 'Fiber cut at Segment FK-042',
                'type' => 'fault',
                'priority' => 'critical',
                'status' => 'open',
                'assigned_to' => 'Tech-205',
                'location' => 'Bekasi',
                'affected_customers' => 45,
                'created_at' => now()->subHours(2)->toIso8601String(),
                'due_at' => now()->addHours(2)->toIso8601String(),
            ],
            [
                'id' => 'TKT-2024-002',
                'title' => 'ODP-042 capacity expansion needed',
                'type' => 'capacity',
                'priority' => 'high',
                'status' => 'in_progress',
                'assigned_to' => 'Tech-101',
                'location' => 'Bandung',
                'affected_customers' => 0,
                'created_at' => now()->subDays(1)->toIso8601String(),
                'due_at' => now()->addDays(3)->toIso8601String(),
            ],
            [
                'id' => 'TKT-2024-003',
                'title' => 'New customer installation request',
                'type' => 'installation',
                'priority' => 'normal',
                'status' => 'pending',
                'assigned_to' => null,
                'location' => 'Jakarta Selatan',
                'affected_customers' => 1,
                'created_at' => now()->subHours(5)->toIso8601String(),
                'due_at' => now()->addDays(2)->toIso8601String(),
            ],
            [
                'id' => 'TKT-2024-004',
                'title' => 'Scheduled maintenance OLT-003',
                'type' => 'maintenance',
                'priority' => 'low',
                'status' => 'scheduled',
                'assigned_to' => 'Tech-303',
                'location' => 'Surabaya',
                'affected_customers' => 0,
                'created_at' => now()->subDays(2)->toIso8601String(),
                'due_at' => now()->addDays(5)->toIso8601String(),
            ],
            [
                'id' => 'TKT-2024-005',
                'title' => 'Customer signal issue reported',
                'type' => 'fault',
                'priority' => 'medium',
                'status' => 'resolved',
                'assigned_to' => 'Tech-103',
                'location' => 'Tangerang',
                'affected_customers' => 1,
                'created_at' => now()->subDays(1)->toIso8601String(),
                'resolved_at' => now()->subHours(6)->toIso8601String(),
            ],
        ];
    }

    public function loadTicketCounts()
    {
        $tickets = $this->tickets;
        
        $this->pendingCount = count(array_filter($tickets, fn($t) => $t['status'] === 'pending' || $t['status'] === 'open'));
        $this->inProgressCount = count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress'));
        $this->resolvedCount = count(array_filter($tickets, fn($t) => $t['status'] === 'resolved'));
    }

    public function viewOnMap($ticketId)
    {
        $ticket = array_filter($this->tickets, fn($t) => $t['id'] === $ticketId);
        
        if ($ticket) {
            $this->dispatch('navigateToLocation', location: array_values($ticket)[0]['location']);
        }
    }

    public function assignTicket($ticketId)
    {
        $this->dispatch('openAssignModal', ticketId: $ticketId);
    }

    public function updateStatus($ticketId, $status)
    {
        $this->dispatch('updateTicketStatus', ticketId: $ticketId, status: $status);
    }

    public function refreshTickets()
    {
        Cache::forget('gis_tickets');
        $this->loadTicketCounts();
    }

    public function ticketCreated($ticket)
    {
        $this->refreshTickets();
        $this->dispatch('showNotification', [
            'title' => 'New Ticket',
            'message' => "Ticket {$ticket['id']} has been created",
        ]);
    }

    public function ticketUpdated($ticket)
    {
        $this->refreshTickets();
    }

    public function render()
    {
        return view('livewire.gis.components.ticket-panel', [
            'tickets' => $this->tickets,
            'pendingCount' => $this->pendingCount,
            'inProgressCount' => $this->inProgressCount,
            'resolvedCount' => $this->resolvedCount,
        ]);
    }
}
