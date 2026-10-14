<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AssetReturn extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $assetId,
        public readonly Uuid $returnedBy,
        public readonly Uuid $receivedBy,
        public readonly DateTimeImmutable $returnedAt,
        public readonly string $condition,
        public readonly ?string $notes = null,
        public readonly ?Uuid $assignmentId = null,
        public readonly string $reason = 'normal_return'
    ) {}

    public const REASON_NORMAL_RETURN = 'normal_return';
    public const REASON_REPAIR = 'repair';
    public const REASON_REPLACEMENT = 'replacement';
    public const REASON_END_OF_LEASE = 'end_of_lease';
    public const REASON_OTHER = 'other';

    public static function create(
        Uuid $assetId,
        Uuid $returnedBy,
        Uuid $receivedBy,
        string $condition,
        ?Uuid $assignmentId = null,
        string $reason = self::REASON_NORMAL_RETURN,
        ?string $notes = null
    ): self {
        return new self(
            id: Uuid::generate(),
            assetId: $assetId,
            returnedBy: $returnedBy,
            receivedBy: $receivedBy,
            returnedAt: new DateTimeImmutable(),
            condition: $condition,
            notes: $notes,
            assignmentId: $assignmentId,
            reason: $reason
        );
    }

    public function requiresInspection(): bool
    {
        return in_array($this->condition, ['damaged', 'poor']);
    }

    public function requiresRepair(): bool
    {
        return $this->condition === 'damaged';
    }
}
