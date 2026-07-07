<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ResourceAssignment extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceInstanceId,
        public readonly ResourceType $resourceType,
        public readonly Uuid $resourceId,
        public readonly Uuid $reservationId,
        public DateTimeImmutable $assignedAt,
        public ?DateTimeImmutable $revokedAt = null,
        public bool $active = true,
        public array $details = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceInstanceId,
        ResourceType $resourceType,
        Uuid $resourceId,
        Uuid $reservationId
    ): self {
        return new self(
            $id,
            $serviceInstanceId,
            $resourceType,
            $resourceId,
            $reservationId,
            new DateTimeImmutable()
        );
    }

    public function revoke(): void
    {
        $this->active = false;
        $this->revokedAt = new DateTimeImmutable();
    }
}
