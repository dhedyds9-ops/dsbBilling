<?php

namespace App\Livewire\Isp\Technician\Provisioning;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Provisioning\ProvisionPipeline;
use App\Services\Provisioning\ProvisioningStatusService;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.technician-app')]
class Show extends Component
{
    public $pipelineId;

    private function isAdminOversight(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['administrator', 'superadmin'])
            && strtoupper((string) $user->job_function) !== 'TECHNICIAN';
    }

    public function mount($id = null)
    {
        if (!$id) {
            abort(404);
        }

        $this->pipelineId = (int) $id;

        if (!$this->isAdminOversight()) {
            $userId = Auth::id();
            $allowed = ProvisionPipeline::where('id', $this->pipelineId)
                ->where(function ($sub) use ($userId) {
                    $sub->where('created_by', $userId)
                        ->orWhereHas('serviceInstance', function ($siQ) use ($userId) {
                            $siQ->withTrashed()->where(function ($siSub) use ($userId) {
                                $siSub->where('created_by', $userId)
                                    ->orWhereHas('customerService', function ($csQ) use ($userId) {
                                        $csQ->withoutGlobalScopes()->withTrashed()->where('created_by', $userId);
                                    });
                            });
                        });
                })
                ->exists();

            if (!$allowed) {
                abort(403, 'Anda tidak memiliki akses ke instalasi ini.');
            }
        }
    }

    public function render(ProvisioningStatusService $statusService)
    {
        $status = $statusService->getStatus($this->pipelineId);

        return view('livewire.isp.technician.provisioning.show', [
            'status' => $status,
        ]);
    }
}
