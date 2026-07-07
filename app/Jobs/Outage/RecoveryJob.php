<?php

namespace App\Jobs\Outage;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Outage\Repositories\OutageRepositoryInterface;
use Src\Domain\Outage\Repositories\RecoveryPlanRepositoryInterface;
use Src\Domain\Outage\Services\RecoveryService;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RecoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $outageId,
        protected string $technicianId,
        protected ?string $technicianName = null,
        protected array $options = []
    ) {}

    public function handle(
        OutageRepositoryInterface $outageRepository,
        RecoveryPlanRepositoryInterface $recoveryRepository,
        RecoveryService $recoveryService
    ): void {
        $outageUuid = Uuid::fromString($this->outageId);

        $this->updateProgress('recovery_initiated', [
            'outage_id' => $this->outageId,
            'technician_id' => $this->technicianId
        ]);

        $recoveryPlan = $recoveryService->startRecovery(
            $outageUuid,
            $this->technicianId,
            $this->technicianName
        );

        $this->updateProgress('recovery_started', [
            'recovery_plan_id' => $recoveryPlan->id->value
        ]);

        if (!empty($this->options['steps'])) {
            $this->executeRecoverySteps($recoveryPlan, $this->options['steps'], $recoveryService);
        }

        $verified = $recoveryService->verifyRecovery($outageUuid);

        $this->updateProgress('recovery_verified', [
            'verified' => $verified
        ]);

        if ($verified) {
            $recoveryService->completeRecovery(
                $outageUuid,
                true,
                $this->options['notes'] ?? 'Recovery completed successfully'
            );

            $this->updateProgress('recovery_completed', [
                'outage_id' => $this->outageId,
                'success' => true
            ]);
        } else {
            $this->updateProgress('recovery_partial', [
                'outage_id' => $this->outageId,
                'success' => false,
                'reason' => 'Partial recovery - some nodes still down'
            ]);
        }
    }

    private function executeRecoverySteps($recoveryPlan, array $steps, RecoveryService $recoveryService): void
    {
        foreach ($steps as $index => $step) {
            $this->updateProgress('executing_step', [
                'step_index' => $index,
                'step_title' => $step['title'] ?? "Step {$index}"
            ]);

            usleep(100000);

            $recoveryService->executeStep(
                $recoveryPlan->id,
                $index,
                $step['result'] ?? 'Completed',
                $step['success'] ?? true
            );

            $this->updateProgress('step_completed', [
                'step_index' => $index,
                'success' => $step['success'] ?? true
            ]);
        }
    }

    private function updateProgress(string $stage, array $data): void
    {
        if (!empty($this->options['callback'])) {
            $callback = $this->options['callback'];
            $callback($stage, $data);
        }
    }

    public function getQueue(): string
    {
        return 'outage-recovery';
    }
}
