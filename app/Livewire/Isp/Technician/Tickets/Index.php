<?php

namespace App\Livewire\Isp\Technician\Tickets;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Support\Ticket;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Src\Domain\Support\Events\TicketResolvedEvent;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'open'; // open, resolved

    #[Url]
    public string $search = '';

    public bool $showResolveModal = false;
    public bool $showEditResolutionModal = false;
    public ?int $selectedTicketId = null;
    public ?string $selectedTicketCode = null;
    public ?string $selectedTicketTitle = null;
    public ?string $selectedTicketCustomer = null;
    public string $resolution_notes = '';
    public string $edit_resolution_status = 'resolved';

    private function isAdminOversight(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['administrator', 'superadmin'])
            && strtoupper((string) $user->job_function) !== 'TECHNICIAN';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function switchTab($tab): void
    {
        $this->activeTab = in_array($tab, ['open', 'resolved'], true) ? $tab : 'open';
        $this->resetPage();
    }

    public function startTicket(int $id): void
    {
        $ticket = Ticket::withoutGlobalScopes()->findOrFail($id);

        if (!$this->isAdminOversight() && (int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
            session()->flash('success', 'Status tiket diperbarui menjadi Sedang Diproses.');
        }
    }

    public function openResolveModal(int $id): void
    {
        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);

        if (!$this->isAdminOversight() && (int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        $this->selectedTicketId = $ticket->id;
        $this->selectedTicketCode = !empty($ticket->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6)))
            : ('#' . $ticket->id);
        $this->selectedTicketTitle = $ticket->title;
        $this->selectedTicketCustomer = $ticket->customer?->name ?? 'Pelanggan #' . ($ticket->customer_id ?? '-');
        $this->resolution_notes = (string) ($ticket->resolution_notes ?? '');
        $this->showResolveModal = true;
    }

    public function confirmResolve(): void
    {
        if (!$this->selectedTicketId) {
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()->findOrFail($this->selectedTicketId);

        if (!$this->isAdminOversight() && (int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        $note = trim($this->resolution_notes) !== ''
            ? trim($this->resolution_notes)
            : 'Diselesaikan oleh teknisi melalui portal gangguan';

        $payload = [
            'status' => 'resolved',
            'resolved_at' => now(),
        ];
        if (Schema::hasColumn('tickets', 'resolution_notes')) {
            $payload['resolution_notes'] = $note;
        }

        $ticket->update($payload);

        Event::dispatch(
            TicketResolvedEvent::create(
                (string) $ticket->id,
                (string) Auth::id(),
                $note
            )
        );

        $code = $this->selectedTicketCode ?? ('#' . $ticket->id);
        $this->showResolveModal = false;
        $this->selectedTicketId = null;
        $this->resolution_notes = '';

        session()->flash('success', "Tiket {$code} berhasil diselesaikan.");
    }

    public function openEditResolutionModal(int $id): void
    {
        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);

        if (!$this->isAdminOversight() && (int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        if ($ticket->status === 'closed') {
            session()->flash('error', 'Tiket sudah ditutup permanen (Closed) oleh Admin dan tidak dapat diubah kembali.');
            return;
        }

        $this->selectedTicketId = $ticket->id;
        $this->selectedTicketCode = !empty($ticket->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6)))
            : ('#' . $ticket->id);
        $this->selectedTicketTitle = $ticket->title;
        $this->selectedTicketCustomer = $ticket->customer?->name ?? 'Pelanggan #' . ($ticket->customer_id ?? '-');
        $this->resolution_notes = (string) ($ticket->resolution_notes ?? '');
        $this->edit_resolution_status = 'resolved';
        $this->showEditResolutionModal = true;
    }

    public function saveResolutionUpdate(): void
    {
        if (!$this->selectedTicketId) {
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()->findOrFail($this->selectedTicketId);

        if (!$this->isAdminOversight() && (int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        if ($ticket->status === 'closed') {
            $this->showEditResolutionModal = false;
            session()->flash('error', 'Tiket sudah ditutup permanen (Closed) oleh Admin.');
            return;
        }

        $code = $this->selectedTicketCode ?? ('#' . $ticket->id);
        $hasNotesCol = Schema::hasColumn('tickets', 'resolution_notes');

        if ($this->edit_resolution_status === 'in_progress') {
            $payload = [
                'status' => 'in_progress',
                'resolved_at' => null,
            ];
            if ($hasNotesCol) {
                $payload['resolution_notes'] = trim($this->resolution_notes) !== '' ? trim($this->resolution_notes) : null;
            }
            $ticket->update($payload);

            $this->showEditResolutionModal = false;
            $this->selectedTicketId = null;
            $this->activeTab = 'open';
            session()->flash('success', "Tiket {$code} dibuka kembali ke Tugas Aktif.");
            return;
        }

        $note = trim($this->resolution_notes) !== ''
            ? trim($this->resolution_notes)
            : 'Diselesaikan oleh teknisi melalui portal gangguan';

        $payload = [
            'status' => 'resolved',
            'resolved_at' => $ticket->resolved_at ?? now(),
        ];
        if ($hasNotesCol) {
            $payload['resolution_notes'] = $note;
        }
        $ticket->update($payload);

        $this->showEditResolutionModal = false;
        $this->selectedTicketId = null;
        session()->flash('success', "Catatan penyelesaian tiket {$code} berhasil diperbarui.");
    }

    public function markResolved($id): void
    {
        $this->openResolveModal((int) $id);
    }

    public function render()
    {
        $userId = Auth::id();
        $isAdmin = $this->isAdminOversight();
        $search = trim($this->search);

        $baseScope = fn ($q) => $q->when(!$isAdmin, fn ($sq) => $sq->where('assigned_to', $userId));

        $openCount = Ticket::withoutGlobalScopes()
            ->tap($baseScope)
            ->whereIn('status', ['open', 'in_progress', 'pending_customer'])
            ->count();

        $resolvedCount = Ticket::withoutGlobalScopes()
            ->tap($baseScope)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        $query = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->tap($baseScope);

        if ($this->activeTab === 'open') {
            $query->whereIn('status', ['open', 'in_progress', 'pending_customer']);
        } else {
            $query->whereIn('status', ['resolved', 'closed']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->withoutGlobalScopes()->where(function ($sub) use ($search) {
                          $sub->where('name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%")
                              ->orWhere('address', 'like', "%{$search}%")
                              ->orWhere('code', 'like', "%{$search}%");
                      });
                  });
            });
        }

        $tickets = $query->latest('created_at')->paginate(10);

        return view('livewire.isp.technician.tickets.index', compact(
            'tickets',
            'openCount',
            'resolvedCount'
        ));
    }
}
