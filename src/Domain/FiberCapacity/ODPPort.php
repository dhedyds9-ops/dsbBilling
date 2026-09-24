<?php

namespace Src\Domain\FiberCapacity;

use DateTimeImmutable;
use Src\Domain\FiberCapacity\Enums\CapacityStatus;
use Src\Domain\FiberCapacity\Enums\PortStatus;
use Src\Domain\FiberCapacity\Events\CapacityUpdated;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ODPPort extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $odpId,
        public readonly int $portNumber,
        public int $totalPorts,
        public int $usedPorts,
        public int $reservedPorts,
        public CapacityStatus $status,
        public DateTimeImmutable $lastUpdated,
        public array $portStatuses = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $odpId,
        int $portNumber,
        int $totalPorts
    ): self {
        return new self(
            $id,
            $odpId,
            $portNumber,
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
            throw new \InvalidArgumentException("ODP port has no available ports");
        }

        $previousUsed = $this->usedPorts;
        $portNumber = $this->findAvailablePort();
        
        $this->usedPorts++;
        $this->portStatuses[$portNumber] = PortStatus::ACTIVE->value;
        $this->lastUpdated = new DateTimeImmutable();
        $this->updateStatus();

        $this->recordThat(new CapacityUpdated(
            $this->odpId->value,
            'odp_port',
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
            $this->odpId->value,
            'odp_port',
            $previousUsed,
            $this->usedPorts,
            $this->totalPorts,
            $this->calculatePercentage($previousUsed),
            $this->calculatePercentage($this->usedPorts)
        ));
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

    public function setPortStatus(int $portNumber, PortStatus $portStatus): void
    {
        if (!isset($this->portStatuses[$portNumber])) {
            throw new \InvalidArgumentException("Port {$portNumber} does not exist");
        }

        $this->portStatuses[$portNumber] = $portStatus->value;
        $this->lastUpdated = new DateTimeImmutable();
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
