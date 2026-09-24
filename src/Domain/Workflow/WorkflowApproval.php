<?php

namespace Src\Domain\Workflow;

use Src\Domain\Workflow\Enums\ApprovalStatus;
use Src\Domain\Workflow\ValueObjects\ApprovalConfig;
use Src\Domain\Workflow\ValueObjects\SLATimer;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class WorkflowApproval extends AggregateRoot
{
    private ApprovalStatus $status;
    private array $approvals = [];
    private ?DateTimeImmutable $completedAt = null;
    private ?string $finalDecision = null;
    private ?string $summary = null;

    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $taskId,
        public readonly Uuid $instanceId,
        public readonly string $title,
        public readonly string $description,
        public readonly ApprovalConfig $config,
        public readonly Uuid $requestedBy,
        public readonly ?SLATimer $slaTimer = null,
        public readonly ?Uuid $deadline = null
    ) {
        $this->status = ApprovalStatus::PENDING;
    }

    public function getStatus(): ApprovalStatus
    {
        return $this->status;
    }

    public function getApprovals(): array
    {
        return $this->approvals;
    }

    public function addApproval(
        Uuid $approverId,
        string $approverType,
        ApprovalStatus $status,
        ?string $comment = null
    ): void {
        $this->approvals[] = [
            'id' => Uuid::generate()->value,
            'approver_id' => $approverId->value,
            'approver_type' => $approverType,
            'status' => $status->value,
            'comment' => $comment,
            'decided_at' => $status->isTerminal() ? (new DateTimeImmutable())->format('c') : null,
        ];
    }

    public function approve(Uuid $approverId, string $approverType, ?string $comment = null): void
    {
        if ($this->status->isTerminal()) {
            throw new \DomainException("Approval has already been decided");
        }

        $this->addApproval($approverId, $approverType, ApprovalStatus::APPROVED, $comment);

        $approvedCount = $this->getApprovedCount();
        
        if ($this->config->approvalStrategy === 'any') {
            $this->complete('approved', 'Approved by at least one approver');
        } elseif ($this->config->approvalStrategy === 'majority') {
            if ($approvedCount >= ceil($this->config->requiredApprovals / 2)) {
                $this->complete('approved', 'Approved by majority');
            }
        } elseif ($this->config->approvalStrategy === 'all') {
            if ($approvedCount >= $this->config->requiredApprovals) {
                $this->complete('approved', 'Unanimously approved');
            }
        }
    }

    public function reject(Uuid $approverId, string $approverType, string $reason): void
    {
        if ($this->status->isTerminal()) {
            throw new \DomainException("Approval has already been decided");
        }

        $this->addApproval($approverId, $approverType, ApprovalStatus::REJECTED, $reason);
        $this->complete('rejected', $reason);
    }

    public function skip(Uuid $skippedBy, string $reason): void
    {
        if ($this->status->isTerminal()) {
            throw new \DomainException("Approval has already been decided");
        }

        $this->addApproval($skippedBy, 'system', ApprovalStatus::SKIPPED, $reason);
        $this->complete('skipped', $reason);
    }

    public function escalate(): void
    {
        $this->status = ApprovalStatus::ESCALATED;
    }

    private function complete(string $decision, string $summary): void
    {
        $this->status = $decision === 'approved' ? ApprovalStatus::APPROVED : 
                       ($decision === 'rejected' ? ApprovalStatus::REJECTED : ApprovalStatus::SKIPPED);
        $this->finalDecision = $decision;
        $this->summary = $summary;
        $this->completedAt = new DateTimeImmutable();
    }

    private function getApprovedCount(): int
    {
        return count(array_filter(
            $this->approvals, 
            fn($a) => $a['status'] === ApprovalStatus::APPROVED->value
        ));
    }

    private function getRejectedCount(): int
    {
        return count(array_filter(
            $this->approvals, 
            fn($a) => $a['status'] === ApprovalStatus::REJECTED->value
        ));
    }

    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::PENDING;
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function getDecision(): ?string
    {
        return $this->finalDecision;
    }

    public function canSelfApprove(Uuid $userId): bool
    {
        return $this->config->allowSelfApproval || !$this->isUserApprover($userId);
    }

    private function isUserApprover(Uuid $userId): bool
    {
        foreach ($this->approvals as $approval) {
            if ($approval['approver_id'] === $userId->value) {
                return true;
            }
        }
        return false;
    }
}
