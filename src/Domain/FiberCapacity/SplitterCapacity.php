<?php

namespace Src\Domain\FiberCapacity;

use DateTimeImmutable;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\FiberCapacity\Enums\PortStatus;
use Src\Domain\FiberCapacity\Events\CapacityUpdated;
use Src\Domain\FiberCapacity\Events\SplitterFull;
use Src\Domain\FiberCapacity\ValueObjects\SplitRatio;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class SplitterCapacity extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $splitterId,
        public readonly Uuid $odpId,
        public readonly SplitRatio $splitRatio,
        public int $totalPorts,
        public int $usedPorts,
        public int $reservedPorts,
        public CapacityStatus $status,
        public DateTimeImmutable $lastUpdated,
        public array $portStatuses = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $splitterId,
        Uuid $odpId,
        SplitRatio $splitRatio
    ): self {
        $totalPorts = $splitRatio->getPortCount();
        
        return new self(
            $id,
            $splitterId,
            $odpId,
            $splitRatio,
            $totalPorts,
            0,
            0,
            CapacityStatus::OPTIMAL,
            new DateTimeImmutable()
        );
    }

    public function allocatePort(): int
    {
        $available = $this->getAvailablePorts();
        
        if ($available <= 0) {
            $this->recordThat(new SplitterFull(
                $this->splitterId->value,
                (string) $this->id,
                $this->totalPorts,
                $this->usedPorts,
                $this->getUtilizationPercentage()
            ));
            
            throw new \InvalidArgumentException("Splitter has no available ports");
        }

        $previousUsed = $this->usedPorts;
        $portNumber = $this->findAvailablePort();
        
        $this->usedPorts++;
        $this->portStatuses[$portNumber] = PortStatus::ACTIVE->value;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->splitterId->value,
            'splitter',
            $previousUsed,
            $this->usedPorts,
            $this->totalPorts,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedPorts)
        ));

        return $portNumber;
    }

    public function releasePort(int $portNumber): void
    {
        if (!isset($this->portStatuses[$portNumber])) {
            throw new \InvalidArgumentException("Port {$portNumber} does not exist");
        }

        $previousUsed = $this->usedPorts;
        $this->usedPorts--;
        unset($this->portStatuses[$portNumber]);
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->splitterId->value,
            'splitter',
            $previousUsed,
            $this->usedPorts,
            $this->totalPorts,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedPorts)
        ));
    }

    public function reservePort(): int
    {
        $available = $this->getAvailablePorts();
        
        if ($available <= 0) {
            throw new \InvalidArgumentException("Splitter has no available ports for reservation");
        }

        $portNumber = $this->findAvailablePort();
        $this->reservedPorts++;
        $this->portStatuses[$portNumber] = PortStatus::AVAILABLE->value;
        $this->lastUpdated = new DateTimeImmutable();

        return $portNumber;
    }

    public function confirmReservation(int $portNumber): void
    {
        if (!isset($this->portStatuses[$portNumber])) {
            throw new \InvalidArgumentException("Port {$portNumber} does not exist");
        }

        if ($this->portStatuses[$portNumber] !== PortStatus::AVAILABLE->value) {
            throw new \InvalidArgumentException("Port {$portNumber} is not reserved");
        }

        $this->reservedPorts--;
        $this->usedPorts++;
        $this->portStatuses[$portNumber] = PortStatus::ACTIVE->value;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();
    }

    public function getAvailablePorts(): int
    {
        return $this->totalPorts - $this->usedPorts - $this->reservedPorts;
    }

    public function getUtilizationPercentage(): float
    {
        return $this->calculatePercentage($this->usedPorts);
    }

    public function hasCapacity(): bool
    {
        return $this->getAvailablePorts() > 0;
    }

    public function getPortStatus(int $portNumber): ?PortStatus
    {
        if (!isset($this->portStatuses[$portNumber])) {
            return null;
        }
        
        return PortStatus::from($this->portStatuses[$portNumber]);
    }

    private function findAvailablePort(): int
    {
        for ($i = 1; $i <= $this->totalPorts; $i++) {
            if (!isset($this->portStatuses[$i])) {
                return $i;
            }
        }
        
        throw new \RuntimeException("No available port found");
    }

    private function updateStatus(): void
    {
        $percentage = $this->getUtilizationPercentage();
        $this->status = CapacityStatus::fromPercentage($percentage);
    }

    private function calculatePercentage(int $used): float
    {
        if ($this->totalPorts === 0) {
            return 0.0;
        }
        return ($used / $this->totalPorts) * 100;
    }
}
