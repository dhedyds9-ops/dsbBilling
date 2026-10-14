<?php

namespace Src\Domain\FiberCapacity;

use DateTimeImmutable;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\FiberCapacity\Enums\PortStatus;
use Src\Domain\FiberCapacity\Events\CapacityUpdated;
use Src\Domain\FiberCapacity\Events\CapacityExceeded;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class OLTCapacity extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $oltId,
        public int $totalPonPorts,
        public int $usedPonPorts,
        public int $reservedPonPorts,
        public CapacityStatus $status,
        public DateTimeImmutable $lastUpdated,
        public array $ponPortStatuses = [],
        public array $onuCountPerPort = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $oltId,
        int $totalPonPorts
    ): self {
        return new self(
            $id,
            $oltId,
            $totalPonPorts,
            0,
            0,
            CapacityStatus::OPTIMAL,
            new DateTimeImmutable()
        );
    }

    public function allocatePonPort(): int
    {
        $available = $this->getAvailablePonPorts();
        
        if ($available <= 0) {
            $this->recordThat(new CapacityExceeded(
                $this->oltId->value,
                'olt',
                1,
                $available,
                $this->totalPonPorts
            ));
            
            throw new \InvalidArgumentException("OLT has no available PON ports");
        }

        $previousUsed = $this->usedPonPorts;
        $portNumber = $this->findAvailablePonPort();
        
        $this->usedPonPorts++;
        $this->ponPortStatuses[$portNumber] = PortStatus::ACTIVE->value;
        $this->onuCountPerPort[$portNumber] = 0;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->oltId->value,
            'olt_pon_port',
            $previousUsed,
            $this->usedPonPorts,
            $this->totalPonPorts,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedPonPorts)
        ));

        return $portNumber;
    }

    public function releasePonPort(int $portNumber): void
    {
        if (!isset($this->ponPortStatuses[$portNumber])) {
            throw new \InvalidArgumentException("PON port {$portNumber} does not exist");
        }

        if (($this->onuCountPerPort[$portNumber] ?? 0) > 0) {
            throw new \InvalidArgumentException(
                "Cannot release PON port {$portNumber}. Still has ONUs connected"
            );
        }

        $previousUsed = $this->usedPonPorts;
        $this->usedPonPorts--;
        unset($this->ponPortStatuses[$portNumber]);
        unset($this->onuCountPerPort[$portNumber]);
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->oltId->value,
            'olt_pon_port',
            $previousUsed,
            $this->usedPonPorts,
            $this->totalPonPorts,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedPonPorts)
        ));
    }

    public function addOnu(int $ponPortNumber): void
    {
        if (!isset($this->ponPortStatuses[$ponPortNumber])) {
            throw new \InvalidArgumentException("PON port {$ponPortNumber} does not exist");
        }

        if (!isset($this->onuCountPerPort[$ponPortNumber])) {
            $this->onuCountPerPort[$ponPortNumber] = 0;
        }

        $this->onuCountPerPort[$ponPortNumber]++;
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function removeOnu(int $ponPortNumber): void
    {
        if (!isset($this->onuCountPerPort[$ponPortNumber])) {
            throw new \InvalidArgumentException("PON port {$ponPortNumber} has no ONUs");
        }

        $this->onuCountPerPort[$ponPortNumber]--;
        
        if ($this->onuCountPerPort[$ponPortNumber] < 0) {
            $this->onuCountPerPort[$ponPortNumber] = 0;
        }

        $this->lastUpdated = new DateTimeImmutable();
    }

    public function getOnuCount(int $ponPortNumber): int
    {
        return $this->onuCountPerPort[$ponPortNumber] ?? 0;
    }

    public function getTotalOnuCount(): int
    {
        return array_sum($this->onuCountPerPort);
    }

    public function getAvailablePonPorts(): int
    {
        return $this->totalPonPorts - $this->usedPonPorts - $this->reservedPonPorts;
    }

    public function getUtilizationPercentage(): float
    {
        return $this->calculatePercentage($this->usedPonPorts);
    }

    public function hasCapacity(): bool
    {
        return $this->getAvailablePonPorts() > 0;
    }

    private function findAvailablePonPort(): int
    {
        for ($i = 1; $i <= $this->totalPonPorts; $i++) {
            if (!isset($this->ponPortStatuses[$i])) {
                return $i;
            }
        }
        
        throw new \RuntimeException("No available PON port found");
    }

    private function updateStatus(): void
    {
        $percentage = $this->getUtilizationPercentage();
        $this->status = CapacityStatus::fromPercentage($percentage);
    }

    private function calculatePercentage(int $used): float
    {
        if ($this->totalPonPorts === 0) {
            return 0.0;
        }
        return ($used / $this->totalPonPorts) * 100;
    }
}
