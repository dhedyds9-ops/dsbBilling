<?php

namespace App\Livewire\NOC\Provisioning;

use App\Livewire\AdminComponent;
use App\Services\NOC\ProvisioningMonitoringService;
use App\Models\Provisioning\ProvisionPipeline;
use Livewire\Attributes\Computed;

class Show extends AdminComponent
{
    public int $id;

    protected ProvisioningMonitoringService $monitoring;

    public function boot(ProvisioningMonitoringService $monitoring): void
    {
        $this->monitoring = $monitoring;
    }

    public function configure(): void
    {
        }

    public function mount($id = null): void
    {
        parent::mount();
        $this->id           = (int) $id;
        $this->activeModule = 'noc';
        $this->activePage   = 'provisioning';
    }

    #[Computed]
    public function pipeline(): ?ProvisionPipeline
    {
        return $this->monitoring->getPipelineDetail($this->id);
    }

    public function retry(): void
    {
        $ok = $this->monitoring->retryPipeline($this->id, auth()->id());

        if ($ok) {
            $this->dispatch('toast', type: 'success', message: 'Pipeline re-queued for retry.');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Failed to retry pipeline — not in failed state or orchestrator error.');
        }
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        abort_if(!$this->pipeline, 404);

        return view('livewire.noc.provisioning.show', [
            'pipeline' => $this->pipeline,
        ]);
    }
}



