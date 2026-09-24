<?php

namespace Src\Domain\Billing;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Money;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case VOID = 'void';
}

class Invoice extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $customerId,
        public readonly Uuid $contractId,
        public string $invoiceNumber,
        public DateTimeImmutable $issueDate,
        public DateTimeImmutable $dueDate,
        public Money $totalAmount,
        public Money $paidAmount,
        public InvoiceStatus $status = InvoiceStatus::DRAFT,
        public array $items = [],
        public array $payments = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $customerId,
        Uuid $contractId,
        string $invoiceNumber,
        DateTimeImmutable $issueDate,
        DateTimeImmutable $dueDate,
        Money $totalAmount,
        array $items = []
    ): self {
        return new self($id, $customerId, $contractId, $invoiceNumber, $issueDate, $dueDate, $totalAmount, new Money(0), InvoiceStatus::DRAFT, $items);
    }

    public function addItem(InvoiceItem $item): void
    {
        $this->items[] = $item;
    }

    public function applyPayment(Money $amount, Uuid $paymentId): void
    {
        $this->paidAmount = $this->paidAmount->add($amount);
        $this->payments[] = $paymentId;
        $this->updateStatus();
    }

    private function updateStatus(): void
    {
        if ($this->paidAmount->amount >= $this->totalAmount->amount) {
            $this->status = InvoiceStatus::PAID;
        } elseif ($this->paidAmount->amount > 0) {
            $this->status = InvoiceStatus::PARTIAL;
        } elseif (new DateTimeImmutable() > $this->dueDate) {
            $this->status = InvoiceStatus::OVERDUE;
        } else {
            $this->status = InvoiceStatus::UNPAID;
        }
    }

    public function markAsPaid(): void
    {
        $this->status = InvoiceStatus::PAID;
    }

    public function void(): void
    {
        $this->status = InvoiceStatus::VOID;
    }
}
