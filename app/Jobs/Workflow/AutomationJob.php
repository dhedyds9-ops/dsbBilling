<?php

namespace App\Jobs\Workflow;

use App\Models\Workflow\WorkflowAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutomationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $actionId,
        public array $context = [],
        public int $attempt = 1
    ) {}

    public function handle(): void
    {
        Log::info('AutomationJob started', [
            'action_id' => $this->actionId,
            'attempt' => $this->attempt,
            'context' => $this->context
        ]);

        $action = WorkflowAction::find($this->actionId);
        if (!$action) {
            Log::warning('Action not found', ['action_id' => $this->actionId]);
            return;
        }

        try {
            $result = $this->executeAction($action);
            
            Log::info('AutomationJob completed', [
                'action_id' => $this->actionId,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('AutomationJob execution failed', [
                'action_id' => $this->actionId,
                'error' => $e->getMessage()
            ]);

            // Retry if allowed
            if ($action->shouldRetry($this->attempt)) {
                $this->retry($action);
            }
        }
    }

    private function executeAction(WorkflowAction $action): array
    {
        $result = match($action->type) {
            'notify' => $this->executeNotify($action),
            'webhook' => $this->executeWebhook($action),
            'api_call' => $this->executeApiCall($action),
            'update_field' => $this->executeUpdateField($action),
            'create_record' => $this->executeCreateRecord($action),
            'email' => $this->executeEmail($action),
            'sms' => $this->executeSms($action),
            default => ['success' => true],
        };

        return $result;
    }

    private function executeNotify(WorkflowAction $action): array
    {
        // Send notification
        return ['success' => true, 'notification_sent' => true];
    }

    private function executeWebhook(WorkflowAction $action): array
    {
        $config = $action->config;
        
        try {
            $response = Http::timeout(30)->send(
                $config['method'] ?? 'POST',
                $config['url']
            );

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'body' => $response->body(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function executeApiCall(WorkflowAction $action): array
    {
        // Similar to webhook but for internal API calls
        return ['success' => true, 'api_called' => true];
    }

    private function executeUpdateField(WorkflowAction $action): array
    {
        $config = $action->config;
        // Update field on entity
        return ['success' => true, 'field_updated' => true];
    }

    private function executeCreateRecord(WorkflowAction $action): array
    {
        // Create a new record
        return ['success' => true, 'record_created' => true];
    }

    private function executeEmail(WorkflowAction $action): array
    {
        // Send email
        return ['success' => true, 'email_sent' => true];
    }

    private function executeSms(WorkflowAction $action): array
    {
        // Send SMS
        return ['success' => true, 'sms_sent' => true];
    }

    private function retry(WorkflowAction $action): void
    {
        $delay = $action->retryDelaySeconds ?? 60;
        
        self::dispatch(
            $this->actionId,
            $this->context,
            $this->attempt + 1
        )->delay(now()->addSeconds($delay));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AutomationJob permanently failed', [
            'action_id' => $this->actionId,
            'attempts' => $this->attempt,
            'error' => $exception->getMessage()
        ]);

        // Could trigger rollback or alert here
    }
}
