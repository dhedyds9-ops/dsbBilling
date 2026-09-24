<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum ServiceInstanceStatus: string
{
    case PENDING = 'pending';
    case RESERVING = 'reserving';
    case RESERVED = 'reserved';
    case ASSIGNING = 'assigning';
    case ASSIGNED = 'assigned';
    case PROVISIONING = 'provisioning';
    case PROVISIONED = 'provisioned';
    case VERIFIED = 'verified';
    case ACTIVE = 'active';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}

class ServiceInstance extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $customerServiceId,
        public readonly Uuid $provisionPipelineId,
        public ServiceInstanceStatus $status = ServiceInstanceStatus::PENDING,
        public ?DateTimeImmutable $reservedAt = null,
        public ?DateTimeImmutable $assignedAt = null,
        public ?DateTimeImmutable $provisionedAt = null,
        public ?DateTimeImmutable $verifiedAt = null,
        public ?DateTimeImmutable $activatedAt = null,
        public array $resources = [],
        public array $steps = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $customerServiceId,
        Uuid $provisionPipelineId
    ): self {
        return new self($id, $customerServiceId, $provisionPipelineId);
    }

    public function startReserving(): void
    {
        $this->status = ServiceInstanceStatus::RESERVING;
    }

    public function markReserved(): void
    {
        $this->status = ServiceInstanceStatus::RESERVED;
        $this->reservedAt = new DateTimeImmutable();
    }

    public function startAssigning(): void
    {
        $this->status = ServiceInstanceStatus::ASSIGNING;
    }

    public function markAssigned(): void
    {
        $this->status = ServiceInstanceStatus::ASSIGNED;
        $this->assignedAt = new DateTimeImmutable();
    }

    public function startProvisioning(): void
    {
        $this->status = ServiceInstanceStatus::PROVISIONING;
    }

    public function markProvisioned(): void
    {
        $this->status = ServiceInstanceStatus::PROVISIONED;
        $this->provisionedAt = new DateTimeImmutable();
    }

    public function markVerified(): void
    {
        $this->status = ServiceInstanceStatus::VERIFIED;
        $this->verifiedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = ServiceInstanceStatus::ACTIVE;
        $this->activatedAt = new DateTimeImmutable();
    }

    public function fail(): void
    {
        $this->status = ServiceInstanceStatus::FAILED;
    }

    public function cancel(): void
    {
        $this->status = ServiceInstanceStatus::CANCELLED;
    }

    public function addResource(string $type, Uuid $resourceId): void
    {
        $this->resources[] = [
            'type' => $type,
            'id' => $resourceId,
            'assigned_at' => new DateTimeImmutable()
        ];
    }

    public function addStep(string $stepName, string $status, ?string $error = null): void
    {
        $this->steps[] = [
            'name' => $stepName,
            'status' => $status,
            'error' => $error,
            'timestamp' => new DateTimeImmutable()
        ];
    }
}
