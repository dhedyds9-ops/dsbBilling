<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\MaintenanceStatus;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AssetMaintenance extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $assetId,
        public readonly string $maintenanceType,
        public readonly DateTimeImmutable $scheduledDate,
        public readonly ?DateTimeImmutable $performedDate = null,
        public readonly ?Uuid $technicianId = null,
        public readonly MaintenanceStatus $status = MaintenanceStatus::SCHEDULED,
        public readonly ?string $description = null,
        public readonly ?string $notes = null,
        public readonly ?float $cost = null,
        public readonly ?Uuid $vendorId = null,
        public readonly ?bool $isRecurring = false,
        public readonly ?int $recurrenceIntervalDays = null,
        public readonly ?Uuid $parentMaintenanceId = null
    ) {}

    public const TYPE_PREVENTIVE = 'preventive';
    public const TYPE_CORRECTIVE = 'corrective';
    public const TYPE_PREDICTIVE = 'predictive';
    public const TYPE_INSPECTION = 'inspection';
    public const TYPE_CALIBRATION = 'calibration';
    public const TYPE_CLEANING = 'cleaning';
    public const TYPE_FIRMWARE_UPDATE = 'firmware_update';

    public static function schedule(
        Uuid $assetId,
        string $maintenanceType,
        DateTimeImmutable $scheduledDate,
        ?string $description = null,
        bool $isRecurring = false,
        ?int $recurrenceIntervalDays = null
    ): self {
        return new self(
            id: Uuid::generate(),
            assetId: $assetId,
            maintenanceType: $maintenanceType,
            scheduledDate: $scheduledDate,
            performedDate: null,
            technicianId: null,
            status: MaintenanceStatus::SCHEDULED,
            description: $description,
            notes: null,
            cost: null,
            vendorId: null,
            isRecurring: $isRecurring,
            recurrenceIntervalDays: $recurrenceIntervalDays,
            parentMaintenanceId: null
        );
    }

    public function perform(
        Uuid $technicianId,
        ?string $notes = null,
        ?float $cost = null
    ): void {
        $this->status = MaintenanceStatus::IN_PROGRESS;
        $this->technicianId = $technicianId;
        $this->notes = $notes;
        $this->cost = $cost;
    }

    public function complete(): void
    {
        $this->status = MaintenanceStatus::COMPLETED;
        $this->performedDate = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = MaintenanceStatus::CANCELLED;
    }

    public function markOverdue(): void
    {
        if ($this->status === MaintenanceStatus::SCHEDULED 
            && $this->scheduledDate < new DateTimeImmutable()) {
            $this->status = MaintenanceStatus::OVERDUE;
        }
    }

    public function createNextRecurrence(): ?self
    {
        if (!$this->isRecurring || $this->recurrenceIntervalDays === null) {
            return null;
        }

        return self::schedule(
            assetId: $this->assetId,
            maintenanceType: $this->maintenanceType,
            scheduledDate: $this->scheduledDate->modify("+{$this->recurrenceIntervalDays} days"),
            description: $this->description,
            isRecurring: $this->isRecurring,
            recurrenceIntervalDays: $this->recurrenceIntervalDays
        );
    }
}
