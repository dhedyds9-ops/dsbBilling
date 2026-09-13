<?php

namespace App\Livewire\NOC\Alarms;

use App\Livewire\AdminComponent;
use App\Models\Alarm;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    public string $search       = '';
    public string $levelFilter  = 'all';
    public string $statusFilter = 'all';
    public string $sourceFilter = 'all';
    public string $sortField    = 'started_at';
    public string $sortDirection = 'desc';
    public int $perPage = 25;

    public function configure(): void
    {
        }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'alarms';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLevelFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'total'     => Alarm::count(),
            'open'      => Alarm::whereIn('status', ['open', 'acknowledged'])->count(),
            'critical'  => Alarm::where('level', 'critical')->where('status', '!=', 'resolved')->count(),
            'warning'   => Alarm::where('level', 'warning')->where('status', '!=', 'resolved')->count(),
            'resolved'  => Alarm::where('status', 'resolved')->count(),
        ];
    }

    #[Computed]
    public function alarms(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Alarm::with([
            'acknowledgedBy:id,name',
        ])
        ->when($this->levelFilter !== 'all', fn ($q) => $q->where('level', $this->levelFilter))
        ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
        ->when($this->sourceFilter !== 'all', fn ($q) => $q->where('source_type', $this->sourceFilter))
        ->when(!empty($this->search), function ($q) {
            $q->where(function ($sq) {
                $sq->where('title', 'like', "%{$this->search}%")
                   ->orWhere('description', 'like', "%{$this->search}%")
                   ->orWhere('source_name', 'like', "%{$this->search}%");
            });
        })
        ->when(
            in_array($this->sortField, ['started_at', 'level', 'status', 'source_name', 'title']),
            fn ($q) => $q->orderBy($this->sortField, $this->sortDirection)
        )
        ->paginate($this->perPage);
    }

    #[Computed]
    public function sourceTypes(): array
    {
        return Alarm::select('source_type')
            ->distinct()
            ->whereNotNull('source_type')
            ->pluck('source_type')
            ->toArray();
    }

    public function acknowledgeAlarm(int $alarmId, string $note = ''): void
    {
        $alarm = Alarm::find($alarmId);
        if (!$alarm || $alarm->status === 'resolved') {
            return;
        }

        $alarm->update([
            'status'            => 'acknowledged',
            'acknowledged_by'   => auth()->id(),
            'acknowledged_note' => $note ?: null,
            'acknowledged_at'   => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: "Alarm #{$alarmId} acknowledged.");
    }

    public function resolveAlarm(int $alarmId, string $note = ''): void
    {
        $alarm = Alarm::find($alarmId);
        if (!$alarm) {
            return;
        }

        $update = [
            'status'      => 'resolved',
            'resolved_at' => now(),
        ];

        if ($alarm->status !== 'acknowledged') {
            $update['acknowledged_by']   = auth()->id();
            $update['acknowledged_note'] = $note ?: 'Auto-acknowledge on resolve';
            $update['acknowledged_at']   = now();
        } elseif ($note) {
            $update['acknowledged_note'] = $note;
        }

        $alarm->update($update);

        $this->dispatch('toast', type: 'success', message: "Alarm #{$alarmId} resolved.");
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.alarms.index', [
            'alarms'     => $this->alarms,
            'summary'    => $this->summary,
            'sourceTypes' => $this->sourceTypes,
        ]);
    }
}



