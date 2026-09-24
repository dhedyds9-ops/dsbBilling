<?php

namespace Src\Domain\Inventory;

use Src\Domain\Inventory\Enums\WarrantyStatus;
use Src\Domain\Inventory\ValueObjects\WarrantyPeriod;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AssetWarranty extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $assetId,
        public readonly WarrantyPeriod $period,
        public readonly WarrantyStatus $status,
        public readonly ?Uuid $vendorId = null,
        public readonly ?string $warrantyType = null,
        public readonly ?string $warrantyNumber = null,
        public readonly ?string $coverageDetails = null,
        public readonly ?DateTimeImmutable $registeredAt = null,
        public readonly ?DateTimeImmutable $expiresAt = null
    ) {}

    public static function create(
        Uuid $assetId,
        WarrantyPeriod $period,
        ?Uuid $vendorId = null,
        ?string $warrantyType = null,
        ?string $warrantyNumber = null,
        ?string $coverageDetails = null
    ): self {
        $status = $period->isActive() ? WarrantyStatus::ACTIVE : WarrantyStatus::EXPIRED;
        
        return new self(
            id: Uuid::generate(),
            assetId: $assetId,
            period: $period,
            status: $status,
            vendorId: $vendorId,
            warrantyType: $warrantyType,
            warrantyNumber: $warrantyNumber,
            coverageDetails: $coverageDetails,
            registeredAt: new DateTimeImmutable(),
            expiresAt: $period->endDate
        );
    }

    public function isActive(): bool
    {
        return $this->status === WarrantyStatus::ACTIVE && !$this->period->isExpired();
    }

    public function isExpiringWithin(int $days): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        return $this->period->getRemainingDays() <= $days;
    }

    public function void(string $reason): void
    {
        $this->status = WarrantyStatus::VOID;
    }

    public function transfer(Uuid $newAssetId): void
    {
        $this->status = WarrantyStatus::TRANSFERRED;
    }

    public function checkAndUpdateStatus(): void
    {
        if ($this->status === WarrantyStatus::ACTIVE && $this->period->isExpired()) {
            $this->status = WarrantyStatus::EXPIRED;
        }
    }

    public function getRemainingDays(): int
    {
        return $this->period->getRemainingDays();
    }
}
