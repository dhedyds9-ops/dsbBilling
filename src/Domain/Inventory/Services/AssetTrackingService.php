<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\Asset;
use Src\Domain\Inventory\SerialNumberTracker;
use Src\Domain\Inventory\MACAddressRecord;
use Src\Domain\Inventory\Repositories\AssetRepositoryInterface;
use Src\Domain\Inventory\Repositories\SerialNumberRepositoryInterface;
use Src\Domain\Inventory\Repositories\MACAddressRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetTrackingService
{
    public function __construct(
        private readonly AssetRepositoryInterface $assetRepository,
        private readonly SerialNumberRepositoryInterface $serialRepository,
        private readonly MACAddressRepositoryInterface $macRepository
    ) {}

    public function trackBySerialNumber(string $serialNumber): ?Asset
    {
        $serial = $this->serialRepository->findBySerialNumber($serialNumber);
        if (!$serial) {
            return null;
        }

        return $this->assetRepository->findById($serial->assetId);
    }

    public function trackByMACAddress(string $macAddress): ?Asset
    {
        $mac = $this->macRepository->findByMACAddress($macAddress);
        if (!$mac) {
            return null;
        }

        return $this->assetRepository->findById($mac->assetId);
    }

    public function registerSerialNumber(
        Uuid $assetId,
        Uuid $productId,
        string $serialNumber,
        ?Uuid $vendorId = null
    ): SerialNumberTracker {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $existing = $this->serialRepository->findBySerialNumber($serialNumber);
        if ($existing) {
            throw new \DomainException("Serial number already registered");
        }

        $serial = SerialNumberTracker::create(
            serialNumber: $serialNumber,
            assetId: $assetId,
            productId: $productId,
            vendorId: $vendorId
        );

        $this->serialRepository->save($serial);

        return $serial;
    }

    public function registerMACAddress(
        Uuid $assetId,
        string $macAddress,
        Uuid $interfaceType,
        ?string $interfaceName = null
    ): MACAddressRecord {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $normalizedMAC = strtoupper(str_replace([':', '-', '.'], '', $macAddress));
        $existing = $this->macRepository->findByMACAddress($normalizedMAC);
        if ($existing) {
            throw new \DomainException("MAC address already registered");
        }

        $mac = MACAddressRecord::create(
            macAddress: $macAddress,
            assetId: $assetId,
            interfaceType: $interfaceType,
            interfaceName: $interfaceName
        );

        $this->macRepository->save($mac);

        return $mac;
    }

    public function getAssetByCode(string $assetCode): ?Asset
    {
        return $this->assetRepository->findByCode($assetCode);
    }

    public function searchAssets(string $query): array
    {
        $bySerial = $this->serialRepository->search($query);
        $byMac = $this->macRepository->search($query);
        $byCode = $this->assetRepository->search($query);

        $assetIds = array_unique(array_merge(
            array_column($bySerial, 'asset_id'),
            array_column($byMac, 'asset_id'),
            array_column($byCode, 'id')
        ));

        $assets = [];
        foreach ($assetIds as $id) {
            $asset = $this->assetRepository->findById(new Uuid($id));
            if ($asset) {
                $assets[] = $asset;
            }
        }

        return $assets;
    }

    public function getAssetIdentifiers(Uuid $assetId): array
    {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        return [
            'asset' => $asset,
            'serial_numbers' => $this->serialRepository->findByAsset($assetId),
            'mac_addresses' => $this->macRepository->findByAsset($assetId)
        ];
    }

    public function validateSerialNumber(string $serialNumber): bool
    {
        $existing = $this->serialRepository->findBySerialNumber($serialNumber);
        return $existing === null;
    }

    public function validateMACAddress(string $macAddress): bool
    {
        $normalized = strtoupper(str_replace([':', '-', '.'], '', $macAddress));
        $existing = $this->macRepository->findByMACAddress($normalized);
        return $existing === null;
    }
}
