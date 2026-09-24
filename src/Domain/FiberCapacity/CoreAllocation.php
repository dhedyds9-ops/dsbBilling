<?php

namespace Src\Domain\FiberCapacity;

use DateTimeImmutable;
use Src\Domain\FiberCapacity\Enums\AllocationStatus;
use Src\Domain\FiberCapacity\Events\CoreAllocated;
use Src\Domain\FiberCapacity\Events\CoreReleased;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoreAllocation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $fiberCoreId,
        public readonly Uuid $fiberCableId,
        public readonly int $coreNumber,
        public readonly string $allocatedTo,
        public readonly string $allocationType,
        public AllocationStatus $status,
        public DateTimeImmutable $allocatedAt,
        public ?DateTimeImmutable $releasedAt = null,
        public ?string $reason = null,
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $fiberCoreId,
        Uuid $fiberCableId,
        int $coreNumber,
        string $allocatedTo,
        string $allocationType
    ): self {
        return new self(
            $id,
            $fiberCoreId,
            $fiberCableId,
            $coreNumber,
            $allocatedTo,
            $allocationType,
            AllocationStatus::ACTIVE,
            new DateTimeImmutable()
        );
    }

    public function release(?string $reason = null): void
    {
        if ($this->status !== AllocationStatus::ACTIVE) {
            throw new \InvalidArgumentException("Allocation is not active");
        }

        $this->status = AllocationStatus::RELEASED;
        $this->releasedAt = new DateTimeImmutable();
        $this->reason = $reason;

        $this->recordThat(new CoreReleased(
            $this->fiberCoreId->value,
            $this->fiberCableId->value,
            $this->coreNumber,
            $this->allocatedTo,
            $reason
        ));
    }

    public function approve(): void
    {
        if ($this->status !== AllocationStatus::PENDING) {
            throw new \InvalidArgumentException("Allocation is not pending");
        }

        $this->status = AllocationStatus::APPROVED;
    }

    public function expire(): void
    {
        if ($this->status !== AllocationStatus::ACTIVE) {
            throw new \InvalidArgumentException("Allocation is not active");
        }

        $this->status = AllocationStatus::EXPIRED;
        $this->releasedAt = new DateTimeImmutable();

        $this->recordThat(new CoreReleased(
            $this->fiberCoreId->value,
            $this->fiberCableId->value,
            $this->coreNumber,
            $this->allocatedTo,
            'expired'
        ));
    }

    public function isActive(): bool
    {
        return $this->status === AllocationStatus::ACTIVE;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getDuration(): ?int
    {
        if ($this->releasedAt === null) {
            return null;
        }
        
        return $this->releasedAt->getTimestamp() - $this->allocatedAt->getTimestamp();
    }
}
