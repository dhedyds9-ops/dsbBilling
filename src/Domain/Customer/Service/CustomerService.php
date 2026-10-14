<?php

namespace Src\Domain\Customer\Service;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use App\Models\ISP\Onu;

enum CustomerServiceStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DISCONNECTED = 'disconnected';
}

class CustomerService extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $customerId,
        public readonly Uuid $contractId,
        public readonly Uuid $serviceId,
        public ?Uuid $onuId = null,
        public string $username,
        public string $password,
        public CustomerServiceStatus $status = CustomerServiceStatus::PENDING,
        public ?DateTimeImmutable $activatedAt = null,
        public ?DateTimeImmutable $suspendedAt = null,
        public array $attributes = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $customerId,
        Uuid $contractId,
        Uuid $serviceId,
        string $username,
        string $password,
        ?Uuid $onuId = null,
        array $attributes = []
    ): self {
        return new self($id, $customerId, $contractId, $serviceId, $onuId, $username, $password, CustomerServiceStatus::PENDING, null, null, $attributes);
    }

    public function activate(): void
    {
        $this->status = CustomerServiceStatus::ACTIVE;
        $this->activatedAt = new DateTimeImmutable();
    }

    public function suspend(): void
    {
        $this->status = CustomerServiceStatus::SUSPENDED;
        $this->suspendedAt = new DateTimeImmutable();
    }

    public function disconnect(): void
    {
        $this->status = CustomerServiceStatus::DISCONNECTED;
    }

    public function assignOnu(Uuid $onuId): void
    {
        $this->onuId = $onuId;
    }
}
