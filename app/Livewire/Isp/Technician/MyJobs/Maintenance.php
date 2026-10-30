<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use App\Models\Support\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Src\Domain\Support\Events\TicketResolvedEvent;

#[Layout('layouts.technician-app')]
class Maintenance extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'active'; // active, history

    #[Url]
    public string $search = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public string $modalSource = 'maintenance'; // maintenance, ticket
    public ?int $selectedId = null;
    public string $selectedCode = '';
    public string $selectedTitle = '';
    public string $selectedTarget = '';
    public string $resolution_notes = '';
    public string $edit_status = 'completed';

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

    public function switchTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['active', 'history'], true) ? $tab : 'active';
        $this->resetPage();
    }

    public function startItem(string $source, int $id): void
    {
        $isAdmin = $this->isAdminOversight();
        $uid = (int) Auth::id();

        if ($source === 'maintenance' && Schema::hasTable('support_maintenances')) {
            $row = DB::table('support_maintenances')->where('id', $id)->first();
            if (!$row) {
                return;
            }
            if (!$isAdmin && (int) ($row->assigned_to ?? 0) !== $uid) {
                abort(403, 'Jadwal maintenance ini tidak ditugaskan kepada Anda.');
            }
            DB::table('support_maintenances')->where('id', $id)->update([
                'status' => 'in_progress',
                'updated_by' => $uid,
                'updated_at' => now(),
            ]);
            session()->flash('success', "Maintenance #MT-{$id} mulai dikerjakan.");
            return;
        }

        if ($source === 'ticket') {
            $ticket = Ticket::withoutGlobalScopes()->findOrFail($id);
            if (!$isAdmin && (int) $ticket->assigned_to !== $uid) {
                abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
            }
            $ticket->update(['status' => 'in_progress']);
            session()->flash('success', "Tiket #{$id} mulai dikerjakan.");
        }
    }

    public function openCompleteModal(string $source, int $id, bool $edit = false): void
    {
        $isAdmin = $this->isAdminOversight();
        $uid = (int) Auth::id();

        if ($source === 'maintenance' && Schema::hasTable('support_maintenances')) {
            $row = DB::table('support_maintenances')->where('id', $id)->first();
            if (!$row) {
                return;
            }
            if (!$isAdmin && (int) ($row->assigned_to ?? 0) !== $uid) {
                abort(403, 'Jadwal maintenance ini tidak ditugaskan kepada Anda.');
            }
            $this->modalSource = 'maintenance';
            $this->selectedId = (int) $row->id;
            $this->selectedCode = 'MT-' . $row->id;
            $this->selectedTitle = (string) ($row->title ?? '-');
            $this->selectedTarget = trim(($row->device_name ?: ucfirst((string) ($row->device_type ?? 'Jaringan'))) . ($row->location_name ? ' · ' . $row->location_name : ''));
            $this->resolution_notes = (string) ($row->resolution_notes ?? '');
            $this->isEditing = $edit;
            $this->edit_status = 'completed';
            $this->showModal = true;
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);

        if (!$isAdmin && (int) $ticket->assigned_to !== $uid) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        if ($edit && $ticket->status === 'closed') {
            session()->flash('error', 'Tiket sudah ditutup permanen (Closed) oleh Admin.');
            return;
        }

        $this->modalSource = 'ticket';
        $this->selectedId = $ticket->id;
        $this->selectedCode = !empty($ticket->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6)))
            : ('TKT-' . $ticket->id);
        $this->selectedTitle = (string) $ticket->title;
        $this->selectedTarget = $ticket->customer?->name ?? 'Jaringan Pelanggan';
        $this->resolution_notes = (string) ($ticket->resolution_notes ?? '');
        $this->isEditing = $edit;
        $this->edit_status = 'completed';
        $this->showModal = true;
    }

    public function saveCompletion(): void
    {
        if (!$this->selectedId) {
            return;
        }

        $isAdmin = $this->isAdminOversight();
        $uid = (int) Auth::id();

        if ($this->modalSource === 'maintenance' && Schema::hasTable('support_maintenances')) {
            $row = DB::table('support_maintenances')->where('id', $this->selectedId)->first();
            if (!$row) {
                return;
            }
            if (!$isAdmin && (int) ($row->assigned_to ?? 0) !== $uid) {
                abort(403);
            }

            if ($this->isEditing && $this->edit_status === 'in_progress') {
                DB::table('support_maintenances')->where('id', $row->id)->update([
                    'status' => 'in_progress',
                    'completed_at' => null,
                    'resolution_notes' => trim($this->resolution_notes) !== '' ? trim($this->resolution_notes) : null,
                    'updated_by' => $uid,
                    'updated_at' => now(),
                ]);
                $this->showModal = false;
                $this->activeTab = 'active';
                session()->flash('success', "Maintenance #{$this->selectedCode} dibuka kembali ke Tugas Aktif.");
                return;
            }

            $note = trim($this->resolution_notes) !== ''
                ? trim($this->resolution_notes)
                : 'Pemeliharaan jaringan selesai dikerjakan oleh teknisi';

            DB::table('support_maintenances')->where('id', $row->id)->update([
                'status' => 'completed',
                'completed_at' => $row->completed_at ?? now(),
                'resolution_notes' => $note,
                'updated_by' => $uid,
                'updated_at' => now(),
            ]);

            $this->showModal = false;
            session()->flash('success', $this->isEditing
                ? "Laporan penyelesaian #{$this->selectedCode} berhasil diperbarui."
                : "Maintenance #{$this->selectedCode} berhasil diselesaikan.");
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()->findOrFail($this->selectedId);
        if (!$isAdmin && (int) $ticket->assigned_to !== $uid) {
            abort(403);
        }
        if ($ticket->status === 'closed') {
            $this->showModal = false;
            session()->flash('error', 'Tiket sudah ditutup permanen oleh Admin.');
            return;
        }

        $hasCol = Schema::hasColumn('tickets', 'resolution_notes');

        if ($this->isEditing && $this->edit_status === 'in_progress') {
            $payload = ['status' => 'in_progress', 'resolved_at' => null];
            if ($hasCol) {
                $payload['resolution_notes'] = trim($this->resolution_notes) !== '' ? trim($this->resolution_notes) : null;
            }
            $ticket->update($payload);
            $this->showModal = false;
            $this->activeTab = 'active';
            session()->flash('success', "Tiket {$this->selectedCode} dibuka kembali ke Tugas Aktif.");
            return;
        }

        $note = trim($this->resolution_notes) !== ''
            ? trim($this->resolution_notes)
            : 'Maintenance selesai dikerjakan oleh teknisi';

        $payload = [
            'status' => 'resolved',
            'resolved_at' => $ticket->resolved_at ?? now(),
        ];
        if ($hasCol) {
            $payload['resolution_notes'] = $note;
        }
        $ticket->update($payload);

        if (!$this->isEditing) {
            Event::dispatch(
                TicketResolvedEvent::create(
                    (string) $ticket->id,
                    (string) $uid,
                    $note
                )
            );
        }

        $this->showModal = false;
        session()->flash('success', $this->isEditing
            ? "Catatan penyelesaian {$this->selectedCode} berhasil diperbarui."
            : "Tiket Maintenance {$this->selectedCode} berhasil diselesaikan.");
    }

    public function render()
    {
        $uid = Auth::id();
        $isAdmin = $this->isAdminOversight();
        $search = trim($this->search);
        $items = collect();

        $activeCount = 0;
        $historyCount = 0;

        if (Schema::hasTable('support_maintenances')) {
            $baseMaint = DB::table('support_maintenances')
                ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $uid));

            $activeCount += (clone $baseMaint)->whereIn('status', ['scheduled', 'in_progress'])->count();
            $historyCount += (clone $baseMaint)->whereIn('status', ['completed', 'cancelled'])->count();

            $mq = clone $baseMaint;
            if ($this->activeTab === 'active') {
                $mq->whereIn('status', ['scheduled', 'in_progress']);
            } else {
                $mq->whereIn('status', ['completed', 'cancelled']);
            }

            if ($search !== '') {
                $mq->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('device_name', 'like', "%{$search}%")
                        ->orWhere('location_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            foreach ($mq->orderByDesc('scheduled_date')->orderByDesc('id')->get() as $m) {
                $isDone = in_array($m->status, ['completed', 'cancelled']);
                $sched = trim(($m->scheduled_date ?? '') . ' ' . ($m->scheduled_time ?? '09:00'));

                $items->push((object) [
                    'source' => 'maintenance',
                    'id' => (int) $m->id,
                    'code' => 'MT-' . $m->id,
                    'title' => $m->title ?? 'Pemeliharaan Jaringan',
                    'target_name' => $m->device_name ?: ('Perangkat ' . strtoupper((string) ($m->device_type ?? 'Jaringan'))),
                    'type_label' => strtoupper((string) ($m->type ?? 'PREVENTIVE')),
                    'priority' => $m->priority ?? 'medium',
                    'status' => $m->status ?? 'scheduled',
                    'is_completed' => $isDone,
                    'is_locked' => $m->status === 'cancelled',
                    'location' => $m->location_name ?: 'Lokasi Infrastruktur',
                    'description' => $m->notes,
                    'resolution_notes' => $m->resolution_notes ?? null,
                    'date' => $sched ?: $m->created_at,
                    'completed_at' => $m->completed_at ?? null,
                ]);
            }
        }

        $baseTickets = Ticket::withoutGlobalScopes()
            ->where('category', 'maintenance')
            ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $uid));

        $activeCount += (clone $baseTickets)->whereIn('status', ['open', 'in_progress', 'pending_customer'])->count();
        $historyCount += (clone $baseTickets)->whereIn('status', ['resolved', 'closed'])->count();

        $tq = (clone $baseTickets)->with(['customer' => fn ($q) => $q->withoutGlobalScopes()]);
        if ($this->activeTab === 'active') {
            $tq->whereIn('status', ['open', 'in_progress', 'pending_customer']);
        } else {
            $tq->whereIn('status', ['resolved', 'closed']);
        }

        if ($search !== '') {
            $tq->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->withoutGlobalScopes()->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
            });
        }

        foreach ($tq->latest('created_at')->get() as $t) {
            $isDone = in_array($t->status, ['resolved', 'closed']);
            $code = !empty($t->uuid)
                ? ('TKT-' . strtoupper(substr(str_replace('-', '', $t->uuid), 0, 6)))
                : ('TKT-' . $t->id);

            $items->push((object) [
                'source' => 'ticket',
                'id' => (int) $t->id,
                'code' => $code,
                'title' => $t->title,
                'target_name' => $t->customer?->name ?? 'Jaringan Pelanggan',
                'type_label' => 'TIKET MAINTENANCE',
                'priority' => $t->priority ?? 'medium',
                'status' => $t->status,
                'is_completed' => $isDone,
                'is_locked' => $t->status === 'closed',
                'location' => $t->customer?->address ?? 'Lokasi ODC / ODP / Pelanggan',
                'description' => $t->description,
                'resolution_notes' => $t->resolution_notes ?? null,
                'date' => $t->created_at,
                'completed_at' => $t->resolved_at,
            ]);
        }

        $sorted = $items->sortByDesc(fn ($i) => $i->date ? \Carbon\Carbon::parse($i->date)->timestamp : 0)->values();
        $page = $this->getPage();
        $perPage = 10;

        $jobs = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.isp.technician.my-jobs.maintenance', [
            'jobs' => $jobs,
            'activeCount' => $activeCount,
            'historyCount' => $historyCount,
        ]);
    }
}
