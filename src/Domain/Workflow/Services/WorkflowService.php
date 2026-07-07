<?php

namespace Src\Domain\Workflow\Services;

use Src\Domain\Workflow\Workflow;
use Src\Domain\Workflow\WorkflowInstance;
use Src\Domain\Workflow\WorkflowTask;
use Src\Domain\Workflow\WorkflowApproval;
use Src\Domain\Workflow\WorkflowTransition;
use Src\Domain\Workflow\Events\WorkflowStarted;
use Src\Domain\Workflow\Events\TaskAssigned;
use Src\Domain\Workflow\Events\TaskCompleted;
use Src\Domain\Workflow\Events\WorkflowApproved;
use Src\Domain\Workflow\Events\WorkflowRejected;
use Src\Domain\Workflow\Events\WorkflowCompleted;
use Src\Domain\Workflow\ValueObjects\WorkflowContext;
use Src\Domain\Workflow\Enums\WorkflowInstanceStatus;
use Src\Domain\Workflow\Enums\TaskStatus;
use Src\Domain\Workflow\Repositories\WorkflowRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowInstanceRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowTaskRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowApprovalRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Illuminate\Support\Facades\Event;

class WorkflowService
{
    public function __construct(
        private readonly WorkflowRepositoryInterface $workflowRepository,
        private readonly WorkflowInstanceRepositoryInterface $instanceRepository,
        private readonly WorkflowTaskRepositoryInterface $taskRepository,
        private readonly WorkflowApprovalRepositoryInterface $approvalRepository
    ) {}

    public function createWorkflow(
        string $name,
        string $description,
        string $entityType,
        string $triggerType,
        ?string $module = null
    ): Workflow {
        $workflow = Workflow::create(
            name: $name,
            description: $description,
            entityType: $entityType,
            triggerType: $triggerType,
            module: $module
        );

        $this->workflowRepository->save($workflow);
        return $workflow;
    }

    public function addNode(
        Uuid $workflowId,
        string $type,
        string $name,
        array $config = []
    ): Workflow {
        $workflow = $this->workflowRepository->findById($workflowId);
        if (!$workflow) {
            throw new \DomainException("Workflow not found");
        }

        $nodeId = Uuid::generate();
        $workflow->addNode($nodeId, $type, $name, $config);
        $this->workflowRepository->save($workflow);

        return $workflow;
    }

    public function addTransition(
        Uuid $workflowId,
        Uuid $fromNodeId,
        Uuid $toNodeId,
        string $type,
        ?array $conditions = null,
        ?array $actions = null
    ): Workflow {
        $workflow = $this->workflowRepository->findById($workflowId);
        if (!$workflow) {
            throw new \DomainException("Workflow not found");
        }

        $workflow->addTransition($fromNodeId, $toNodeId, $type, $conditions, $actions);
        $this->workflowRepository->save($workflow);

        return $workflow;
    }

    public function activateWorkflow(Uuid $workflowId): Workflow
    {
        $workflow = $this->workflowRepository->findById($workflowId);
        if (!$workflow) {
            throw new \DomainException("Workflow not found");
        }

        $workflow->activate();
        $this->workflowRepository->save($workflow);

        return $workflow;
    }

    public function startWorkflow(
        Workflow $workflow,
        string $entityId,
        WorkflowContext $context,
        Uuid $initiatedBy
    ): WorkflowInstance {
        if (!$workflow->isEffective()) {
            throw new \DomainException("Workflow is not effective or active");
        }

        $instance = WorkflowInstance::start($workflow, $entityId, $context, $initiatedBy);
        $this->instanceRepository->save($instance);

        Event::dispatch(new WorkflowStarted(
            $instance->id,
            $workflow->id,
            $initiatedBy,
            $workflow->entityType,
            $entityId
        ));

        $this->processCurrentNode($instance, $workflow);

        return $instance;
    }

    public function processCurrentNode(WorkflowInstance $instance, Workflow $workflow): void
    {
        $currentNodeId = $instance->getCurrentNodeId();
        if ($currentNodeId === null) {
            $this->completeInstance($instance);
            return;
        }

        $node = $this->getNodeFromWorkflow($workflow, $currentNodeId);
        
        switch ($node['type'] ?? 'task') {
            case 'approval':
                $this->createApprovalTask($instance, $node);
                break;
            case 'task':
                $this->createManualTask($instance, $node);
                break;
            case 'action':
                $this->executeNodeActions($instance, $node);
                $this->moveToNextNode($instance, $workflow);
                break;
            case 'condition':
                $this->evaluateConditionAndTransition($instance, $workflow, $node);
                break;
            case 'end':
                $this->completeInstance($instance);
                break;
        }

        $this->instanceRepository->save($instance);
    }

    private function getNodeFromWorkflow(Workflow $workflow, Uuid $nodeId): ?array
    {
        $nodes = $workflow->getNodes();
        return $nodes[$nodeId->value] ?? null;
    }

    private function createApprovalTask(WorkflowInstance $instance, array $node): void
    {
        $approval = WorkflowApproval::create(/* ... */);
        $this->approvalRepository->save($approval);
        
        $instance->wait();
    }

    private function createManualTask(WorkflowInstance $instance, array $node): void
    {
        $task = WorkflowTask::create(
            instanceId: $instance->id,
            nodeId: Uuid::generate(),
            name: $node['name'],
            description: $node['config']['description'] ?? ''
        );

        if (isset($node['config']['assignee'])) {
            $task->assignTo(new Uuid($node['config']['assignee']));
            
            Event::dispatch(new TaskAssigned(
                $task->id,
                $instance->id,
                new Uuid($node['config']['assignee']),
                'user',
                $task->name
            ));
        }

        $this->taskRepository->save($task);
    }

    private function executeNodeActions(WorkflowInstance $instance, array $node): void
    {
        if (isset($node['config']['actions'])) {
            foreach ($node['config']['actions'] as $action) {
                // Execute action
            }
        }
    }

    private function evaluateConditionAndTransition(WorkflowInstance $instance, Workflow $workflow, array $node): void
    {
        $transitions = $workflow->getTransitions();
        $context = $instance->getVariables();

        foreach ($transitions as $transition) {
            if ($transition['from_node_id'] === $instance->getCurrentNodeId()?->value) {
                // Evaluate condition
                $conditionMet = true; // Simplified
                if ($conditionMet) {
                    $instance->moveToNode(new Uuid($transition['to_node_id']), 'condition_met');
                    $this->processCurrentNode($instance, $workflow);
                    return;
                }
            }
        }
    }

    private function moveToNextNode(WorkflowInstance $instance, Workflow $workflow): void
    {
        $transitions = $workflow->getTransitions();
        $currentNodeId = $instance->getCurrentNodeId()?->value;

        foreach ($transitions as $transition) {
            if ($transition['from_node_id'] === $currentNodeId) {
                $instance->moveToNode(new Uuid($transition['to_node_id']));
                $this->processCurrentNode($instance, $workflow);
                return;
            }
        }

        // No transition found, complete the instance
        $this->completeInstance($instance);
    }

    public function completeTask(Uuid $taskId, Uuid $completedBy, array $outputs = []): WorkflowTask
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new \DomainException("Task not found");
        }

        $task->complete($outputs);
        $this->taskRepository->save($task);

        $instance = $this->instanceRepository->findById($task->instanceId);
        if ($instance) {
            $workflow = $this->workflowRepository->findById($instance->workflowId);
            if ($workflow) {
                $this->processCurrentNode($instance, $workflow);
            }
        }

        Event::dispatch(new TaskCompleted($task->id, $task->instanceId, $completedBy, $task->name, $outputs));

        return $task;
    }

    public function approveWorkflow(Uuid $instanceId, Uuid $approvedBy, ?string $comment = null): WorkflowInstance
    {
        $instance = $this->instanceRepository->findById($instanceId);
        if (!$instance) {
            throw new \DomainException("Workflow instance not found");
        }

        Event::dispatch(new WorkflowApproved($instance->id, $instance->workflowId, $approvedBy, 'approved', $comment));

        // Continue workflow processing
        $workflow = $this->workflowRepository->findById($instance->workflowId);
        if ($workflow) {
            $this->processCurrentNode($instance, $workflow);
        }

        return $instance;
    }

    public function rejectWorkflow(Uuid $instanceId, Uuid $rejectedBy, string $reason): WorkflowInstance
    {
        $instance = $this->instanceRepository->findById($instanceId);
        if (!$instance) {
            throw new \DomainException("Workflow instance not found");
        }

        $instance->cancel($reason);
        $this->instanceRepository->save($instance);

        Event::dispatch(new WorkflowRejected($instance->id, $instance->workflowId, $rejectedBy, $reason));

        return $instance;
    }

    private function completeInstance(WorkflowInstance $instance): void
    {
        $instance->complete();
        $this->instanceRepository->save($instance);

        Event::dispatch(new WorkflowCompleted(
            $instance->id,
            $instance->workflowId,
            'completed',
            $instance->getDurationMinutes()
        ));
    }

    public function cancelInstance(Uuid $instanceId, Uuid $cancelledBy, string $reason): WorkflowInstance
    {
        $instance = $this->instanceRepository->findById($instanceId);
        if (!$instance) {
            throw new \DomainException("Workflow instance not found");
        }

        $instance->cancel($reason);
        $this->instanceRepository->save($instance);

        return $instance;
    }

    public function rollbackInstance(Uuid $instanceId, Uuid $toNodeId): WorkflowInstance
    {
        $instance = $this->instanceRepository->findById($instanceId);
        if (!$instance) {
            throw new \DomainException("Workflow instance not found");
        }

        $instance->rollback($toNodeId);
        $this->instanceRepository->save($instance);

        return $instance;
    }

    public function getInstanceHistory(Uuid $instanceId): array
    {
        return []; // Would fetch from history repository
    }

    public function getActiveInstances(?string $entityType = null): array
    {
        return $this->instanceRepository->findActive($entityType);
    }

    public function getPendingTasks(?Uuid $assignedTo = null): array
    {
        return $this->taskRepository->findPending($assignedTo);
    }
}
