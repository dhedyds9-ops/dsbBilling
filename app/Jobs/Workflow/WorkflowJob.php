<?php

namespace App\Jobs\Workflow;

use App\Models\Workflow\WorkflowInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $instanceId,
        public string $action = 'process'
    ) {}

    public function handle(): void
    {
        Log::info('WorkflowJob started', [
            'instance_id' => $this->instanceId,
            'action' => $this->action
        ]);

        $instance = WorkflowInstance::find($this->instanceId);
        if (!$instance) {
            Log::warning('WorkflowInstance not found', ['instance_id' => $this->instanceId]);
            return;
        }

        match($this->action) {
            'process' => $this->processWorkflow($instance),
            'resume' => $this->resumeWorkflow($instance),
            'cancel' => $this->cancelWorkflow($instance),
            default => Log::warning('Unknown action', ['action' => $this->action]),
        };

        Log::info('WorkflowJob completed', ['instance_id' => $this->instanceId]);
    }

    private function processWorkflow(WorkflowInstance $instance): void
    {
        // Process the current node
        // This would call WorkflowService::processCurrentNode
    }

    private function resumeWorkflow(WorkflowInstance $instance): void
    {
        $instance->resume();
        $instance->save();
    }

    private function cancelWorkflow(WorkflowInstance $instance): void
    {
        $instance->cancel('Cancelled by system');
        $instance->save();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('WorkflowJob failed', [
            'instance_id' => $this->instanceId,
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);

        $instance = WorkflowInstance::find($this->instanceId);
        if ($instance) {
            $instance->fail($exception->getMessage());
            $instance->save();
        }
    }
}
