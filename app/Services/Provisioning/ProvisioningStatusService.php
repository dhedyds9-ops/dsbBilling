<?php

namespace App\Services\Provisioning;

use App\Models\Provisioning\ProvisionPipeline;

class ProvisioningStatusService
{
    /**
     * Get the standardized provisioning status for the UI
     */
    public function getStatus(int $pipelineId): array
    {
        $pipeline = ProvisionPipeline::with('steps')->findOrFail($pipelineId);
        $cs = $pipeline->serviceInstance?->customerService;
        
        $steps = $pipeline->steps->map(function ($step) {
            return [
                'name' => $step->step_name,
                'status' => $step->status,
                'started_at' => $step->started_at,
                'completed_at' => $step->completed_at,
                'error' => $step->error_message,
                'type' => $step->step_type
            ];
        })->toArray();

        $completedSteps = $pipeline->steps->where('status', 'completed')->count();
        $totalSteps = $pipeline->total_steps;
        $progress = $totalSteps > 0 ? (int) (($completedSteps / $totalSteps) * 100) : 0;

        // Ensure we handle skipped properly
        $completedOrSkippedSteps = $pipeline->steps->whereIn('status', ['completed', 'skipped'])->count();
        $progress = $totalSteps > 0 ? (int) (($completedOrSkippedSteps / $totalSteps) * 100) : 0;

        // Determine specific subsystem states based on step payloads and logic
        $onuStep = $pipeline->steps->where('step_type', 'olt_onu_registration')->first();
        $tr069Step = $pipeline->steps->where('step_type', 'onu_tr069_provisioning')->first();
        $radiusStep = $pipeline->steps->where('step_type', 'radius_provisioning')->first();
        $verificationStep = $pipeline->steps->where('step_type', 'service_verification')->first();

        // can_activate is strictly ALL REQUIRED COMPLETED + VERIFICATION_PASSED + NOT FAILED
        $canActivate = false;
        if ($pipeline->status !== 'failed') {
            $requiredStepsCount = max(0, $pipeline->total_steps - 1);
            $completedCount = $pipeline->steps->where('order', '<', $totalSteps)->whereIn('status', ['completed', 'skipped'])->count();
            if ($completedCount >= $requiredStepsCount && $verificationStep?->status === 'completed') {
                $canActivate = true;
            }
        }

        return [
            'pipeline_status' => $pipeline->status,
            'customer_service_status' => $cs?->status ?? 'unknown',
            'current_step' => $pipeline->current_step,
            'progress' => $progress,
            'steps' => $steps,
            'onu_status' => $onuStep?->status ?? 'pending',
            'tr069_status' => $tr069Step?->status ?? 'pending',
            'radius_status' => $radiusStep?->status ?? 'pending',
            'verification_status' => $verificationStep?->status ?? 'pending',
            'can_activate' => $canActivate,
        ];
    }
}
