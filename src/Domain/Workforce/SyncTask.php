<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\SyncTaskStatus;
use Src\Domain\Workforce\Enums\SyncTaskType;

class SyncTask extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $userId,
        public readonly SyncTaskType $type,
        public SyncTaskStatus $status,
        public readonly array $payload,
        public readonly ?string $modelType = null,
        public readonly ?Uuid $modelId = null,
        public readonly ?int $retryCount = 0,
        public readonly ?DateTimeImmutable $lastRetryAt = null,
        public readonly ?string $errorMessage = null,
        public readonly ?bool $isCompressed = false,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        Uuid $userId,
        SyncTaskType $type,
        array $payload,
        ?string $modelType = null,
        ?Uuid $modelId = null,
        ?bool $isCompressed = false,
    ): self {
        return new self(
            Uuid::random(),
            $userId,
            $type,
            SyncTaskStatus::PENDING,
            $payload,
            $modelType,
            $modelId,
            0,
            null,
            null,
            $isCompressed,
        );
    }

    public function markAsProcessing(): void {
        $this->status = SyncTaskStatus::PROCESSING;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsCompleted(): void {
        $this->status = SyncTaskStatus::COMPLETED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsFailed(string $errorMessage): void {
        $this->status = SyncTaskStatus::FAILED;
        $this->errorMessage = $errorMessage;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsConflict(): void {
        $this->status = SyncTaskStatus::CONFLICT;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsCancelled(): void {
        $this->status = SyncTaskStatus::CANCELLED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function incrementRetry(): void {
        $this->retryCount = $this->retryCount + 1;
        $this->lastRetryAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }
}
