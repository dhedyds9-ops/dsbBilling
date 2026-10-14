<?php

namespace Src\Domain\FiberCapacity;

use DateTimeImmutable;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\FiberCapacity\Enums\PortStatus;
use Src\Domain\FiberCapacity\Events\CapacityUpdated;
use Src\Domain\FiberCapacity\Events\CapacityExceeded;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class PonCapacity extends AggregateRoot
{
    public const MAX_ONU_PER_PON_PORT = 64;

    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $ponPortId,
        public readonly Uuid $oltId,
        public readonly int $ponPortNumber,
        public int $totalOnuCapacity,
        public int $usedOnuCapacity,
        public int $reservedOnuCapacity,
        public CapacityStatus $status,
        public PortStatus $portStatus,
        public DateTimeImmutable $lastUpdated,
        public array $onuAllocations = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $ponPortId,
        Uuid $oltId,
        int $ponPortNumber,
        int $totalOnuCapacity = self::MAX_ONU_PER_PON_PORT
    ): self {
        return new self(
            $id,
            $ponPortId,
            $oltId,
            $ponPortNumber,
            $totalOnuCapacity,
            0,
            0,
            CapacityStatus::OPTIMAL,
            PortStatus::AVAILABLE,
            new DateTimeImmutable()
        );
    }

    public function allocateOnu(string $onuId): int
    {
        $available = $this->getAvailableOnuCapacity();
        
        if ($available <= 0) {
            $this->recordThat(new CapacityExceeded(
                $this->ponPortId->value,
                'pon_port',
                1,
                $available,
                $this->totalOnuCapacity
            ));
            
            throw new \InvalidArgumentException("PON port has no available capacity for ONU");
        }

        $previousUsed = $this->usedOnuCapacity;
        $onuSlot = $this->findAvailableOnuSlot();
        
        $this->usedOnuCapacity++;
        $this->onuAllocations[$onuSlot] = [
            'onu_id' => $onuId,
            'allocated_at' => new DateTimeImmutable()
        ];
        $this->portStatus = PortStatus::ACTIVE;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->ponPortId->value,
            'pon_onu',
            $previousUsed,
            $this->usedOnuCapacity,
            $this->totalOnuCapacity,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedOnuCapacity)
        ));

        return $onuSlot;
    }

    public function releaseOnu(string $onuId): void
    {
        $slot = null;
        
        foreach ($this->onuAllocations as $slotNumber => $allocation) {
            if ($allocation['onu_id'] === $onuId) {
                $slot = $slotNumber;
                break;
            }
        }

        if ($slot === null) {
            throw new \InvalidArgumentException("ONU {$onuId} not found in this PON port");
        }

        $previousUsed = $this->usedOnuCapacity;
        $this->usedOnuCapacity--;
        unset($this->onuAllocations[$slot]);
        
        if ($this->usedOnuCapacity === 0) {
            $this->portStatus = PortStatus::AVAILABLE;
        }
        
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->ponPortId->value,
            'pon_onu',
            $previousUsed,
            $this->usedOnuCapacity,
            $this->totalOnuCapacity,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedOnuCapacity)
        ));
    }

    public function reserveOnu(string $reservationId): int
    {
        $available = $this->getAvailableOnuCapacity();
        
        if ($available <= 0) {
            throw new \InvalidArgumentException("PON port has no available capacity for reservation");
        }

        $onuSlot = $this->findAvailableOnuSlot();
        $this->reservedOnuCapacity++;
        $this->onuAllocations[$onuSlot] = [
            'onu_id' => $reservationId,
            'reserved_at' => new DateTimeImmutable(),
            'is_reservation' => true
        ];
        $this->lastUpdated = new DateTimeImmutable();

        return $onuSlot;
    }

    public function confirmReservation(int $slotNumber, string $onuId): void
    {
        if (!isset($this->onuAllocations[$slotNumber])) {
            throw new \InvalidArgumentException("Slot {$slotNumber} is not reserved");
        }

        if (!($this->onuAllocations[$slotNumber]['is_reservation'] ?? false)) {
            throw new \InvalidArgumentException("Slot {$slotNumber} is not a reservation");
        }

        $this->reservedOnuCapacity--;
        $this->usedOnuCapacity++;
        $this->onuAllocations[$slotNumber] = [
            'onu_id' => $onuId,
            'allocated_at' => new DateTimeImmutable()
        ];
        $this->portStatus = PortStatus::ACTIVE;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();
    }

    public function getAvailableOnuCapacity(): int
    {
        return $this->totalOnuCapacity - $this->usedOnuCapacity - $this->reservedOnuCapacity;
    }

    public function getUtilizationPercentage(): float
    {
        return $this->calculatePercentage($this->usedOnuCapacity);
    }

    public function hasCapacity(): bool
    {
        return $this->getAvailableOnuCapacity() > 0;
    }

    public function shutdown(): void
    {
        $this->portStatus = PortStatus::SHUTDOWN;
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->portStatus = PortStatus::ACTIVE;
        $this->lastUpdated = new DateTimeImmutable();
    }

    private function findAvailableOnuSlot(): int
    {
        for ($i = 1; $i <= $this->totalOnuCapacity; $i++) {
            if (!isset($this->onuAllocations[$i])) {
                return $i;
            }
        }
        
        throw new \RuntimeException("No available ONU slot found");
    }

    private function updateStatus(): void
    {
        $percentage = $this->getUtilizationPercentage();
        $this->status = CapacityStatus::fromPercentage($percentage);
    }

    private function calculatePercentage(int $used): float
    {
        if ($this->totalOnuCapacity === 0) {
            return 0.0;
        }
        return ($used / $this->totalOnuCapacity) * 100;
    }
}
