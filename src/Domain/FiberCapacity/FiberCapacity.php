<?php

namespace Src\Domain\FiberCapacity;

use DateTimeImmutable;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\FiberCapacity\Events\CapacityUpdated;
use Src\Domain\FiberCapacity\Events\CapacityExceeded;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class FiberCapacity extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $resourceId,
        public readonly string $resourceType,
        public int $totalCore,
        public int $usedCore,
        public int $reservedCore,
        public CapacityStatus $status,
        public DateTimeImmutable $lastUpdated,
        public array $capacityHistory = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $resourceId,
        string $resourceType,
        int $totalCore
    ): self {
        return new self(
            $id,
            $resourceId,
            $resourceType,
            $totalCore,
            0,
            0,
            CapacityStatus::OPTIMAL,
            new DateTimeImmutable()
        );
    }

    public function allocateCore(int $count = 1): void
    {
        $previousUsed = $this->usedCore;
        $available = $this->getAvailableCore();
        
        if ($available < $count) {
            $this->recordThat(new CapacityExceeded(
                $this->resourceId->value,
                $this->resourceType,
                $count,
                $available,
                $this->totalCore
            ));
            
            throw new \InvalidArgumentException(
                "Cannot allocate {$count} cores. Available: {$available}"
            );
        }

        $this->usedCore += $count;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->resourceId->value,
            $this->resourceType,
            $previousUsed,
            $this->usedCore,
            $this->totalCore,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedCore)
        ));
    }

    public function releaseCore(int $count = 1): void
    {
        $previousUsed = $this->usedCore;
        
        if ($this->usedCore < $count) {
            throw new \InvalidArgumentException(
                "Cannot release {$count} cores. Used: {$this->usedCore}"
            );
        }

        $this->usedCore -= $count;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->resourceId->value,
            $this->resourceType,
            $previousUsed,
            $this->usedCore,
            $this->totalCore,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedCore)
        ));
    }

    public function reserveCore(int $count = 1): void
    {
        $available = $this->getAvailableCore();
        
        if ($available < $count) {
            throw new \InvalidArgumentException(
                "Cannot reserve {$count} cores. Available: {$available}"
            );
        }

        $this->reservedCore += $count;
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function releaseReservation(int $count = 1): void
    {
        if ($this->reservedCore < $count) {
            throw new \InvalidArgumentException(
                "Cannot release {$count} reservations. Reserved: {$this->reservedCore}"
            );
        }

        $this->reservedCore -= $count;
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function getAvailableCore(): int
    {
        return $this->totalCore - $this->usedCore - $this->reservedCore;
    }

    public function getUtilizationPercentage(): float
    {
        return $this->calculatePercentage($this->usedCore);
    }

    public function hasCapacity(int $required = 1): bool
    {
        return $this->getAvailableCore() >= $required;
    }

    public function updateTotalCore(int $newTotal): void
    {
        if ($newTotal < $this->usedCore) {
            throw new \InvalidArgumentException(
                "New total cannot be less than used cores"
            );
        }

        $this->totalCore = $newTotal;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();
    }

    public function addHistoryEntry(array $entry): void
    {
        $this->capacityHistory[] = [
            ...$entry,
            'timestamp' => new DateTimeImmutable()
        ];
    }

    private function updateStatus(): void
    {
        $percentage = $this->getUtilizationPercentage();
        
        $this->status = CapacityStatus::fromPercentage($percentage);
    }

    private function calculatePercentage(int $used): float
    {
        if ($this->totalCore === 0) {
            return 0.0;
        }
        return ($used / $this->totalCore) * 100;
    }
}
