<?php

namespace App\Livewire\NOC;

use App\Livewire\AdminComponent;
use App\Models\Alarm;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AlertList extends AdminComponent
{
    use WithPagination;

    public array $filters = [
        'severity' => 'all',
        'status' => 'all',
        'source' => 'all',
    ];

    public string $sortField = 'started_at';
    public string $sortDirection = 'desc';
    public int $perPage = 20;

    public function configure(): void
    {
        }

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'noc';
        $this->activePage = 'alerts';
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function alerts(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Alarm::with([
            'acknowledgedBy:id,name',
        ])
        ->when($this->filters['severity'] !== 'all', fn ($q) => $q->where('level', $this->filters['severity']))
        ->when($this->filters['status'] !== 'all', function ($q) {
            if ($this->filters['status'] === 'active') {
                $q->whereIn('status', ['open', 'acknowledged']);
            } else {
                $q->where('status', $this->filters['status']);
            }
        })
        ->when($this->filters['source'] !== 'all', fn ($q) => $q->where('source_type', $this->filters['source']))
        ->when(
            in_array($this->sortField, ['started_at', 'level', 'status', 'source_name', 'title']),
            fn ($q) => $q->orderBy($this->sortField, $this->sortDirection)
        )
        ->paginate($this->perPage);
    }

    #[Computed]
    public function alertStats(): array
    {
        return [
            'total' => Alarm::count(),
            'critical' => Alarm::where('level', 'critical')->where('status', '!=', 'resolved')->count(),
            'warning' => Alarm::where('level', 'warning')->where('status', '!=', 'resolved')->count(),
            'active' => Alarm::whereIn('status', ['open', 'acknowledged'])->count(),
            'acknowledged' => Alarm::where('status', 'acknowledged')->count(),
            'resolved' => Alarm::where('status', 'resolved')->count(),
        ];
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

    public function acknowledgeAlert(int $alertId): void
    {
        $alarm = Alarm::find($alertId);
        if (!$alarm || $alarm->status === 'resolved') {
            return;
        }
        $alarm->update([
            'status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "Alarm #{$alertId} acknowledged.");
    }

    public function resolveAlert(int $alertId): void
    {
        $alarm = Alarm::find($alertId);
        if (!$alarm) {
            return;
        }
        $update = [
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ];
        if ($alarm->status !== 'acknowledged') {
            $update['acknowledged_by'] = auth()->id();
            $update['acknowledged_at'] = now();
        }
        $alarm->update($update);
        $this->dispatch('toast', type: 'success', message: "Alarm #{$alertId} resolved.");
    }

    public function getSeverityColor(string $severity): string
    {
        return match($severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            'info' => 'primary',
            default => 'neutral',
        };
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.alert-list', [
            'alerts' => $this->alerts,
            'stats' => $this->alertStats,
            'sourceTypes' => $this->sourceTypes,
        ]);
    }
}



