<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\AssetWarranty;
use Src\Domain\Inventory\Repositories\AssetWarrantyRepositoryInterface;
use Src\Domain\Inventory\Repositories\AssetRepositoryInterface;
use Src\Domain\Inventory\ValueObjects\WarrantyPeriod;
use Src\Domain\Inventory\Events\WarrantyExpired;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Illuminate\Support\Facades\Event;

class WarrantyService
{
    public function __construct(
        private readonly AssetWarrantyRepositoryInterface $warrantyRepository,
        private readonly AssetRepositoryInterface $assetRepository
    ) {}

    public function registerWarranty(
        Uuid $assetId,
        WarrantyPeriod $period,
        ?Uuid $vendorId = null,
        ?string $warrantyType = null,
        ?string $warrantyNumber = null,
        ?string $coverageDetails = null
    ): AssetWarranty {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $warranty = AssetWarranty::create(
            assetId: $assetId,
            period: $period,
            vendorId: $vendorId,
            warrantyType: $warrantyType,
            warrantyNumber: $warrantyNumber,
            coverageDetails: $coverageDetails
        );

        $this->warrantyRepository->save($warranty);

        return $warranty;
    }

    public function checkWarrantyStatus(Uuid $assetId): ?AssetWarranty
    {
        $warranty = $this->warrantyRepository->findActiveByAsset($assetId);
        
        if ($warranty) {
            $warranty->checkAndUpdateStatus();
            $this->warrantyRepository->save($warranty);

            if ($warranty->status->value === 'expired') {
                Event::dispatch(new WarrantyExpired($assetId, $warranty->id));
            }
        }

        return $warranty;
    }

    public function getExpiringWarranties(int $daysThreshold = 30): array
    {
        return $this->warrantyRepository->findExpiringWithin($daysThreshold);
    }

    public function getWarrantyByAsset(Uuid $assetId): array
    {
        return $this->warrantyRepository->findByAsset($assetId);
    }

    public function voidWarranty(Uuid $warrantyId, string $reason): AssetWarranty
    {
        $warranty = $this->warrantyRepository->findById($warrantyId);
        if (!$warranty) {
            throw new \DomainException("Warranty not found");
        }

        $warranty->void($reason);
        $this->warrantyRepository->save($warranty);

        return $warranty;
    }

    public function transferWarranty(Uuid $warrantyId, Uuid $newAssetId): AssetWarranty
    {
        $warranty = $this->warrantyRepository->findById($warrantyId);
        if (!$warranty) {
            throw new \DomainException("Warranty not found");
        }

        $warranty->transfer($newAssetId);
        $this->warrantyRepository->save($warranty);

        return $warranty;
    }

    public function calculateRemainingWarrantyDays(Uuid $assetId): ?int
    {
        $warranty = $this->warrantyRepository->findActiveByAsset($assetId);
        
        if (!$warranty) {
            return null;
        }

        return $warranty->getRemainingDays();
    }

    public function isUnderWarranty(Uuid $assetId): bool
    {
        $warranty = $this->warrantyRepository->findActiveByAsset($assetId);
        return $warranty !== null && $warranty->isActive();
    }

    public function getWarrantyCoverageDetails(Uuid $assetId): ?string
    {
        $warranty = $this->warrantyRepository->findActiveByAsset($assetId);
        return $warranty?->coverageDetails;
    }
}
