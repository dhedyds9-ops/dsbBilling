<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CapacityManagementService
{
    public function __construct(
        private CapacityManagementRepositoryInterface $repository
    ) {}

    public function createCapacity(
        Uuid $resourceId,
        ResourceType $resourceType,
        int $totalCapacity
    ): CapacityManagement {
        $capacity = CapacityManagement::create(
            Uuid::generate(),
            $resourceId,
            $resourceType,
            $totalCapacity
        );

        $this->repository->save($capacity);
        return $capacity;
    }

    public function checkCapacity(Uuid $resourceId, ResourceType $resourceType, int $required = 1): bool
    {
        $capacity = $this->repository->findByResource($resourceType, $resourceId);
        if (!$capacity) {
            return false;
        }

        return $capacity->hasAvailableCapacity($required);
    }

    public function getCapacityStats(Uuid $resourceId, ResourceType $resourceType): array
    {
        $capacity = $this->repository->findByResource($resourceType, $resourceId);
        if (!$capacity) {
            return [
                'total' => 0,
                'used' => 0,
                'reserved' => 0,
                'available' => 0,
                'utilization' => 0.0
            ];
        }

        return [
            'total' => $capacity->totalCapacity,
            'used' => $capacity->usedCapacity,
            'reserved' => $capacity->reservedCapacity,
            'available' => $capacity->getAvailableCapacity(),
            'utilization' => $capacity->getUtilizationPercentage()
        ];
    }

    public function updateTotalCapacity(Uuid $resourceId, ResourceType $resourceType, int $newTotal): void
    {
        $capacity = $this->repository->findByResource($resourceType, $resourceId);
        if (!$capacity) {
            throw new \InvalidArgumentException("Capacity record not found");
        }

        $capacity->totalCapacity = $newTotal;
        $this->repository->save($capacity);
    }
}
