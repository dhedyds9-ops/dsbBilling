<?php

namespace Src\Domain\Inventory;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class WarehouseLocation extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $warehouseId,
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description,
        public readonly ?Uuid $parentLocationId,
        public readonly int $maxCapacity = 0,
        public readonly int $currentUtilization = 0,
        public readonly bool $isActive = true
    ) {}

    public static function create(
        Uuid $warehouseId,
        string $name,
        string $code,
        ?Uuid $parentLocationId = null,
        ?string $description = null,
        int $maxCapacity = 0
    ): self {
        return new self(
            id: Uuid::generate(),
            warehouseId: $warehouseId,
            name: $name,
            code: $code,
            description: $description,
            parentLocationId: $parentLocationId,
            maxCapacity: $maxCapacity,
            currentUtilization: 0,
            isActive: true
        );
    }

    public function isAtCapacity(): bool
    {
        if ($this->maxCapacity === 0) {
            return false;
        }
        return $this->currentUtilization >= $this->maxCapacity;
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->maxCapacity === 0) {
            return 0;
        }
        return ($this->currentUtilization / $this->maxCapacity) * 100;
    }

    public function incrementUtilization(int $amount = 1): void
    {
        $this->currentUtilization += $amount;
    }

    public function decrementUtilization(int $amount = 1): void
    {
        $this->currentUtilization = max(0, $this->currentUtilization - $amount);
    }
}
