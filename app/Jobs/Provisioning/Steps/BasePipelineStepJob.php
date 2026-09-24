<?php

namespace App\Jobs\Provisioning\Steps;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Provisioning\ProvisionPipelineStep;
use App\Services\Provisioning\Pipeline\PipelineOrchestrator;
use App\Services\Provisioning\Pipeline\PipelineStateManager;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BasePipelineStepJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;
    public $backoff = [10, 30, 60];
    
    // Idempotency timeout
    public $uniqueFor = 60; 

    protected ProvisionPipelineStep $step;

    public function __construct(ProvisionPipelineStep $step)
    {
        $this->step = $step;
    }

    public function uniqueId(): string
    {
        // Unik per step untuk mencegah duplikasi eksekusi step yang sama bersamaan
        return "step_execution:{$this->step->id}";
    }

    public function handle(PipelineStateManager $stateManager, PipelineOrchestrator $orchestrator): void
    {
        // If already completed or skipped (idempotency check at pipeline level)
        if (in_array($this->step->status, ['completed', 'skipped'])) {
            $orchestrator->processNext($this->step->provisionPipeline);
            return;
        }

        try {
            $stateManager->updateStepStatus($this->step, 'in_progress');
            
            // Execute actual business logic in child class
            $payload = $this->executeStep();

            $stateManager->updateStepStatus($this->step, 'completed', null, $payload);
            
            // Lanjut ke step berikutnya
            $orchestrator->processNext($this->step->provisionPipeline);
            
        } catch (Throwable $e) {
            $this->handleFailure($e, $stateManager, $orchestrator);
        }
    }

    protected function handleFailure(Throwable $e, PipelineStateManager $stateManager, PipelineOrchestrator $orchestrator): void
    {
        Log::error("Pipeline Step Failed [{$this->step->step_name}]: " . $e->getMessage(), [
            'pipeline_id' => $this->step->provision_pipeline_id,
            'trace' => $e->getTraceAsString()
        ]);

        if ($this->attempts() < $this->tries) {
            // Throw exception so Laravel re-queues it
            throw $e;
        }

        // Permanent failure
        $stateManager->updateStepStatus($this->step, 'failed', $e->getMessage());
        $stateManager->markPipelineFailed($this->step->provisionPipeline, "Failed at {$this->step->step_name}: " . $e->getMessage());
        
        $this->onPermanentFailure($e);
    }

    public function failed(Throwable $exception): void
    {
        // Called natively by Laravel when job reaches max tries
        $stateManager = app(PipelineStateManager::class);
        $stateManager->updateStepStatus($this->step, 'failed', $exception->getMessage());
        $stateManager->markPipelineFailed($this->step->provisionPipeline, "Failed at {$this->step->step_name}: " . $exception->getMessage());
    }

    abstract protected function executeStep(): ?array;
    
    protected function onPermanentFailure(Throwable $e): void
    {
        // Override in child class if specific cleanup is needed
    }
}
