<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class IPAllocation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $serviceInstanceId,
        public readonly Uuid $ipPoolId,
        public readonly string $ipAddress,
        public readonly ?string $subnetMask = null,
        public readonly ?string $gateway = null,
        public bool $active = true,
        public DateTimeImmutable $allocatedAt,
        public ?DateTimeImmutable $releasedAt = null
    ) {}

    public static function create(
        Uuid $id,
        Uuid $serviceInstanceId,
        Uuid $ipPoolId,
        string $ipAddress,
        ?string $subnetMask = null,
        ?string $gateway = null
    ): self {
        return new self(
            $id,
            $serviceInstanceId,
            $ipPoolId,
            $ipAddress,
            $subnetMask,
            $gateway,
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
