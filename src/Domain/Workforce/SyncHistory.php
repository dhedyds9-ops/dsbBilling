<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\SyncTaskType;

class SyncHistory extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $userId,
        public readonly SyncTaskType $type,
        public readonly int $itemsSynced,
        public readonly ?int $itemsFailed = 0,
        public readonly ?DateTimeImmutable $startedAt = null,
        public readonly ?DateTimeImmutable $completedAt = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $userId,
        SyncTaskType $type,
        int $itemsSynced,
        int $itemsFailed = 0,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $completedAt = null,
    ): self {
        return new self(
            Uuid::random(),
            $userId,
            $type,
            $itemsSynced,
            $itemsFailed,
            $startedAt,
            $completedAt,
        );
    }
}
