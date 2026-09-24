<?php

namespace Src\Domain\Workflow;

use Src\Domain\Workflow\Enums\ActionType;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WorkflowAction extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $workflowId,
        public readonly Uuid $nodeId,
        public readonly ActionType $type,
        public readonly string $name,
        public readonly array $config,
        public readonly int $order = 0,
        public readonly bool $isAsync = false,
        public readonly ?int $retryAttempts = null,
        public readonly ?int $retryDelaySeconds = null,
        public readonly ?string $failureAction = null,
        public readonly bool $isActive = true
    ) {}

    public static function createNotify(
        Uuid $workflowId,
        Uuid $nodeId,
        string $name,
        array $recipients,
        string $template,
        array $templateData = []
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            nodeId: $nodeId,
            type: ActionType::NOTIFY,
            name: $name,
            config: [
                'recipients' => $recipients,
                'template' => $template,
                'template_data' => $templateData,
            ]
        );
    }

    public static function createWebhook(
        Uuid $workflowId,
        Uuid $nodeId,
        string $name,
        string $url,
        string $method = 'POST',
        ?array $headers = null,
        ?string $bodyTemplate = null
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            nodeId: $nodeId,
            type: ActionType::WEBHOOK,
            name: $name,
            config: [
                'url' => $url,
                'method' => $method,
                'headers' => $headers ?? [],
                'body_template' => $bodyTemplate,
            ]
        );
    }

    public static function createApiCall(
        Uuid $workflowId,
        Uuid $nodeId,
        string $name,
        string $service,
        string $endpoint,
        string $method = 'POST',
        ?array $credentials = null
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            nodeId: $nodeId,
            type: ActionType::API_CALL,
            name: $name,
            config: [
                'service' => $service,
                'endpoint' => $endpoint,
                'method' => $method,
                'credentials' => $credentials,
            ]
        );
    }

    public static function createUpdateField(
        Uuid $workflowId,
        Uuid $nodeId,
        string $name,
        string $entityType,
        string $field,
        mixed $value
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            nodeId: $nodeId,
            type: ActionType::UPDATE_FIELD,
            name: $name,
            config: [
                'entity_type' => $entityType,
                'field' => $field,
                'value' => $value,
            ]
        );
    }

    public static function createAssignTask(
        Uuid $workflowId,
        Uuid $nodeId,
        string $name,
        string $assigneeType,
        ?string $assigneeId = null,
        ?string $assigneeRole = null,
        int $priority = 5
    ): self {
        return new self(
            id: Uuid::generate(),
            workflowId: $workflowId,
            nodeId: $nodeId,
            type: ActionType::ASSIGN_TASK,
            name: $name,
            config: [
                'assignee_type' => $assigneeType,
                'assignee_id' => $assigneeId,
                'assignee_role' => $assigneeRole,
                'priority' => $priority,
            ]
        );
    }

    public function execute(array $context = []): array
    {
        $result = match($this->type) {
            ActionType::NOTIFY => $this->executeNotify($context),
            ActionType::WEBHOOK => $this->executeWebhook($context),
            ActionType::API_CALL => $this->executeApiCall($context),
            ActionType::UPDATE_FIELD => $this->executeUpdateField($context),
            ActionType::ASSIGN_TASK => $this->executeAssignTask($context),
            default => ['success' => true, 'data' => null],
        };

        return $result;
    }

    private function executeNotify(array $context): array
    {
        // Implementation for sending notifications
        return ['success' => true, 'notification_sent' => true];
    }

    private function executeWebhook(array $context): array
    {
        // Implementation for webhook calls
        return ['success' => true, 'webhook_triggered' => true];
    }

    private function executeApiCall(array $context): array
    {
        // Implementation for API calls
        return ['success' => true, 'api_response' => null];
    }

    private function executeUpdateField(array $context): array
    {
        // Implementation for field updates
        return ['success' => true, 'field_updated' => true];
    }

    private function executeAssignTask(array $context): array
    {
        // Implementation for task assignment
        return ['success' => true, 'task_assigned' => true];
    }

    public function shouldRetry(int $attemptCount): bool
    {
        if ($this->retryAttempts === null) {
            return false;
        }
        return $attemptCount < $this->retryAttempts;
    }
}
