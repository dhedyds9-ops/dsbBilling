<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class VLANAllocation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceInstanceId,
        public readonly Uuid $vlanId,
        public readonly int $vlanTag,
        public bool $active = true,
        public DateTimeImmutable $allocatedAt,
        public ?DateTimeImmutable $releasedAt = null
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceInstanceId,
        Uuid $vlanId,
        int $vlanTag
    ): self {
        return new self(
            $id,
            $serviceInstanceId,
            $vlanId,
            $vlanTag,
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
