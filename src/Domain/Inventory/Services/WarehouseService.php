<?php

namespace Src\Domain\Inventory\Services;

use Src\Domain\Inventory\Warehouse;
use Src\Domain\Inventory\WarehouseLocation;
use Src\Domain\Inventory\RackUnit;
use Src\Domain\Inventory\Repositories\WarehouseRepositoryInterface;
use Src\Domain\Inventory\Repositories\WarehouseLocationRepositoryInterface;
use Src\Domain\Inventory\Repositories\RackUnitRepositoryInterface;
use Src\Domain\Inventory\Enums\WarehouseType;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WarehouseService
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $warehouseRepository,
        private readonly WarehouseLocationRepositoryInterface $locationRepository,
        private readonly RackUnitRepositoryInterface $rackUnitRepository
    ) {}

    public function createWarehouse(
        string $name,
        string $code,
        WarehouseType $type,
        ?string $address = null,
        ?string $city = null,
        ?Uuid $managerId = null
    ): Warehouse {
        $warehouse = Warehouse::create(
            name: $name,
            code: $code,
            type: $type,
            address: $address,
            city: $city,
            managerId: $managerId
        );

        $this->warehouseRepository->save($warehouse);

        return $warehouse;
    }

    public function addLocation(
        Uuid $warehouseId,
        string $name,
        string $code,
        ?Uuid $parentLocationId = null,
        ?string $description = null,
        int $maxCapacity = 0
    ): WarehouseLocation {
        $warehouse = $this->warehouseRepository->findById($warehouseId);
        if (!$warehouse) {
            throw new \DomainException("Warehouse not found");
        }

        $location = WarehouseLocation::create(
            warehouseId: $warehouseId,
            name: $name,
            code: $code,
            parentLocationId: $parentLocationId,
            description: $description,
            maxCapacity: $maxCapacity
        );

        $this->locationRepository->save($location);

        return $location;
    }

    public function createRack(
        Uuid $warehouseId,
        Uuid $locationId,
        string $name,
        int $totalUnits,
        int $unitSize = 1
    ): array {
        $rackId = Uuid::generate();
        $units = [];

        for ($i = 1; $i <= $totalUnits; $i++) {
            $unit = RackUnit::create(
                rackId: $rackId,
                position: $i,
                size: $unitSize
            );
            $this->rackUnitRepository->save($unit);
            $units[] = $unit;
        }

        return $units;
    }

    public function occupyRackUnit(Uuid $rackUnitId, Uuid $assetId): RackUnit
    {
        $unit = $this->rackUnitRepository->findById($rackUnitId);
        if (!$unit) {
            throw new \DomainException("Rack unit not found");
        }

        $unit->occupy($assetId);
        $this->rackUnitRepository->save($unit);

        return $unit;
    }

    public function vacateRackUnit(Uuid $rackUnitId): RackUnit
    {
        $unit = $this->rackUnitRepository->findById($rackUnitId);
        if (!$unit) {
            throw new \DomainException("Rack unit not found");
        }

        $unit->vacate();
        $this->rackUnitRepository->save($unit);

        return $unit;
    }

    public function getWarehouseUtilization(Uuid $warehouseId): array
    {
        $warehouse = $this->warehouseRepository->findById($warehouseId);
        if (!$warehouse) {
            throw new \DomainException("Warehouse not found");
        }

        $locations = $this->locationRepository->findByWarehouse($warehouseId);
        $totalCapacity = array_sum(array_column($locations, 'maxCapacity'));
        $totalUtilization = array_sum(array_column($locations, 'currentUtilization'));

        return [
            'warehouse_id' => $warehouseId->value,
            'warehouse_name' => $warehouse->name,
            'total_capacity' => $totalCapacity,
            'current_utilization' => $totalUtilization,
            'utilization_percentage' => $totalCapacity > 0 
                ? round(($totalUtilization / $totalCapacity) * 100, 2) 
                : 0,
            'locations' => $locations
        ];
    }

    public function getAvailableRackSpace(Uuid $warehouseId): array
    {
        $locations = $this->locationRepository->findByWarehouse($warehouseId);
        $availableSpaces = [];

        foreach ($locations as $location) {
            if ($location->maxCapacity > 0) {
                $available = $location->maxCapacity - $location->currentUtilization;
                if ($available > 0) {
                    $availableSpaces[] = [
                        'location_id' => $location->id->value,
                        'location_name' => $location->name,
                        'available_units' => $available
                    ];
                }
            }
        }

        return $availableSpaces;
    }

    public function transferBetweenWarehouses(
        Uuid $assetId,
        Uuid $fromWarehouseId,
        Uuid $toWarehouseId,
        Uuid $initiatedBy
    ): array {
        $fromWarehouse = $this->warehouseRepository->findById($fromWarehouseId);
        $toWarehouse = $this->warehouseRepository->findById($toWarehouseId);

        if (!$fromWarehouse || !$toWarehouse) {
            throw new \DomainException("One or both warehouses not found");
        }

        if (!$fromWarehouse->canIssueStock()) {
            throw new \DomainException("Source warehouse cannot issue stock");
        }

        if (!$toWarehouse->canReceiveStock()) {
            throw new \DomainException("Destination warehouse cannot receive stock");
        }

        return [
            'from_warehouse' => $fromWarehouse,
            'to_warehouse' => $toWarehouse
        ];
    }
}
