<?php

namespace Src\Domain\Workflow\ValueObjects;

readonly class ApprovalConfig
{
    public function __construct(
        public int $requiredApprovals = 1,
        public bool $allowSelfApproval = false,
        public bool $requireJustification = false,
        public ?int $slaHours = null,
        public array $escalationUsers = [],
        public array $escalationRoles = [],
        public ?string $approvalStrategy = 'any' // 'any', 'all', 'majority'
    ) {}

    public static function singleApproval(): self
    {
        return new self(requiredApprovals: 1);
    }

    public static function unanimous(int $slaHours = null): self
    {
        return new self(
            requiredApprovals: 2,
            requireJustification: true,
            slaHours: $slaHours,
            approvalStrategy: 'all'
        );
    }

    public static function majority(int $slaHours = null): self
    {
        return new self(
            requiredApprovals: 2,
            slaHours: $slaHours,
            approvalStrategy: 'majority'
        );
    }

    public function withEscalation(array $users = [], array $roles = []): self
    {
        return new self(
            requiredApprovals: $this->requiredApprovals,
            allowSelfApproval: $this->allowSelfApproval,
            requireJustification: $this->requireJustification,
            slaHours: $this->slaHours,
            escalationUsers: $users,
            escalationRoles: $roles,
            approvalStrategy: $this->approvalStrategy
        );
    }

    public function toArray(): array
    {
        return [
            'required_approvals' => $this->requiredApprovals,
            'allow_self_approval' => $this->allowSelfApproval,
            'require_justification' => $this->requireJustification,
            'sla_hours' => $this->slaHours,
            'escalation_users' => $this->escalationUsers,
            'escalation_roles' => $this->escalationRoles,
            'approval_strategy' => $this->approvalStrategy,
        ];
    }
}
