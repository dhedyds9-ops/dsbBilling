<?php

namespace Src\Domain\FiberCapacity\Services;

use Src\Domain\FiberCapacity\SplitterCapacity;
use Src\Domain\FiberCapacity\Repositories\SplitterCapacityRepositoryInterface;
use Src\Domain\FiberCapacity\ValueObjects\SplitRatio;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class SplitterPlanningService
{
    public function __construct(
        private SplitterCapacityRepositoryInterface $splitterRepository
    ) {}

    public function createSplitterCapacity(
        Uuid $splitterId,
        Uuid $odpId,
        SplitRatio $splitRatio
    ): SplitterCapacity {
        $capacity = SplitterCapacity::create(
            Uuid::generate(),
            $splitterId,
            $odpId,
            $splitRatio
        );

        $this->splitterRepository->save($capacity);
        return $capacity;
    }

    public function allocatePort(Uuid $splitterId): int
    {
        $capacity = $this->splitterRepository->findBySplitterId($splitterId);
        
        if (!$capacity) {
            throw new \InvalidArgumentException("Splitter capacity not found");
        }

        $portNumber = $capacity->allocatePort();
        $this->splitterRepository->save($capacity);

        return $portNumber;
    }

    public function releasePort(Uuid $splitterId, int $portNumber): void
    {
        $capacity = $this->splitterRepository->findBySplitterId($splitterId);
        
        if (!$capacity) {
            throw new \InvalidArgumentException("Splitter capacity not found");
        }

        $capacity->releasePort($portNumber);
        $this->splitterRepository->save($capacity);
    }

    public function reservePort(Uuid $splitterId): int
    {
        $capacity = $this->splitterRepository->findBySplitterId($splitterId);
        
        if (!$capacity) {
            throw new \InvalidArgumentException("Splitter capacity not found");
        }

        $portNumber = $capacity->reservePort();
        $this->splitterRepository->save($capacity);

        return $portNumber;
    }

    public function confirmReservation(Uuid $splitterId, int $portNumber): void
    {
        $capacity = $this->splitterRepository->findBySplitterId($splitterId);
        
        if (!$capacity) {
            throw new \InvalidArgumentException("Splitter capacity not found");
        }

        $capacity->confirmReservation($portNumber);
        $this->splitterRepository->save($capacity);
    }

    public function getSplitterStats(Uuid $splitterId): array
    {
        $capacity = $this->splitterRepository->findBySplitterId($splitterId);
        
        if (!$capacity) {
            return [
                'total' => 0,
                'used' => 0,
                'reserved' => 0,
                'available' => 0,
                'utilization' => 0.0,
                'status' => 'unknown',
                'split_ratio' => null
            ];
        }

        return [
            'total' => $capacity->totalPorts,
            'used' => $capacity->usedPorts,
            'reserved' => $capacity->reservedPorts,
            'available' => $capacity->getAvailablePorts(),
            'utilization' => $capacity->getUtilizationPercentage(),
            'status' => $capacity->status->value,
            'split_ratio' => (string) $capacity->splitRatio
        ];
    }

    public function findAvailableSplitter(Uuid $odpId): ?SplitterCapacity
    {
        $splitters = $this->splitterRepository->findAvailableSplitters($odpId);
        
        if (empty($splitters)) {
            return null;
        }

        usort($splitters, function ($a, $b) {
            return $b->getAvailablePorts() <=> $a->getAvailablePorts();
        });

        return $splitters[0];
    }

    public function suggestSplitterRatio(int $requiredPorts): SplitRatio
    {
        $ratios = SplitRatio::SUPPORTED_RATIOS;
        
        foreach ($ratios as $ratio) {
            if ($ratio >= $requiredPorts) {
                return new SplitRatio($ratio);
            }
        }

        return new SplitRatio(128);
    }

    public function calculateCascadeRequirements(int $totalPorts): array
    {
        $requirements = [];
        $remaining = $totalPorts;
        $level = 1;

        while ($remaining > 0) {
            $ratio = $this->suggestSplitterRatio($remaining);
            $splitterCount = (int) ceil($remaining / $ratio->getPortCount());
            
            $requirements[] = [
                'level' => $level,
                'ratio' => (string) $ratio,
                'splitter_count' => $splitterCount,
                'ports_provided' => $splitterCount * $ratio->getPortCount(),
                'ports_needed' => $remaining
            ];

            $remaining = $splitterCount;
            $level++;
        }

        return $requirements;
    }
}
