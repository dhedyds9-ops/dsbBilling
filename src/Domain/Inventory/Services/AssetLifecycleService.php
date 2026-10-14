<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\Asset;
use Src\Domain\Inventory\AssetAssignment;
use Src\Domain\Inventory\AssetTransfer;
use Src\Domain\Inventory\AssetReturn;
use Src\Domain\Inventory\AssetRepair;
use Src\Domain\Inventory\Enums\AssetStatus;
use Src\Domain\Inventory\Repositories\AssetRepositoryInterface;
use Src\Domain\Inventory\Repositories\AssetAssignmentRepositoryInterface;
use Src\Domain\Inventory\Repositories\AssetTransferRepositoryInterface;
use Src\Domain\Inventory\Repositories\AssetReturnRepositoryInterface;
use Src\Domain\Inventory\Repositories\AssetRepairRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AssetLifecycleService
{
    public function __construct(
        private readonly AssetRepositoryInterface $assetRepository,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepository,
        private readonly AssetTransferRepositoryInterface $transferRepository,
        private readonly AssetReturnRepositoryInterface $returnRepository,
        private readonly AssetRepairRepositoryInterface $repairRepository
    ) {}

    public function createAsset(/* params */): Asset
    {
        // Create new asset
        $asset = Asset::create(/* params */);
        $this->assetRepository->save($asset);
        return $asset;
    }

    public function assignAsset(
        Uuid $assetId,
        Uuid $entityId,
        string $entityType,
        Uuid $assignedBy,
        ?string $notes = null
    ): AssetAssignment {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        if (!$asset->getStatus()->canBeAssigned()) {
            throw new \DomainException("Asset cannot be assigned in current status");
        }

        $assignment = AssetAssignment::create(
            assetId: $assetId,
            assignedToId: $entityId,
            assignedToType: $entityType,
            assignedBy: $assignedBy,
            notes: $notes
        );

        $this->assignmentRepository->save($assignment);

        $asset->assignTo($entityId, new Uuid($entityType));
        $this->assetRepository->save($asset);

        return $assignment;
    }

    public function installAsset(Uuid $assetId, Uuid $installationId): Asset
    {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $asset->install($installationId);
        $this->assetRepository->save($asset);

        return $asset;
    }

    public function transferAsset(
        Uuid $assetId,
        Uuid $toWarehouseId,
        Uuid $initiatedBy,
        ?string $reason = null
    ): AssetTransfer {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        if (!$asset->getStatus()->canBeTransferred()) {
            throw new \DomainException("Asset cannot be transferred");
        }

        $transfer = AssetTransfer::create(
            assetId: $assetId,
            fromWarehouseId: $asset->warehouseId,
            toWarehouseId: $toWarehouseId,
            initiatedBy: $initiatedBy,
            reason: $reason
        );

        $this->transferRepository->save($transfer);

        $asset->transfer($toWarehouseId, $asset->location);
        $this->assetRepository->save($asset);

        return $transfer;
    }

    public function returnAsset(
        Uuid $assetId,
        Uuid $returnedBy,
        Uuid $receivedBy,
        string $condition,
        ?string $notes = null
    ): AssetReturn {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $return = AssetReturn::create(
            assetId: $assetId,
            returnedBy: $returnedBy,
            receivedBy: $receivedBy,
            condition: $condition,
            notes: $notes
        );

        $this->returnRepository->save($return);

        $asset->return();
        $this->assetRepository->save($asset);

        return $return;
    }

    public function reportForRepair(
        Uuid $assetId,
        string $issueDescription,
        ?Uuid $technicianId = null
    ): AssetRepair {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $asset->markForRepair();
        $this->assetRepository->save($asset);

        $repair = AssetRepair::create(
            assetId: $assetId,
            issueDescription: $issueDescription,
            technicianId: $technicianId
        );

        $this->repairRepository->save($repair);

        return $repair;
    }

    public function completeRepair(
        Uuid $repairId,
        string $notes,
        ?float $cost = null
    ): AssetRepair {
        $repair = $this->repairRepository->findById($repairId);
        if (!$repair) {
            throw new \DomainException("Repair not found");
        }

        $repair->completeRepair($notes, $cost);
        $this->repairRepository->save($repair);

        $asset = $this->assetRepository->findById($repair->assetId);
        if ($asset) {
            $asset->repairCompleted();
            $this->assetRepository->save($asset);
        }

        return $repair;
    }

    public function retireAsset(Uuid $assetId, string $reason): Asset
    {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $asset->retire($reason);
        $this->assetRepository->save($asset);

        return $asset;
    }

    public function disposeAsset(Uuid $assetId, string $reason): Asset
    {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $asset->dispose($reason);
        $this->assetRepository->save($asset);

        return $asset;
    }

    public function getAssetHistory(Uuid $assetId): array
    {
        $assignments = $this->assignmentRepository->findByAsset($assetId);
        $transfers = $this->transferRepository->findByAsset($assetId);
        $returns = $this->returnRepository->findByAsset($assetId);
        $repairs = $this->repairRepository->findByAsset($assetId);

        return [
            'assignments' => $assignments,
            'transfers' => $transfers,
            'returns' => $returns,
            'repairs' => $repairs
        ];
    }

    public function getActiveAssignments(?Uuid $entityId = null): array
    {
        return $this->assignmentRepository->findActive($entityId);
    }

    public function getPendingTransfers(): array
    {
        return $this->transferRepository->findPending();
    }

    public function getAssetsRequiringMaintenance(): array
    {
        return $this->assetRepository->findRequiringMaintenance();
    }
}
