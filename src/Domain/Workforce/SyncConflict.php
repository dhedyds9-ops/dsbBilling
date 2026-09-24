<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\SyncConflictResolutionType;

class SyncConflict extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $taskId,
        public readonly Uuid $userId,
        public readonly string $modelType,
        public readonly Uuid $modelId,
        public readonly array $serverData,
        public readonly array $clientData,
        public SyncConflictResolutionType $resolutionType,
        public readonly ?array $resolvedData = null,
        public readonly ?bool $isResolved = false,
        public readonly ?Uuid $resolvedBy = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?DateTimeImmutable $resolvedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $taskId,
        Uuid $userId,
        string $modelType,
        Uuid $modelId,
        array $serverData,
        array $clientData,
    ): self {
        return new self(
            Uuid::random(),
            $taskId,
            $userId,
            $modelType,
            $modelId,
            $serverData,
            $clientData,
            SyncConflictResolutionType::SERVER_WINS,
        );
    }

    public function resolve(
        SyncConflictResolutionType $type,
        ?array $resolvedData = null,
        ?Uuid $resolvedBy = null,
    ): void {
        $this->resolutionType = $type;
        $this->resolvedData = $resolvedData;
        $this->resolvedBy = $resolvedBy;
        $this->isResolved = true;
        $this->resolvedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }
}
