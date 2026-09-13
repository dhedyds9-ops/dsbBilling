<?php

namespace App\Services\Provisioning\Pipeline;

use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Provisioning\ProvisionPipelineStep;
use Illuminate\Support\Facades\DB;

class PipelineStateManager
{
    public function markPipelineRunning(ProvisionPipeline $pipeline): void
    {
        if ($pipeline->status === 'pending') {
            $pipeline->update([
                'status' => 'running',
                'started_at' => now(),
            ]);
        }
    }

    public function markPipelineCompleted(ProvisionPipeline $pipeline): void
    {
        $pipeline->update([
            'status' => 'completed',
            'completed_at' => now(),
            'current_step' => $pipeline->total_steps,
        ]);
    }

    public function markPipelineFailed(ProvisionPipeline $pipeline, string $error): void
    {
        $pipeline->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $error,
        ]);
    }

    public function updateStepStatus(ProvisionPipelineStep $step, string $status, ?string $error = null, ?array $payload = null): void
    {
        $data = ['status' => $status];
        
        if ($status === 'in_progress' && !$step->started_at) {
            $data['started_at'] = now();
        } elseif ($status === 'completed') {
            $data['completed_at'] = now();
        } elseif ($status === 'failed') {
            $data['failed_at'] = now();
            $data['error_message'] = $error;
        } elseif ($status === 'skipped') {
            $data['completed_at'] = now(); // Mark as done logically
            $data['notes'] = $error ?? 'Skipped';
        }

        if ($payload !== null) {
            $currentPayload = is_array($step->payload) ? $step->payload : [];
            $data['payload'] = array_merge($currentPayload, $payload);
        }

        $step->update($data);
    }

    public function getNextPendingStep(ProvisionPipeline $pipeline): ?ProvisionPipelineStep
    {
        return $pipeline->steps()
            ->whereIn('status', ['pending', 'failed']) // Support retry for failed
            ->orderBy('order')
            ->first();
    }
}
