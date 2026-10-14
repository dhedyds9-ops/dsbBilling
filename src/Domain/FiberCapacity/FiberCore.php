<?php

namespace Src\Domain\FiberCapacity;

use DateTimeImmutable;
use Src\Domain\FiberCapacity\Enums\CoreStatus;
use Src\Domain\FiberCapacity\Events\CapacityUpdated;
use Src\Domain\FiberCapacity\ValueObjects\CoreNumber;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class FiberCore extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $fiberCableId,
        public readonly CoreNumber $coreNumber,
        public readonly string $color,
        public CoreStatus $status,
        public int $usedCapacity,
        public int $totalCapacity,
        public DateTimeImmutable $lastCapacityUpdate
    ) {}

    public static function create(
        Uuid $id,
        Uuid $fiberCableId,
        CoreNumber $coreNumber,
        string $color,
        int $totalCapacity = 1
    ): self {
        return new self(
            $id,
            $fiberCableId,
            $coreNumber,
            $color,
            CoreStatus::AVAILABLE,
            0,
            $totalCapacity,
            new DateTimeImmutable()
        );
    }

    public function allocate(): void
    {
        if ($this->status !== CoreStatus::AVAILABLE) {
            throw new \InvalidArgumentException("Core is not available for allocation");
        }
        if ($this->usedCapacity >= $this->totalCapacity) {
            throw new \InvalidArgumentException("Core capacity is full");
        }

        $previousUsed = $this->usedCapacity;
        $this->usedCapacity++;
        $this->status = CoreStatus::ALLOCATED;
        $this->lastCapacityUpdate = new DateTimeImmutable();

        $this->recordThat(new CapacityUpdated(
            $this->id->value,
            'fiber_core',
            $previousUsed,
            $this->usedCapacity,
            $this->totalCapacity,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedCapacity)
        ));
    }

    public function release(): void
    {
        if ($this->usedCapacity <= 0) {
            throw new \InvalidArgumentException("Core is already empty");
        }

        $previousUsed = $this->usedCapacity;
        $this->usedCapacity--;
        
        if ($this->usedCapacity === 0) {
            $this->status = CoreStatus::AVAILABLE;
        }

        $this->lastCapacityUpdate = new DateTimeImmutable();

        $this->recordThat(new CapacityUpdated(
            $this->id->value,
            'fiber_core',
            $previousUsed,
            $this->usedCapacity,
            $this->totalCapacity,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedCapacity)
        ));
    }

    public function markMaintenance(): void
    {
        $this->status = CoreStatus::MAINTENANCE;
        $this->lastCapacityUpdate = new DateTimeImmutable();
    }

    public function markAvailable(): void
    {
        $this->status = CoreStatus::AVAILABLE;
        $this->lastCapacityUpdate = new DateTimeImmutable();
    }

    public function markDamaged(): void
    {
        $this->status = CoreStatus::DAMAGED;
        $this->lastCapacityUpdate = new DateTimeImmutable();
    }

    public function getAvailableCapacity(): int
    {
        return $this->totalCapacity - $this->usedCapacity;
    }

    public function hasAvailableCapacity(int $required = 1): bool
    {
        return $this->getAvailableCapacity() >= $required;
    }

    public function getUtilizationPercentage(): float
    {
        return $this->calculatePercentage($this->usedCapacity);
    }

    private function calculatePercentage(int $used): float
    {
        if ($this->totalCapacity === 0) {
            return 0.0;
        }
        return ($used / $this->totalCapacity) * 100;
    }
}
