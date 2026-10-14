<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class QueueAllocation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceInstanceId,
        public readonly Uuid $deviceId,
        public readonly int $queueId,
        public readonly int $priority,
        public readonly ?int $bandwidthLimit = null,
        public bool $active = true,
        public DateTimeImmutable $allocatedAt,
        public ?DateTimeImmutable $releasedAt = null
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceInstanceId,
        Uuid $deviceId,
        int $queueId,
        int $priority,
        ?int $bandwidthLimit = null
    ): self {
        return new self(
            $id,
            $serviceInstanceId,
            $deviceId,
            $queueId,
            $priority,
            $bandwidthLimit,
            true,
            new DateTimeImmutable()
        );
    }

    public function release(): void
    {
        $this->active = false;
        $this->releasedAt = new DateTimeImmutable();
    }
}
