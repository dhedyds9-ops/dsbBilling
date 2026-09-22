<?php

namespace App\Livewire\Isp\Technician\Provisioning;

use Livewire\Component;
use Livewire\Attributes\Layout;


use App\Services\Provisioning\ProvisioningStatusService;

#[Layout('layouts.technician-app')]
class Show extends Component
{
    public $pipelineId;

    public function mount($id = null)
    {
        if (!$id) abort(404);
        $this->pipelineId = (int) $id;
    }

    public function render(ProvisioningStatusService $statusService)
    {
        $status = $statusService->getStatus($this->pipelineId);

        return view('livewire.isp.technician.provisioning.show', [
            'status' => $status
        ]);
    }
}
