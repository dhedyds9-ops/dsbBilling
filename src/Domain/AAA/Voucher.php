<?php

namespace Src\Domain\AAA;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class Voucher extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $code,
        public readonly string $type, // 'time', 'data', 'quota'
        public readonly int $value,
        public readonly int $duration, // in minutes
        public readonly int $quota, // in MB
        public readonly Uuid $serviceProfileId,
        public string $status = 'available',
        public readonly ?Uuid $customerServiceId = null,
        public readonly ?\DateTimeImmutable $activatedAt = null,
        public readonly ?\DateTimeImmutable $expiredAt = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        Uuid $id,
        string $code,
        string $type,
        int $value,
        int $duration,
        int $quota,
        Uuid $serviceProfileId,
    ): self {
        return new self(
            id: $id,
            code: $code,
            type: $type,
            value: $value,
            duration: $duration,
            quota: $quota,
            serviceProfileId: $serviceProfileId,
            status: 'available',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function activate(Uuid $customerServiceId): void
    {
        $this->status = 'active';
        $this->customerServiceId = $customerServiceId;
        $this->activatedAt = new \DateTimeImmutable();
        if ($this->duration > 0) {
            $this->expiredAt = (new \DateTimeImmutable())->add(new \DateInterval("PT{$this->duration}M"));
        }
    }
}
