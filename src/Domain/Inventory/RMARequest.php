<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\RMAStatus;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class RMARequest extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $rmaNumber,
        public readonly Uuid $assetId,
        public readonly Uuid $customerId,
        public readonly Uuid $vendorId,
        public readonly RMAStatus $status,
        public readonly DateTimeImmutable $requestedAt,
        public readonly string $reason,
        public readonly ?string $description = null,
        public readonly ?Uuid $approvedBy = null,
        public readonly ?DateTimeImmutable $approvedAt = null,
        public readonly ?Uuid $shippedBy = null,
        public readonly ?DateTimeImmutable $shippedAt = null,
        public readonly ?Uuid $receivedBy = null,
        public readonly ?DateTimeImmutable $receivedAt = null,
        public readonly ?Uuid $technicianId = null,
        public readonly ?DateTimeImmutable $diagnosedAt = null,
        public readonly ?string $diagnosis = null,
        public readonly ?Uuid $repairBy = null,
        public readonly ?DateTimeImmutable $repairedAt = null,
        public readonly ?string $repairNotes = null,
        public readonly ?Uuid $replacedBy = null,
        public readonly ?DateTimeImmutable $replacedAt = null,
        public readonly ?Uuid $replacementAssetId = null,
        public readonly ?DateTimeImmutable $resolvedAt = null,
        public readonly ?DateTimeImmutable $closedAt = null,
        public readonly ?DateTimeImmutable $returnedAt = null,
        public readonly ?Uuid $returnShippedBy = null,
        public readonly ?float $cost = null,
        public readonly ?string $notes = null
    ) {}

    public static function create(
        string $rmaNumber,
        Uuid $assetId,
        Uuid $customerId,
        Uuid $vendorId,
        string $reason,
        ?string $description = null
    ): self {
        return new self(
            id: Uuid::generate(),
            rmaNumber: $rmaNumber,
            assetId: $assetId,
            customerId: $customerId,
            vendorId: $vendorId,
            status: RMAStatus::REQUESTED,
            requestedAt: new DateTimeImmutable(),
            reason: $reason,
            description: $description
        );
    }

    public function approve(Uuid $approvedBy): void
    {
        if ($this->status !== RMAStatus::REQUESTED) {
            throw new \DomainException("RMA must be requested to be approved");
        }
        
        $this->status = RMAStatus::APPROVED;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = new DateTimeImmutable();
    }

    public function reject(Uuid $rejectedBy, string $reason): void
    {
        $this->status = RMAStatus::REJECTED;
        $this->notes = $reason;
    }

    public function shipToVendor(Uuid $shippedBy): void
    {
        if ($this->status !== RMAStatus::APPROVED) {
            throw new \DomainException("RMA must be approved before shipping");
        }
        
        $this->status = RMAStatus::SHIPPED;
        $this->shippedBy = $shippedBy;
        $this->shippedAt = new DateTimeImmutable();
    }

    public function receiveAtVendor(Uuid $receivedBy): void
    {
        if ($this->status !== RMAStatus::SHIPPED) {
            throw new \DomainException("RMA must be shipped to be received");
        }
        
        $this->status = RMAStatus::RECEIVED;
        $this->receivedBy = $receivedBy;
        $this->receivedAt = new DateTimeImmutable();
    }

    public function inspect(Uuid $technicianId, string $diagnosis): void
    {
        if (!in_array($this->status, [RMAStatus::RECEIVED, RMAStatus::INSPECTING])) {
            throw new \DomainException("RMA must be received before inspection");
        }
        
        $this->status = RMAStatus::INSPECTING;
        $this->technicianId = $technicianId;
        $this->diagnosedAt = new DateTimeImmutable();
        $this->diagnosis = $diagnosis;
    }

    public function repair(Uuid $repairBy, string $notes, ?float $cost = null): void
    {
        $this->status = RMAStatus::REPAIRING;
        $this->repairBy = $repairBy;
        $this->repairNotes = $notes;
        if ($cost !== null) {
            $this->cost = $cost;
        }
    }

    public function replace(Uuid $replacedBy, Uuid $replacementAssetId): void
    {
        $this->status = RMAStatus::REPLACING;
        $this->replacedBy = $replacedBy;
        $this->replacementAssetId = $replacementAssetId;
        $this->replacedAt = new DateTimeImmutable();
    }

    public function resolve(): void
    {
        $this->status = RMAStatus::RESOLVED;
        $this->resolvedAt = new DateTimeImmutable();
    }

    public function close(): void
    {
        $this->status = RMAStatus::CLOSED;
        $this->closedAt = new DateTimeImmutable();
    }

    public function returnToCustomer(Uuid $shippedBy): void
    {
        $this->status = RMAStatus::RETURNED;
        $this->returnShippedBy = $shippedBy;
        $this->returnedAt = new DateTimeImmutable();
    }

    public function isComplete(): bool
    {
        return $this->status->isComplete();
    }

    public function getTurnaroundDays(): int
    {
        $end = $this->resolvedAt ?? new DateTimeImmutable();
        return $this->requestedAt->diff($end)->days;
    }
}
