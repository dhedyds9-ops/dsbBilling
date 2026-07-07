<?php

namespace Src\Domain\FiberCapacity\Services;

use Src\Domain\FiberCapacity\FiberCapacity;
use Src\Domain\FiberCapacity\FiberCore;
use Src\Domain\FiberCapacity\Repositories\FiberCapacityRepositoryInterface;
use Src\Domain\FiberCapacity\Repositories\FiberCoreRepositoryInterface;
use Src\Domain\FiberCapacity\ValueObjects\CoreNumber;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class FiberCapacityService
{
    public function __construct(
        private FiberCapacityRepositoryInterface $capacityRepository,
        private FiberCoreRepositoryInterface $coreRepository
    ) {}

    public function createFiberCapacity(
        Uuid $resourceId,
        string $resourceType,
        int $totalCore
    ): FiberCapacity {
        $capacity = FiberCapacity::create(
            Uuid::generate(),
            $resourceId,
            $resourceType,
            $totalCore
        );

        $this->capacityRepository->save($capacity);
        return $capacity;
    }

    public function allocateCore(Uuid $fiberCoreId): void
    {
        $fiberCore = $this->coreRepository->findById($fiberCoreId);
        if (!$fiberCore) {
            throw new \InvalidArgumentException("Fiber core not found");
        }

        $fiberCore->allocate();
        $this->coreRepository->save($fiberCore);

        $capacity = $this->capacityRepository->findByResourceId(
            $fiberCore->fiberCableId,
            'fiber_cable'
        );

        if ($capacity) {
            $capacity->allocateCore(1);
            $this->capacityRepository->save($capacity);
        }
    }

    public function releaseCore(Uuid $fiberCoreId): void
    {
        $fiberCore = $this->coreRepository->findById($fiberCoreId);
        if (!$fiberCore) {
            throw new \InvalidArgumentException("Fiber core not found");
        }

        $fiberCore->release();
        $this->coreRepository->save($fiberCore);

        $capacity = $this->capacityRepository->findByResourceId(
            $fiberCore->fiberCableId,
            'fiber_cable'
        );

        if ($capacity) {
            $capacity->releaseCore(1);
            $this->capacityRepository->save($capacity);
        }
    }

    public function getCapacityStats(Uuid $resourceId, string $resourceType): array
    {
        $capacity = $this->capacityRepository->findByResourceId($resourceId, $resourceType);
        
        if (!$capacity) {
            return [
                'total' => 0,
                'used' => 0,
                'reserved' => 0,
                'available' => 0,
                'utilization' => 0.0,
                'status' => 'unknown'
            ];
        }

        return [
            'total' => $capacity->totalCore,
            'used' => $capacity->usedCore,
            'reserved' => $capacity->reservedCore,
            'available' => $capacity->getAvailableCore(),
            'utilization' => $capacity->getUtilizationPercentage(),
            'status' => $capacity->status->value
        ];
    }

    public function checkCapacity(Uuid $resourceId, string $resourceType, int $required = 1): bool
    {
        $capacity = $this->capacityRepository->findByResourceId($resourceId, $resourceType);
        
        if (!$capacity) {
            return false;
        }

        return $capacity->hasCapacity($required);
    }

    public function getAvailableCores(Uuid $fiberCableId, int $required = 1): array
    {
        return $this->coreRepository->findAvailableCores($fiberCableId, $required);
    }

    public function updateTotalCore(Uuid $resourceId, string $resourceType, int $newTotal): void
    {
        $capacity = $this->capacityRepository->findByResourceId($resourceId, $resourceType);
        
        if (!$capacity) {
            throw new \InvalidArgumentException("Capacity record not found");
        }

        $capacity->updateTotalCore($newTotal);
        $this->capacityRepository->save($capacity);
    }

    public function getCriticalCapacities(): array
    {
        return $this->capacityRepository->findByUtilizationRange(90.0, 100.0);
    }

    public function getWarningCapacities(): array
    {
        return $this->capacityRepository->findByUtilizationRange(75.0, 90.0);
    }
}
