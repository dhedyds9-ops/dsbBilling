<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\MaintenanceStatus;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AssetRepair extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $assetId,
        public readonly string $issueDescription,
        public readonly ?Uuid $technicianId,
        public readonly DateTimeImmutable $reportedAt,
        public readonly ?DateTimeImmutable $diagnosedAt = null,
        public readonly ?DateTimeImmutable $repairStartedAt = null,
        public readonly ?DateTimeImmutable $completedAt = null,
        public readonly ?DateTimeImmutable $returnedAt = null,
        public readonly MaintenanceStatus $status = MaintenanceStatus::SCHEDULED,
        public readonly ?string $diagnosis = null,
        public readonly ?string $repairNotes = null,
        public readonly ?float $cost = null,
        public readonly ?Uuid $vendorId = null,
        public readonly ?string $vendorInvoiceNumber = null
    ) {}

    public static function create(
        Uuid $assetId,
        string $issueDescription,
        ?Uuid $technicianId = null
    ): self {
        return new self(
            id: Uuid::generate(),
            assetId: $assetId,
            issueDescription: $issueDescription,
            technicianId: $technicianId,
            reportedAt: new DateTimeImmutable()
        );
    }

    public function diagnose(string $diagnosis, ?Uuid $technicianId = null): void
    {
        $this->diagnosis = $diagnosis;
        $this->diagnosedAt = new DateTimeImmutable();
        $this->status = MaintenanceStatus::SCHEDULED;
        
        if ($technicianId !== null) {
            $this->technicianId = $technicianId;
        }
    }

    public function startRepair(?Uuid $technicianId = null): void
    {
        $this->status = MaintenanceStatus::IN_PROGRESS;
        $this->repairStartedAt = new DateTimeImmutable();
        
        if ($technicianId !== null) {
            $this->technicianId = $technicianId;
        }
    }

    public function completeRepair(string $notes, ?float $cost = null): void
    {
        $this->status = MaintenanceStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->repairNotes = $notes;
        
        if ($cost !== null) {
            $this->cost = $cost;
        }
    }

    public function returnAsset(): void
    {
        $this->returnedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = MaintenanceStatus::CANCELLED;
    }

    public function getTurnaroundDays(): int
    {
        if ($this->completedAt === null) {
            return $this->reportedAt->diff(new DateTimeImmutable())->days;
        }
        return $this->reportedAt->diff($this->completedAt)->days;
    }
}
