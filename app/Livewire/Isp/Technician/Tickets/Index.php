<?php

namespace App\Livewire\Isp\Technician\Tickets;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Support\Ticket;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Src\Domain\Support\Events\TicketResolvedEvent;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    #[Url]
    public string $activeTab = 'open'; // open, resolved

    #[Url]
    public string $scope = 'my'; // 'my' (Tiket Saya), 'all' (Semua Tiket - khusus admin)

    #[Url]
    public string $search = '';

    public bool $showResolveModal = false;
    public bool $showEditResolutionModal = false;
    public bool $showProgressModal = false;

    public ?int $selectedTicketId = null;
    public ?string $selectedTicketCode = null;
    public ?string $selectedTicketTitle = null;
    public ?string $selectedTicketCustomer = null;
    public ?int $selectedTicketCustomerId = null;
    public string $resolution_notes = '';
    public string $edit_resolution_status = 'resolved';

    // Upload Foto Bukti Lapangan
    public $upload_photos = [];

    // Edit Progress Lapangan Sebelum Selesai
    public ?int $progressTicketId = null;
    public string $progress_status = 'in_progress';
    public string $progress_notes = '';
    public string $progress_category = '';

    private function isAdminOversight(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['administrator', 'superadmin', 'manager', 'admin'])
            || in_array(strtolower((string) ($user->role ?? '')), ['administrator', 'superadmin', 'manager', 'admin'], true);
    }

    private function canOperateTicket(Ticket $ticket): bool
    {
        if ($this->isAdminOversight()) {
            return true;
        }

        $userId = (int) Auth::id();
        if ((int) $ticket->assigned_to === $userId) {
            return true;
        }

        // Jika tiket belum ditugaskan ke teknisi manapun, otomatis klaim ke teknisi yang sedang login
        if (empty($ticket->assigned_to)) {
            $ticket->update(['assigned_to' => $userId]);
            return true;
        }

        return false;
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

    public function setScope(string $scope): void
    {
        $this->scope = in_array($scope, ['my', 'all'], true) ? $scope : 'my';
        $this->resetPage();
    }

    public function startTicket(int $id): void
    {
        $ticket = Ticket::withoutGlobalScopes()->findOrFail($id);

        if (!$this->canOperateTicket($ticket)) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
            session()->flash('success', 'Status tiket diperbarui menjadi Sedang Diproses.');
        }
    }

    public function updateCustomerCoordinates(int $customerId, $latitude, $longitude): void
    {
        $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->find($customerId);
        if ($customer) {
            $customer->update([
                'latitude' => round((float) $latitude, 8),
                'longitude' => round((float) $longitude, 8),
            ]);
            session()->flash('success', "Titik koordinat pelanggan {$customer->name} berhasil diperbarui dari GPS HP Anda!");
        } else {
            session()->flash('error', "Pelanggan tidak ditemukan.");
        }
    }

    public function openProgressModal(int $id): void
    {
        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);

        if (!$this->canOperateTicket($ticket)) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        $this->progressTicketId = $ticket->id;
        $this->selectedTicketCode = !empty($ticket->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6)))
            : ('#' . $ticket->id);
        $this->selectedTicketTitle = $ticket->title;
        $this->selectedTicketCustomer = $ticket->customer?->name ?? 'Pelanggan #' . ($ticket->customer_id ?? '-');
        $this->selectedTicketCustomerId = $ticket->customer_id;
        $this->progress_status = $ticket->status ?? 'in_progress';
        $this->progress_notes = (string) ($ticket->description ?? '');
        $this->progress_category = (string) ($ticket->category ?? '');
        $this->showProgressModal = true;
    }

    public function saveProgress(): void
    {
        if (!$this->progressTicketId) return;

        $ticket = Ticket::withoutGlobalScopes()->findOrFail($this->progressTicketId);
        if (!$this->canOperateTicket($ticket)) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        $ticket->update([
            'status' => in_array($this->progress_status, ['open', 'in_progress', 'pending_customer', 'pending_material']) ? $this->progress_status : 'in_progress',
            'description' => trim($this->progress_notes) !== '' ? trim($this->progress_notes) : $ticket->description,
            'category' => trim($this->progress_category) !== '' ? trim($this->progress_category) : $ticket->category,
        ]);

        $code = $this->selectedTicketCode ?? ('#' . $ticket->id);
        $this->showProgressModal = false;
        $this->progressTicketId = null;
        session()->flash('success', "Progress pekerjaan tiket {$code} berhasil diperbarui.");
    }

    public function openResolveModal(int $id): void
    {
        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);

        if (!$this->canOperateTicket($ticket)) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        $this->selectedTicketId = $ticket->id;
        $this->selectedTicketCode = !empty($ticket->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6)))
            : ('#' . $ticket->id);
        $this->selectedTicketTitle = $ticket->title;
        $this->selectedTicketCustomer = $ticket->customer?->name ?? 'Pelanggan #' . ($ticket->customer_id ?? '-');
        $this->selectedTicketCustomerId = $ticket->customer_id;
        $this->resolution_notes = (string) ($ticket->resolution_notes ?? '');
        $this->upload_photos = [];
        $this->showResolveModal = true;
    }

    public function removePhoto(int $index): void
    {
        if (isset($this->upload_photos[$index])) {
            unset($this->upload_photos[$index]);
            $this->upload_photos = array_values($this->upload_photos);
        }
    }

    public function confirmResolve(): void
    {
        if (!$this->selectedTicketId) {
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()->findOrFail($this->selectedTicketId);

        if (!$this->canOperateTicket($ticket)) {
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

        // Upload dan simpan foto bukti pekerjaan jika ada
        $photos = is_array($ticket->photos) ? $ticket->photos : [];
        if (!empty($this->upload_photos)) {
            foreach ($this->upload_photos as $photo) {
                if ($photo) {
                    $path = $photo->store('tickets/' . $ticket->id, 'public');
                    $photos[] = 'storage/' . $path;
                }
            }
        }
        if (Schema::hasColumn('tickets', 'photos')) {
            $payload['photos'] = $photos;
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
        $this->upload_photos = [];

        session()->flash('success', "Tiket {$code} berhasil diselesaikan.");
    }

    public function openEditResolutionModal(int $id): void
    {
        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);

        if (!$this->canOperateTicket($ticket)) {
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

        if (!$this->canOperateTicket($ticket)) {
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
        $isAllScope = ($this->scope === 'all' && $isAdmin);
        $search = trim($this->search);

        // Secara default HANYA tampilkan tiket yang ditugaskan kepada teknisi yang sedang login
        $baseScope = fn ($q) => $q->when(!$isAllScope, fn ($sq) => $sq->where('assigned_to', $userId));

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
            'resolvedCount',
            'isAdmin'
        ));
    }
}
