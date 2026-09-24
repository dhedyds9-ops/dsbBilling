<?php

namespace App\Jobs\Workflow;

use App\Models\Workflow\WorkflowTask;
use App\Models\Workflow\WorkflowInstance;
use App\Models\Workflow\WorkflowApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EscalationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $taskId,
        public string $escalationType = 'sla_breach'
    ) {}

    public function handle(): void
    {
        Log::info('EscalationJob started', [
            'task_id' => $this->taskId,
            'escalation_type' => $this->escalationType
        ]);

        $task = WorkflowTask::with(['instance', 'assignee'])->find($this->taskId);
        if (!$task) {
            Log::warning('Task not found', ['task_id' => $this->taskId]);
            return;
        }

        match($this->escalationType) {
            'sla_breach' => $this->handleSLABreach($task),
            'approval_timeout' => $this->handleApprovalTimeout($task),
            'task_timeout' => $this->handleTaskTimeout($task),
            'escalation_level' => $this->handleEscalationLevel($task),
            default => Log::warning('Unknown escalation type', ['escalation_type' => $this->escalationType]),
        };

        Log::info('EscalationJob completed', ['task_id' => $this->taskId]);
    }

    private function handleSLABreach(WorkflowTask $task): void
    {
        // Mark task as overdue
        $task->metadata = array_merge($task->metadata ?? [], [
            'sla_breached_at' => now()->toIso8601String(),
            'sla_breach_count' => ($task->metadata['sla_breach_count'] ?? 0) + 1,
        ]);
        $task->save();

        // Notify managers
        Log::info('SLA breach escalated', [
            'task_id' => $task->id,
            'instance_id' => $task->instance_id
        ]);

        // Potentially reassign or escalate to higher authority
        if (($task->metadata['sla_breach_count'] ?? 0) >= 2) {
            $this->performEscalation($task, 'senior_manager');
        }
    }

    private function handleApprovalTimeout(WorkflowApproval $approval): void
    {
        // Similar to SLA breach but for approvals
        Log::info('Approval timeout escalated', ['approval_id' => $approval->id]);
    }

    private function handleTaskTimeout(WorkflowTask $task): void
    {
        // Auto-complete or skip if timeout
        Log::info('Task timeout escalated', ['task_id' => $task->id]);
    }

    private function handleEscalationLevel(WorkflowTask $task): void
    {
        // Increase escalation level
        $task->metadata = array_merge($task->metadata ?? [], [
            'escalation_level' => ($task->metadata['escalation_level'] ?? 0) + 1,
        ]);
        $task->save();

        // Reassign or notify higher authority
        Log::info('Task escalated to higher level', [
            'task_id' => $task->id,
            'level' => $task->metadata['escalation_level']
        ]);
    }

    private function performEscalation(WorkflowTask $task, string $level): void
    {
        // Implement escalation logic
        // Could reassign to manager, add additional reviewers, etc.
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('EscalationJob failed', [
            'task_id' => $this->taskId,
            'escalation_type' => $this->escalationType,
            'error' => $exception->getMessage()
        ]);
    }
}
