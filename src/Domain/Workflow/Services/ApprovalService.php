<?php

namespace Src\Domain\Workflow\Services;

use Src\Domain\Workflow\WorkflowApproval;
use Src\Domain\Workflow\WorkflowTask;
use Src\Domain\Workflow\Events\WorkflowApproved;
use Src\Domain\Workflow\Events\WorkflowRejected;
use Src\Domain\Workflow\Repositories\WorkflowApprovalRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowTaskRepositoryInterface;
use Src\Domain\Workflow\Repositories\WorkflowInstanceRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Illuminate\Support\Facades\Event;

class ApprovalService
{
    public function __construct(
        private readonly WorkflowApprovalRepositoryInterface $approvalRepository,
        private readonly WorkflowTaskRepositoryInterface $taskRepository,
        private readonly WorkflowInstanceRepositoryInterface $instanceRepository
    ) {}

    public function createApproval(
        Uuid $taskId,
        Uuid $instanceId,
        string $title,
        string $description,
        array $approverIds,
        array $config = [],
        ?int $slaHours = null
    ): WorkflowApproval {
        $approvalConfig = new \Src\Domain\Workflow\ValueObjects\ApprovalConfig(
            requiredApprovals: $config['required_approvals'] ?? 1,
            allowSelfApproval: $config['allow_self_approval'] ?? false,
            requireJustification: $config['require_justification'] ?? false,
            slaHours: $slaHours,
            escalationUsers: $config['escalation_users'] ?? [],
            escalationRoles: $config['escalation_roles'] ?? [],
            approvalStrategy: $config['approval_strategy'] ?? 'any'
        );

        $approval = new WorkflowApproval(
            id: Uuid::generate(),
            taskId: $taskId,
            instanceId: $instanceId,
            title: $title,
            description: $description,
            config: $approvalConfig,
            requestedBy: new Uuid($config['requested_by'] ?? ''),
            slaTimer: $slaHours ? \Src\Domain\Workflow\ValueObjects\SLATimer::fromHours($slaHours) : null
        );

        $this->approvalRepository->save($approval);
        return $approval;
    }

    public function approve(
        Uuid $approvalId,
        Uuid $approverId,
        string $approverType = 'user',
        ?string $comment = null
    ): WorkflowApproval {
        $approval = $this->approvalRepository->findById($approvalId);
        if (!$approval) {
            throw new \DomainException("Approval not found");
        }

        if (!$approval->canSelfApprove($approverId)) {
            throw new \DomainException("User cannot approve their own request");
        }

        $approval->approve($approverId, $approverType, $comment);
        $this->approvalRepository->save($approval);

        Event::dispatch(new WorkflowApproved(
            $approval->instanceId,
            $approval->instanceId, // Would be workflow ID
            $approverId,
            'approved',
            $comment
        ));

        return $approval;
    }

    public function reject(
        Uuid $approvalId,
        Uuid $rejecterId,
        string $reason
    ): WorkflowApproval {
        $approval = $this->approvalRepository->findById($approvalId);
        if (!$approval) {
            throw new \DomainException("Approval not found");
        }

        $approval->reject($rejecterId, 'user', $reason);
        $this->approvalRepository->save($approval);

        Event::dispatch(new WorkflowRejected(
            $approval->instanceId,
            $approval->instanceId,
            $rejecterId,
            $reason
        ));

        // Cancel the workflow instance
        $instance = $this->instanceRepository->findById($approval->instanceId);
        if ($instance) {
            $instance->cancel($reason);
            $this->instanceRepository->save($instance);
        }

        return $approval;
    }

    public function escalateApproval(Uuid $approvalId, array $escalationUsers): WorkflowApproval
    {
        $approval = $this->approvalRepository->findById($approvalId);
        if (!$approval) {
            throw new \DomainException("Approval not found");
        }

        $approval->escalate();
        $this->approvalRepository->save($approval);

        return $approval;
    }

    public function getPendingApprovals(?Uuid $approverId = null): array
    {
        return $this->approvalRepository->findPending($approverId);
    }

    public function getApprovalHistory(Uuid $instanceId): array
    {
        return $this->approvalRepository->findByInstance($instanceId);
    }

    public function getOverdueApprovals(): array
    {
        return $this->approvalRepository->findOverdue();
    }

    public function canAutoApprove(Uuid $approvalId): bool
    {
        $approval = $this->approvalRepository->findById($approvalId);
        if (!$approval) {
            return false;
        }

        // Check if conditions for auto-approval are met
        return false; // Simplified
    }

    public function getApprovalStats(?Uuid $workflowId = null): array
    {
        return [
            'pending' => count($this->approvalRepository->findPending()),
            'approved_today' => 0, // Would query by date
            'rejected_today' => 0,
            'avg_approval_time_hours' => 0,
        ];
    }
}
