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

    public function retryProvisioning()
    {
        $pipeline = ProvisionPipeline::with('serviceInstance.customerService.onu')->find($this->pipelineId);
        if (!$pipeline) {
            session()->flash('error', 'Pipeline provisioning tidak ditemukan.');
            return;
        }

        $cs = $pipeline->serviceInstance?->customerService;
        $onu = $cs?->onu ?? ($cs?->onu_id ? \App\Models\ISP\Onu::withoutGlobalScopes()->find($cs->onu_id) : null);

        if (!$onu) {
            session()->flash('error', 'Perangkat ONU tidak ditemukan untuk pipeline ini.');
            return;
        }

        try {
            $service = app(\App\Services\ISP\GenieAcsProvisioningService::class);
            $result = $service->provisionOnt($onu, [
                'force_retry' => true,
                'customer_service' => $cs,
            ]);

            if ($result['status'] === 'success') {
                $step = $pipeline->steps()->where('step_type', 'onu_tr069_provisioning')->first();
                if ($step) {
                    $step->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'error_message' => null,
                    ]);
                }
                session()->flash('success', 'Retry Provisioning Berhasil: ONT telah terkonfigurasi via GenieACS!');
            } elseif ($result['status'] === 'waiting_device') {
                session()->flash('warning', 'Retry Provisioning: ONT sedang offline. Task telah diantrikan kembali di GenieACS.');
            } else {
                session()->flash('error', 'Retry Provisioning Gagal: ' . ($result['error'] ?? 'Terjadi kesalahan NBI'));
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal retry provisioning: ' . $e->getMessage());
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
