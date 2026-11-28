<?php

namespace App\Jobs\Provisioning\Steps;

use App\Services\Provisioning\ProvisioningOrchestrator;

class ActivationJob extends BasePipelineStepJob
{
    protected function executeStep(): ?array
    {
        $serviceInstance = $this->step->provisionPipeline->serviceInstance;
        $cs = $serviceInstance->customerService;
        $pipeline = $this->step->provisionPipeline;

        // Final sanity check: P4 - ALL_REQUIRED_STEPS_COMPLETED && VERIFICATION_PASSED
        $verificationStep = $pipeline->steps()->where('step_type', 'service_verification')->first();
        if (!$verificationStep || $verificationStep->status !== 'completed') {
            throw new \Exception("Cannot activate: Service verification has not passed.");
        }

        $failedSteps = $pipeline->steps()->where('status', 'failed')->count();
        if ($failedSteps > 0) {
            throw new \Exception("Cannot activate: There are failed steps in the pipeline.");
        }

        // Must have completed all previous steps
        $requiredSteps = $pipeline->total_steps - 1; // Assuming activation is the last step
        $completedSteps = $pipeline->steps()->where('status', 'completed')->where('order', '<', $this->step->order)->count();

        if ($completedSteps < $requiredSteps) {
            throw new \Exception("Cannot activate: Not all required steps are completed ({$completedSteps} / {$requiredSteps}).");
        }

        // SSOT aktivasi: set status aktif + aktifkan PPPoE + publikasikan ServiceActivatedEvent.
        app(ProvisioningOrchestrator::class)->activate($cs);

        return [
            'activated_at' => now()->toDateTimeString(),
            'final_status' => 'ACTIVE'
        ];
    }
}
