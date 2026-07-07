<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class SyncQueue extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $userId,
        public readonly array $taskIds,
        public readonly ?DateTimeImmutable $lastSyncedAt = null,
        public readonly ?int $pendingCount = 0,
        public readonly ?bool $isOnline = true,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(Uuid $userId): self {
        return new self(
            Uuid::random(),
            $userId,
            [],
            null,
            0,
            true,
        );
    }

    public function addTask(Uuid $taskId): void {
        if (!in_array($taskId, $this->taskIds)) {
            $this->taskIds[] = $taskId;
            $this->pendingCount = count($this->taskIds);
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeTask(Uuid $taskId): void {
        $this->taskIds = array_filter($this->taskIds, fn($id) => $id !== $taskId);
        $this->pendingCount = count($this->taskIds);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsOnline(): void {
        $this->isOnline = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsOffline(): void {
        $this->isOnline = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateLastSyncedAt(): void {
        $this->lastSyncedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }
}
