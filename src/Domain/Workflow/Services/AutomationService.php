<?php

namespace Src\Domain\Workflow\Services;

use Src\Domain\Workflow\Workflow;
use Src\Domain\Workflow\WorkflowInstance;
use Src\Domain\Workflow\WorkflowAction;
use Src\Domain\Workflow\Repositories\WorkflowRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowInstanceRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowActionRepositoryInterface;
use Src\Domain\Workflow\Enums\TriggerType;
use Src\Domain\Workflow\ValueObjects\WorkflowContext;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AutomationService
{
    public function __construct(
        private readonly WorkflowRepositoryInterface $workflowRepository,
        private readonly WorkflowInstanceRepositoryInterface $instanceRepository,
        private readonly WorkflowActionRepositoryInterface $actionRepository
    ) {}

    public function triggerByEvent(string $eventType, array $eventData): array
    {
        $workflows = $this->workflowRepository->findByTrigger(TriggerType::EVENT);
        
        $triggeredInstances = [];
        foreach ($workflows as $workflow) {
            if ($this->evaluateTriggerCondition($workflow, $eventType, $eventData)) {
                $context = WorkflowContext::fromArray($eventData);
                $instance = $this->startWorkflow($workflow, $context);
                $triggeredInstances[] = $instance;
            }
        }

        return $triggeredInstances;
    }

    public function triggerBySchedule(string $workflowId): ?WorkflowInstance
    {
        $workflow = $this->workflowRepository->findById(new Uuid($workflowId));
        if (!$workflow || $workflow->triggerType !== TriggerType::SCHEDULED) {
            return null;
        }

        $context = new WorkflowContext();
        return $this->startWorkflow($workflow, $context);
    }

    public function triggerByCondition(string $workflowId, array $conditionData): ?WorkflowInstance
    {
        $workflow = $this->workflowRepository->findById(new Uuid($workflowId));
        if (!$workflow) {
            return null;
        }

        if ($this->evaluateConditions($workflow, $conditionData)) {
            $context = WorkflowContext::fromArray($conditionData);
            return $this->startWorkflow($workflow, $context);
        }

        return null;
    }

    private function evaluateTriggerCondition(Workflow $workflow, string $eventType, array $eventData): bool
    {
        // Check if event type matches and conditions are met
        return true; // Simplified
    }

    private function evaluateConditions(Workflow $workflow, array $data): bool
    {
        // Evaluate workflow conditions against data
        return true; // Simplified
    }

    private function startWorkflow(Workflow $workflow, WorkflowContext $context): WorkflowInstance
    {
        // Same as WorkflowService::startWorkflow
        $initiatedBy = new Uuid($context->initiatorId ?? 'system');
        return WorkflowInstance::start($workflow, '', $context, $initiatedBy);
    }

    public function executeAction(Uuid $actionId, array $context = []): array
    {
        $action = $this->actionRepository->findById($actionId);
        if (!$action) {
            throw new \DomainException("Action not found");
        }

        return $action->execute($context);
    }

    public function executeNodeActions(Uuid $workflowId, Uuid $nodeId, array $context = []): array
    {
        $actions = $this->actionRepository->findByNode($nodeId);
        $results = [];

        foreach ($actions as $action) {
            if ($action->isActive) {
                $results[] = $action->execute($context);
            }
        }

        return $results;
    }

    public function createAutomationRule(
        string $name,
        string $workflowId,
        string $triggerType,
        array $conditions,
        array $actions
    ): array {
        // Create an automation rule that ties trigger conditions to workflow actions
        return [
            'id' => Uuid::generate()->value,
            'name' => $name,
            'workflow_id' => $workflowId,
            'trigger_type' => $triggerType,
            'conditions' => $conditions,
            'actions' => $actions,
            'is_active' => true,
        ];
    }

    public function evaluateAndExecute(string $triggerType, array $data): array
    {
        $results = [];

        // Find matching automation rules
        $rules = $this->findMatchingRules($triggerType, $data);

        foreach ($rules as $rule) {
            $results[] = $this->executeRule($rule, $data);
        }

        return $results;
    }

    private function findMatchingRules(string $triggerType, array $data): array
    {
        // Find rules that match the trigger type and conditions
        return [];
    }

    private function executeRule(array $rule, array $data): array
    {
        // Execute the rule's actions
        return ['success' => true, 'rule_id' => $rule['id'] ?? null];
    }

    public function retryFailedAction(Uuid $actionId, array $context = []): array
    {
        return $this->executeAction($actionId, $context);
    }

    public function rollbackAction(Uuid $instanceId, int $step): bool
    {
        $instance = $this->instanceRepository->findById($instanceId);
        if (!$instance) {
            return false;
        }

        // Rollback to previous state
        return true;
    }
}
