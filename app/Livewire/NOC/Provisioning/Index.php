<?php

namespace App\Livewire\NOC\Provisioning;

use App\Livewire\AdminComponent;
use App\Services\NOC\ProvisioningMonitoringService;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    public string $search       = '';
    public string $statusFilter = 'all';
    public int $perPage = 20;

    protected ProvisioningMonitoringService $monitoring;

    public function boot(ProvisioningMonitoringService $monitoring): void
    {
        $this->monitoring = $monitoring;
    }

    public function configure(): void
    {
        }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'provisioning';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function summary(): array
    {
        return $this->monitoring->getQueueSummary();
    }

    #[Computed]
    public function pipelines(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->monitoring->getPipelines(
            statusFilter: $this->statusFilter,
            search:       $this->search,
            perPage:      $this->perPage,
        );
    }

    #[Computed]
    public function recentFailures(): \Illuminate\Support\Collection
    {
        return $this->monitoring->getRecentFailures(limit: 8);
    }

    public function retryPipeline(int $pipelineId): void
    {
        $ok = $this->monitoring->retryPipeline($pipelineId, auth()->id());

        if ($ok) {
            $this->dispatch('toast', type: 'success', message: "Pipeline #{$pipelineId} re-queued for retry.");
        } else {
            $this->dispatch('toast', type: 'error', message: "Failed to retry pipeline #{$pipelineId} — check log.");
        }
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.provisioning.index', [
            'pipelines'      => $this->pipelines,
            'summary'        => $this->summary,
            'recentFailures' => $this->recentFailures,
        ]);
    }
}



