<?php

namespace Src\Domain\Customer\Contract;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum ContractStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case TERMINATED = 'terminated';
    case EXPIRED = 'expired';
}

class Contract extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $customerId,
        public string $contractNumber,
        public DateTimeImmutable $startDate,
        public ?DateTimeImmutable $endDate = null,
        public ContractStatus $status = ContractStatus::DRAFT,
        public array $terms = [],
        public array $services = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $customerId,
        string $contractNumber,
        DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate = null,
        array $terms = []
    ): self {
        return new self($id, $customerId, $contractNumber, $startDate, $endDate, ContractStatus::DRAFT, $terms);
    }

    public function activate(): void
    {
        $this->status = ContractStatus::ACTIVE;
    }

    public function suspend(): void
    {
        $this->status = ContractStatus::SUSPENDED;
    }

    public function terminate(): void
    {
        $this->status = ContractStatus::TERMINATED;
    }

    public function addService(Uuid $serviceId): void
    {
        $this->services[] = $serviceId;
    }
}
