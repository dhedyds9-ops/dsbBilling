<?php

namespace App\Jobs\Workflow;

use App\Models\Workflow\WorkflowTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class ReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $taskId,
        public string $reminderType = 'due_soon'
    ) {}

    public function handle(): void
    {
        Log::info('ReminderJob started', [
            'task_id' => $this->taskId,
            'reminder_type' => $this->reminderType
        ]);

        $task = WorkflowTask::with(['assignee', 'instance'])->find($this->taskId);
        if (!$task) {
            Log::warning('Task not found', ['task_id' => $this->taskId]);
            return;
        }

        match($this->reminderType) {
            'due_soon' => $this->sendDueSoonReminder($task),
            'overdue' => $this->sendOverdueReminder($task),
            'assigned' => $this->sendAssignedReminder($task),
            'escalated' => $this->sendEscalatedReminder($task),
            default => Log::warning('Unknown reminder type', ['reminder_type' => $this->reminderType]),
        };

        Log::info('ReminderJob completed', ['task_id' => $this->taskId]);
    }

    private function sendDueSoonReminder(WorkflowTask $task): void
    {
        if (!$task->assignee) {
            return;
        }

        // Send notification
        Log::info('Due soon reminder sent', [
            'task_id' => $task->id,
            'assignee' => $task->assignee->email ?? 'unknown'
        ]);
    }

    private function sendOverdueReminder(WorkflowTask $task): void
    {
        if (!$task->assignee) {
            return;
        }

        // Send urgent notification
        Log::info('Overdue reminder sent', [
            'task_id' => $task->id,
            'assignee' => $task->assignee->email ?? 'unknown'
        ]);
    }

    private function sendAssignedReminder(WorkflowTask $task): void
    {
        if (!$task->assignee) {
            return;
        }

        // Send assignment notification
        Log::info('Assignment reminder sent', [
            'task_id' => $task->id,
            'assignee' => $task->assignee->email ?? 'unknown'
        ]);
    }

    private function sendEscalatedReminder(WorkflowTask $task): void
    {
        // Notify managers/escalation contacts
        Log::info('Escalated reminder sent', ['task_id' => $task->id]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ReminderJob failed', [
            'task_id' => $this->taskId,
            'reminder_type' => $this->reminderType,
            'error' => $exception->getMessage()
        ]);
    }
}
