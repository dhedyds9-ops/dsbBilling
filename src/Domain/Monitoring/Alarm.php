<?php

namespace Src\Domain\Monitoring;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class Alarm extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $deviceId,
        public readonly Uuid $metricId,
        public readonly string $deviceType,
        public readonly string $metricName,
        public readonly AlarmSeverity $severity,
        public AlarmStatus $status,
        public readonly string $message,
        public readonly float $value,
        public readonly float $threshold,
        public readonly DateTimeImmutable $triggeredAt,
        public ?DateTimeImmutable $acknowledgedAt = null,
        public ?int $acknowledgedBy = null,
        public ?DateTimeImmutable $resolvedAt = null,
        public ?int $resolvedBy = null,
    ) {}

    public static function create(
        Uuid $deviceId,
        Uuid $metricId,
        string $deviceType,
        string $metricName,
        AlarmSeverity $severity,
        string $message,
        float $value,
        float $threshold,
    ): self {
        return new self(
            Uuid::random(),
            $deviceId,
            $metricId,
            $deviceType,
            $metricName,
            $severity,
            AlarmStatus::OPEN,
            $message,
            $value,
            $threshold,
            new DateTimeImmutable(),
        );
    }

    public function acknowledge(int $userId): void
    {
        $this->status = AlarmStatus::ACKNOWLEDGED;
        $this->acknowledgedAt = new DateTimeImmutable();
        $this->acknowledgedBy = $userId;
    }

    public function resolve(int $userId): void
    {
        $this->status = AlarmStatus::RESOLVED;
        $this->resolvedAt = new DateTimeImmutable();
        $this->resolvedBy = $userId;
    }

    public function close(): void
    {
        if ($this->status !== AlarmStatus::RESOLVED) {
            throw new \RuntimeException("Cannot close unresolved alarm");
        }
        $this->status = AlarmStatus::CLOSED;
    }
}
