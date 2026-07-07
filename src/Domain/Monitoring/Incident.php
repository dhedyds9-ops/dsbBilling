<?php

namespace Src\Domain\Monitoring;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class Incident extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $rootAlarmId,
        public readonly array $relatedAlarmIds,
        public readonly string $category,
        public readonly AlarmSeverity $severity,
        public IncidentStatus $status,
        public readonly string $description,
        public readonly DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $assignedAt = null,
        public ?int $assignedTo = null,
        public ?DateTimeImmutable $resolvedAt = null,
        public ?string $rootCause = null,
        public ?string $resolution = null,
        public ?DateTimeImmutable $closedAt = null,
    ) {}

    public static function create(
        Uuid $rootAlarmId,
        array $relatedAlarmIds,
        string $category,
        AlarmSeverity $severity,
        string $description,
    ): self {
        return new self(
            Uuid::random(),
            $rootAlarmId,
            $relatedAlarmIds,
            $category,
            $severity,
            IncidentStatus::OPEN,
            $description,
            new DateTimeImmutable(),
        );
    }

    public function assign(int $userId): void
    {
        $this->status = IncidentStatus::ASSIGNED;
        $this->assignedAt = new DateTimeImmutable();
        $this->assignedTo = $userId;
    }

    public function startProgress(): void
    {
        $this->status = IncidentStatus::IN_PROGRESS;
    }

    public function resolve(string $rootCause, string $resolution): void
    {
        $this->status = IncidentStatus::RESOLVED;
        $this->rootCause = $rootCause;
        $this->resolution = $resolution;
        $this->resolvedAt = new DateTimeImmutable();
    }

    public function close(): void
    {
        if ($this->status !== IncidentStatus::RESOLVED) {
            throw new \RuntimeException("Cannot close unresolved incident");
        }
        $this->status = IncidentStatus::CLOSED;
        $this->closedAt = new DateTimeImmutable();
    }

    public function escalate(): void
    {
        $this->status = IncidentStatus::ESCALATED;
    }

    public function getDuration(): int
    {
        $end = $this->resolvedAt ?? $this->closedAt ?? new DateTimeImmutable();
        return $end->getTimestamp() - $this->startedAt->getTimestamp();
    }
}
