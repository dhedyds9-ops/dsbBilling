<?php

namespace Src\Domain\Provisioning;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CapacityManagement extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $resourceId,
        public readonly ResourceType $resourceType,
        public int $totalCapacity,
        public int $usedCapacity,
        public int $reservedCapacity,
        public DateTimeImmutable $lastUpdated,
        public array $metrics = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $resourceId,
        ResourceType $resourceType,
        int $totalCapacity
    ): self {
        return new self(
            $id,
            $resourceId,
            $resourceType,
            $totalCapacity,
            0,
            0,
            new DateTimeImmutable()
        );
    }

    public function getAvailableCapacity(): int
    {
        return $this->totalCapacity - $this->usedCapacity - $this->reservedCapacity;
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->totalCapacity === 0) {
            return 0.0;
        }
        return ($this->usedCapacity / $this->totalCapacity) * 100;
    }

    public function incrementUsed(int $amount = 1): void
    {
        $this->usedCapacity += $amount;
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function decrementUsed(int $amount = 1): void
    {
        $this->usedCapacity = max(0, $this->usedCapacity - $amount);
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function incrementReserved(int $amount = 1): void
    {
        $this->reservedCapacity += $amount;
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function decrementReserved(int $amount = 1): void
    {
        $this->reservedCapacity = max(0, $this->reservedCapacity - $amount);
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function hasAvailableCapacity(int $required = 1): bool
    {
        return $this->getAvailableCapacity() >= $required;
    }

    public function addMetric(string $key, mixed $value): void
    {
        $this->metrics[$key] = [
            'value' => $value,
            'timestamp' => new DateTimeImmutable()
        ];
    }
}
