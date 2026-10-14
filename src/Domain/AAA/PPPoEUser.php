<?php

namespace Src\Domain\AAA;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class PPPoEUser extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $username,
        public string $password,
        public readonly Uuid $customerServiceId,
        public readonly Uuid $serviceProfileId,
        public readonly ?Uuid $ipAllocationId = null,
        public string $status = 'active',
        public readonly ?\DateTimeImmutable $activatedAt = null,
        public readonly ?\DateTimeImmutable $suspendedAt = null,
        public readonly ?\DateTimeImmutable $terminatedAt = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        Uuid $id,
        string $username,
        string $password,
        Uuid $customerServiceId,
        Uuid $serviceProfileId,
        ?Uuid $ipAllocationId = null,
    ): self {
        return new self(
            id: $id,
            username: $username,
            password: $password,
            customerServiceId: $customerServiceId,
            serviceProfileId: $serviceProfileId,
            ipAllocationId: $ipAllocationId,
            status: 'active',
            activatedAt: new \DateTimeImmutable(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
        $this->suspendedAt = new \DateTimeImmutable();
    }

    public function reactivate(): void
    {
        $this->status = 'active';
        $this->activatedAt = new \DateTimeImmutable();
        $this->suspendedAt = null;
    }

    public function terminate(): void
    {
        $this->status = 'terminated';
        $this->terminatedAt = new \DateTimeImmutable();
    }
}
