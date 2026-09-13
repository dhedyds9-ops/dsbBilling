<?php

namespace App\Services\Provisioning\Pipeline;

use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Provisioning\ProvisionPipelineStep;
use Illuminate\Support\Facades\Log;

class PipelineOrchestrator
{
    public function __construct(
        protected PipelineStateManager $stateManager
    ) {}

    public function processNext(ProvisionPipeline $pipeline): void
    {
        // Refresh to get latest state
        $pipeline->refresh();
        
        if (in_array($pipeline->status, ['completed', 'cancelled'])) {
            return;
        }

        $this->stateManager->markPipelineRunning($pipeline);

        $nextStep = $this->stateManager->getNextPendingStep($pipeline);

        if (!$nextStep) {
            // All steps are completed or skipped
            $this->stateManager->markPipelineCompleted($pipeline);
            return;
        }

        $this->dispatchStepJob($pipeline, $nextStep);
    }

    protected function dispatchStepJob(ProvisionPipeline $pipeline, ProvisionPipelineStep $step): void
    {
        $jobClass = $this->resolveJobClass($step->step_type);
        
        if (!$jobClass || !class_exists($jobClass)) {
            Log::error("Pipeline Orchestrator: Job class not found for step type: {$step->step_type}");
            $this->stateManager->updateStepStatus($step, 'failed', "Job class not found for type: {$step->step_type}");
            $this->stateManager->markPipelineFailed($pipeline, "Pipeline halted: Step resolution failed.");
            return;
        }

        // Update pipeline current_step counter
        $pipeline->update(['current_step' => $step->order]);
        
        // Dispatch the specific job
        $jobClass::dispatch($step)->onQueue('provisioning');
    }

    protected function resolveJobClass(string $type): ?string
    {
        $map = [
            'customer_validation' => \App\Jobs\Provisioning\Steps\CustomerServiceValidationJob::class,
            'resource_reservation' => \App\Jobs\Provisioning\Steps\ResourceReservationJob::class,
            'olt_onu_registration' => \App\Jobs\Provisioning\Steps\OltOnuRegistrationJob::class,
            'olt_service_provisioning' => \App\Jobs\Provisioning\Steps\OltServiceProvisioningJob::class,
            'radius_provisioning' => \App\Jobs\Provisioning\Steps\RadiusProvisioningJob::class,
            'onu_tr069_provisioning' => \App\Jobs\Provisioning\Steps\OnuTr069ProvisioningJob::class,
            'service_verification' => \App\Jobs\Provisioning\Steps\ServiceVerificationJob::class,
            'activation' => \App\Jobs\Provisioning\Steps\ActivationJob::class,
        ];

        return $map[$type] ?? null;
    }
}
