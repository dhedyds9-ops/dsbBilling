<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\AssetStatus;
use Src\Domain\Inventory\Enums\AssetType;
use Src\Domain\Inventory\Events\AssetCreated;
use Src\Domain\Inventory\Events\AssetAssigned;
use Src\Domain\Inventory\Events\AssetTransferred;
use Src\Domain\Inventory\Events\AssetInstalled;
use Src\Domain\Inventory\Events\AssetReturned;
use Src\Domain\Inventory\Events\AssetRepaired;
use Src\Domain\Inventory\ValueObjects\AssetCode;
use Src\Domain\Inventory\ValueObjects\AssetCondition;
use Src\Domain\Inventory\ValueObjects\AssetLocation;
use Src\Domain\Inventory\ValueObjects\MACAddress;
use Src\Domain\Inventory\ValueObjects\SerialNumber;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class Asset extends AggregateRoot
{
    private AssetStatus $status;
    private AssetCondition $condition;
    private ?Uuid $assignedToId = null;
    private ?Uuid $assignedToType = null;
    private ?Uuid $installationId = null;
    private ?DateTimeImmutable $assignedAt = null;
    private ?DateTimeImmutable $installedAt = null;
    private array $customFields = [];

    public function __construct(
        public readonly Uuid $id,
        public readonly AssetCode $code,
        public readonly AssetType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly ?SerialNumber $serialNumber,
        public readonly ?MACAddress $macAddress,
        public readonly Uuid $categoryId,
        public readonly Uuid $warehouseId,
        public readonly AssetLocation $location,
        public readonly float $purchasePrice,
        public readonly DateTimeImmutable $purchaseDate,
        public readonly ?int $warrantyMonths,
        public readonly ?Uuid $vendorId,
        public readonly ?Uuid $parentAssetId = null
    ) {
        $this->status = AssetStatus::PENDING;
        $this->condition = AssetCondition::NEW;
    }

    public static function create(/* params */): self
    {
        $asset = new self(/* params */);
        $asset->recordThat(new AssetCreated($asset->id, $asset->code));
        return $asset;
    }

    public function getStatus(): AssetStatus
    {
        return $this->status;
    }

    public function getCondition(): AssetCondition
    {
        return $this->condition;
    }

    public function assignTo(Uuid $entityId, Uuid $entityType): void
    {
        if (!$this->status->canBeAssigned()) {
            throw new \DomainException("Asset cannot be assigned in {$this->status->value} status");
        }

        $this->assignedToId = $entityId;
        $this->assignedToType = $entityType;
        $this->assignedAt = new DateTimeImmutable();
        $this->status = AssetStatus::ASSIGNED;

        $this->recordThat(new AssetAssigned($this->id, $entityId, $entityType));
    }

    public function install(Uuid $installationId): void
    {
        if ($this->status !== AssetStatus::ASSIGNED) {
            throw new \DomainException("Asset must be assigned before installation");
        }

        $this->installationId = $installationId;
        $this->installedAt = new DateTimeImmutable();
        $this->status = AssetStatus::INSTALLED;

        $this->recordThat(new AssetInstalled($this->id, $installationId));
    }

    public function transfer(Uuid $toWarehouseId, AssetLocation $newLocation): void
    {
        if (!$this->status->canBeTransferred()) {
            throw new \DomainException("Asset cannot be transferred in {$this->status->value} status");
        }

        $fromWarehouseId = $this->warehouseId;
        $this->warehouseId = $toWarehouseId;
        $this->location = $newLocation;
        $this->status = AssetStatus::IN_TRANSIT;

        $this->recordThat(new AssetTransferred($this->id, $fromWarehouseId, $toWarehouseId));
    }

    public function markAsInstalled(): void
    {
        if ($this->status !== AssetStatus::IN_TRANSIT) {
            throw new \DomainException("Asset must be in transit to be marked as installed");
        }

        $this->status = AssetStatus::INSTALLED;
    }

    public function return(): void
    {
        if (!in_array($this->status, [AssetStatus::ASSIGNED, AssetStatus::INSTALLED])) {
            throw new \DomainException("Asset cannot be returned in {$this->status->value} status");
        }

        $previousStatus = $this->status;
        $this->status = AssetStatus::IN_STOCK;
        $this->assignedToId = null;
        $this->assignedToType = null;
        $this->installationId = null;

        $this->recordThat(new AssetReturned($this->id, $previousStatus->value));
    }

    public function markForRepair(): void
    {
        $this->status = AssetStatus::IN_REPAIR;
    }

    public function repairCompleted(): void
    {
        $this->status = AssetStatus::IN_STOCK;
        $this->condition = AssetCondition::GOOD;
        $this->recordThat(new AssetRepaired($this->id));
    }

    public function updateCondition(AssetCondition $condition): void
    {
        $this->condition = $condition;
    }

    public function retire(string $reason): void
    {
        if (!$this->status->canBeRetired()) {
            throw new \DomainException("Asset cannot be retired in {$this->status->value} status");
        }

        $this->status = AssetStatus::RETIRED;
    }

    public function dispose(string $reason): void
    {
        $this->status = AssetStatus::DISPOSED;
    }

    public function setCustomField(string $key, mixed $value): void
    {
        $this->customFields[$key] = $value;
    }

    public function getCustomField(string $key): mixed
    {
        return $this->customFields[$key] ?? null;
    }

    public function calculateDepreciation(float $salvageValue = 0): float
    {
        $usefulLifeMonths = $this->warrantyMonths ?? 60;
        $ageMonths = $this->purchaseDate->diff(new DateTimeImmutable())->m 
            + ($this->purchaseDate->diff(new DateTimeImmutable())->y * 12);
        
        if ($ageMonths >= $usefulLifeMonths) {
            return $salvageValue;
        }

        $depreciationPerMonth = ($this->purchasePrice - $salvageValue) / $usefulLifeMonths;
        return max($salvageValue, $this->purchasePrice - ($depreciationPerMonth * $ageMonths));
    }
}
