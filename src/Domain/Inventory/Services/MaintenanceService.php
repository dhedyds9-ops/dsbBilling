<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\AssetMaintenance;
use Src\Domain\Inventory\AssetRepair;
use Src\Domain\Inventory\Repositories\AssetMaintenanceRepositoryInterface;
use Src\Domain\Inventory\Repositories\AssetRepairRepositoryInterface;
use Src\Domain\Inventory\Repositories\AssetRepositoryInterface;
use Src\Domain\Inventory\Enums\MaintenanceStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class MaintenanceService
{
    public function __construct(
        private readonly AssetMaintenanceRepositoryInterface $maintenanceRepository,
        private readonly AssetRepairRepositoryInterface $repairRepository,
        private readonly AssetRepositoryInterface $assetRepository
    ) {}

    public function scheduleMaintenance(
        Uuid $assetId,
        string $maintenanceType,
        DateTimeImmutable $scheduledDate,
        ?string $description = null,
        bool $isRecurring = false,
        ?int $recurrenceIntervalDays = null
    ): AssetMaintenance {
        $asset = $this->assetRepository->findById($assetId);
        if (!$asset) {
            throw new \DomainException("Asset not found");
        }

        $maintenance = AssetMaintenance::schedule(
            assetId: $assetId,
            maintenanceType: $maintenanceType,
            scheduledDate: $scheduledDate,
            description: $description,
            isRecurring: $isRecurring,
            recurrenceIntervalDays: $recurrenceIntervalDays
        );

        $this->maintenanceRepository->save($maintenance);

        return $maintenance;
    }

    public function performMaintenance(
        Uuid $maintenanceId,
        Uuid $technicianId,
        ?string $notes = null,
        ?float $cost = null
    ): AssetMaintenance {
        $maintenance = $this->maintenanceRepository->findById($maintenanceId);
        if (!$maintenance) {
            throw new \DomainException("Maintenance record not found");
        }

        $maintenance->perform($technicianId, $notes, $cost);
        $this->maintenanceRepository->save($maintenance);

        return $maintenance;
    }

    public function completeMaintenance(Uuid $maintenanceId): AssetMaintenance
    {
        $maintenance = $this->maintenanceRepository->findById($maintenanceId);
        if (!$maintenance) {
            throw new \DomainException("Maintenance record not found");
        }

        $maintenance->complete();
        $this->maintenanceRepository->save($maintenance);

        // Create next recurrence if applicable
        $nextRecurrence = $maintenance->createNextRecurrence();
        if ($nextRecurrence) {
            $this->maintenanceRepository->save($nextRecurrence);
        }

        return $maintenance;
    }

    public function cancelMaintenance(Uuid $maintenanceId): AssetMaintenance
    {
        $maintenance = $this->maintenanceRepository->findById($maintenanceId);
        if (!$maintenance) {
            throw new \DomainException("Maintenance record not found");
        }

        $maintenance->cancel();
        $this->maintenanceRepository->save($maintenance);

        return $maintenance;
    }

    public function getUpcomingMaintenance(int $days = 7): array
    {
        return $this->maintenanceRepository->findUpcoming($days);
    }

    public function getOverdueMaintenance(): array
    {
        return $this->maintenanceRepository->findOverdue();
    }

    public function getMaintenanceHistory(Uuid $assetId): array
    {
        return $this->maintenanceRepository->findByAsset($assetId);
    }

    public function getScheduledMaintenance(Uuid $assetId): array
    {
        return $this->maintenanceRepository->findScheduledByAsset($assetId);
    }

    public function markOverdueMaintenance(): int
    {
        $overdue = $this->maintenanceRepository->findOverdue();
        $count = 0;

        foreach ($overdue as $maintenance) {
            $maintenance->markOverdue();
            $this->maintenanceRepository->save($maintenance);
            $count++;
        }

        return $count;
    }

    public function calculateMaintenanceCost(Uuid $assetId): float
    {
        $history = $this->maintenanceRepository->findByAsset($assetId);
        
        return array_reduce(
            $history,
            fn(float $total, AssetMaintenance $m) => $total + ($m->cost ?? 0),
            0.0
        );
    }

    public function getMaintenanceStats(Uuid $assetId): array
    {
        $history = $this->maintenanceRepository->findByAsset($assetId);
        
        $completed = array_filter($history, fn($m) => $m->status === MaintenanceStatus::COMPLETED);
        $cancelled = array_filter($history, fn($m) => $m->status === MaintenanceStatus::CANCELLED);
        
        $totalCost = array_reduce(
            $history,
            fn(float $total, AssetMaintenance $m) => $total + ($m->cost ?? 0),
            0.0
        );

        return [
            'asset_id' => $assetId->value,
            'total_scheduled' => count($history),
            'completed' => count($completed),
            'cancelled' => count($cancelled),
            'total_cost' => $totalCost,
            'last_maintenance' => !empty($completed) 
                ? max(array_column($completed, 'performedDate')) 
                : null
        ];
    }
}
