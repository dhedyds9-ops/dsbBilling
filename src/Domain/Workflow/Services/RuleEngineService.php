<?php

namespace Src\Domain\Workflow\Services;

use Src\Domain\Workflow\Repositories\WorkflowRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowInstanceRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowTaskRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowApprovalRepositoryInterface;
use Src\Domain\Workflow\ValueObjects\TransitionCondition;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RuleEngineService
{
    public function __construct(
        private readonly WorkflowRepositoryInterface $workflowRepository,
        private readonly WorkflowInstanceRepositoryInterface $instanceRepository,
        private readonly WorkflowTaskRepositoryInterface $taskRepository,
        private readonly WorkflowApprovalRepositoryInterface $approvalRepository
    ) {}

    public function evaluateCondition(TransitionCondition $condition, array $context): bool
    {
        return $condition->evaluate($context);
    }

    public function evaluateConditions(array $conditions, array $context, string $operator = 'AND'): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $conditionData) {
            $condition = TransitionCondition::equals(
                $conditionData['field'],
                $conditionData['value']
            );
            
            $result = $condition->evaluate($context);

            if ($operator === 'AND' && !$result) {
                return false;
            }
            
            if ($operator === 'OR' && $result) {
                return true;
            }
        }

        return $operator === 'AND';
    }

    public function findMatchingTransitions(Uuid $workflowId, Uuid $fromNodeId, array $context): array
    {
        $workflow = $this->workflowRepository->findById($workflowId);
        if (!$workflow) {
            return [];
        }

        $matchingTransitions = [];
        foreach ($workflow->getTransitions() as $transition) {
            if ($transition['from_node_id'] === $fromNodeId->value) {
                if ($this->evaluateTransitionConditions($transition, $context)) {
                    $matchingTransitions[] = $transition;
                }
            }
        }

        return $matchingTransitions;
    }

    private function evaluateTransitionConditions(array $transition, array $context): bool
    {
        if (!isset($transition['conditions']) || empty($transition['conditions'])) {
            return true;
        }

        return $this->evaluateConditions($transition['conditions'], $context);
    }

    public function determineNextNode(array $transitions, array $context): ?string
    {
        // If multiple transitions match, use priority or first match
        foreach ($transitions as $transition) {
            if ($this->evaluateTransitionConditions($transition, $context)) {
                return $transition['to_node_id'];
            }
        }

        return null;
    }

    public function validateWorkflow(Uuid $workflowId): array
    {
        $workflow = $this->workflowRepository->findById($workflowId);
        if (!$workflow) {
            return ['valid' => false, 'errors' => ['Workflow not found']];
        }

        $errors = [];

        // Check if workflow has start and end nodes
        if ($workflow->getStartNodeId() === null) {
            $errors[] = 'Workflow has no start node';
        }

        if ($workflow->getEndNodeId() === null) {
            $errors[] = 'Workflow has no end node';
        }

        // Check if all transitions reference valid nodes
        $nodeIds = array_keys($workflow->getNodes());
        foreach ($workflow->getTransitions() as $transition) {
            if (!in_array($transition['from_node_id'], $nodeIds)) {
                $errors[] = "Transition references invalid from_node: {$transition['from_node_id']}";
            }
            if (!in_array($transition['to_node_id'], $nodeIds)) {
                $errors[] = "Transition references invalid to_node: {$transition['to_node_id']}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function canAutoTransition(Uuid $workflowId, Uuid $fromNodeId, array $context): bool
    {
        $transitions = $this->findMatchingTransitions($workflowId, $fromNodeId, $context);
        
        foreach ($transitions as $transition) {
            if ($transition['type'] === 'automatic') {
                return true;
            }
        }

        return false;
    }

    public function getAvailableActions(Uuid $workflowId, Uuid $nodeId, array $context): array
    {
        $workflow = $this->workflowRepository->findById($workflowId);
        if (!$workflow) {
            return [];
        }

        $node = $workflow->getNodes()[$nodeId->value] ?? null;
        if (!$node) {
            return [];
        }

        return $node['config']['actions'] ?? [];
    }

    public function executeRule(string $ruleType, array $context): mixed
    {
        return match($ruleType) {
            'amount_threshold' => $this->evaluateAmountThreshold($context),
            'date_range' => $this->evaluateDateRange($context),
            'user_role' => $this->evaluateUserRole($context),
            'custom' => $context['result'] ?? true,
            default => null,
        };
    }

    private function evaluateAmountThreshold(array $context): bool
    {
        $amount = $context['amount'] ?? 0;
        $threshold = $context['threshold'] ?? 0;
        $operator = $context['operator'] ?? 'gt';

        return match($operator) {
            'gt' => $amount > $threshold,
            'gte' => $amount >= $threshold,
            'lt' => $amount < $threshold,
            'lte' => $amount <= $threshold,
            'eq' => $amount == $threshold,
            default => false,
        };
    }

    private function evaluateDateRange(array $context): bool
    {
        $date = $context['date'] ?? new \DateTime();
        $start = $context['start_date'] ?? null;
        $end = $context['end_date'] ?? null;

        if ($start && $date < new \DateTime($start)) {
            return false;
        }
        if ($end && $date > new \DateTime($end)) {
            return false;
        }

        return true;
    }

    private function evaluateUserRole(array $context): bool
    {
        $userRole = $context['user_role'] ?? '';
        $allowedRoles = $context['allowed_roles'] ?? [];

        return in_array($userRole, $allowedRoles);
    }
}
