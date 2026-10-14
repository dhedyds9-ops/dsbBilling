<?php

namespace Src\Domain\Payment;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Money;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';
}

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case E_WALLET = 'e_wallet';
}

class Payment extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $customerId,
        public Money $amount,
        public PaymentMethod $method,
        public PaymentStatus $status = PaymentStatus::PENDING,
        public ?string $referenceNumber = null,
        public ?DateTimeImmutable $paidAt = null,
        public array $invoiceIds = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $customerId,
        Money $amount,
        PaymentMethod $method,
        array $invoiceIds = []
    ): self {
        return new self($id, $customerId, $amount, $method, PaymentStatus::PENDING, null, null, $invoiceIds);
    }

    public function complete(?string $referenceNumber = null): void
    {
        $this->status = PaymentStatus::COMPLETED;
        $this->referenceNumber = $referenceNumber;
        $this->paidAt = new DateTimeImmutable();
    }

    public function fail(): void
    {
        $this->status = PaymentStatus::FAILED;
    }

    public function refund(): void
    {
        $this->status = PaymentStatus::REFUNDED;
    }

    public function cancel(): void
    {
        $this->status = PaymentStatus::CANCELLED;
    }
}
