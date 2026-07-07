<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AssetAssignment extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $assetId,
        public readonly Uuid $assignedToId,
        public readonly string $assignedToType,
        public readonly Uuid $assignedBy,
        public readonly DateTimeImmutable $assignedAt,
        public readonly ?DateTimeImmutable $returnedAt = null,
        public readonly ?Uuid $returnAcceptedBy = null,
        public readonly ?string $notes = null,
        public readonly ?string $conditionUponReturn = null
    ) {}

    public static function create(
        Uuid $assetId,
        Uuid $assignedToId,
        string $assignedToType,
        Uuid $assignedBy,
        ?string $notes = null
    ): self {
        return new self(
            id: Uuid::generate(),
            assetId: $assetId,
            assignedToId: $assignedToId,
            assignedToType: $assignedToType,
            assignedBy: $assignedBy,
            assignedAt: new DateTimeImmutable(),
            returnedAt: null,
            returnAcceptedBy: null,
            notes: $notes,
            conditionUponReturn: null
        );
    }

    public function return(?Uuid $acceptedBy, ?string $condition): void
    {
        $this->returnedAt = new DateTimeImmutable();
        $this->returnAcceptedBy = $acceptedBy;
        $this->conditionUponReturn = $condition;
    }

    public function isActive(): bool
    {
        return $this->returnedAt === null;
    }

    public function getDurationDays(): int
    {
        $end = $this->returnedAt ?? new DateTimeImmutable();
        return $this->assignedAt->diff($end)->days;
    }
}
