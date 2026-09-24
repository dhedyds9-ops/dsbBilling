<?php

namespace Src\Domain\FiberCapacity\Services;

use Src\Domain\FiberCapacity\OLTCapacity;
use Src\Domain\FiberCapacity\PonCapacity;
use Src\Domain\FiberCapacity\Repositories\OLTCapacityRepositoryInterface;
use Src\Domain\FiberCapacity\Repositories\PonCapacityRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class OLTCapacityService
{
    public function __construct(
        private OLTCapacityRepositoryInterface $oltRepository,
        private PonCapacityRepositoryInterface $ponRepository
    ) {}

    public function createOLTCapacity(
        Uuid $oltId,
        int $totalPonPorts
    ): OLTCapacity {
        $capacity = OLTCapacity::create(
            Uuid::generate(),
            $oltId,
            $totalPonPorts
        );

        $this->oltRepository->save($capacity);
        return $capacity;
    }

    public function allocatePonPort(Uuid $oltId): int
    {
        $capacity = $this->oltRepository->findByOltId($oltId);
        
        if (!$capacity) {
            throw new \InvalidArgumentException("OLT capacity not found");
        }

        $portNumber = $capacity->allocatePonPort();
        $this->oltRepository->save($capacity);

        return $portNumber;
    }

    public function releasePonPort(Uuid $oltId, int $portNumber): void
    {
        $capacity = $this->oltRepository->findByOltId($oltId);
        
        if (!$capacity) {
            throw new \InvalidArgumentException("OLT capacity not found");
        }

        $capacity->releasePonPort($portNumber);
        $this->oltRepository->save($capacity);
    }

    public function addOnuToPonPort(Uuid $ponPortId, string $onuId): void
    {
        $ponCapacity = $this->ponRepository->findByPonPortId($ponPortId);
        
        if (!$ponCapacity) {
            throw new \InvalidArgumentException("PON capacity not found");
        }

        $ponCapacity->addOnu($onuId);
        $this->ponRepository->save($ponCapacity);

        $oltCapacity = $this->oltRepository->findByOltId($ponCapacity->oltId);
        if ($oltCapacity) {
            $oltCapacity->addOnu($ponCapacity->ponPortNumber);
            $this->oltRepository->save($oltCapacity);
        }
    }

    public function removeOnuFromPonPort(Uuid $ponPortId, string $onuId): void
    {
        $ponCapacity = $this->ponRepository->findByPonPortId($ponPortId);
        
        if (!$ponCapacity) {
            throw new \InvalidArgumentException("PON capacity not found");
        }

        $ponCapacity->removeOnu($onuId);
        $this->ponRepository->save($ponCapacity);

        $oltCapacity = $this->oltRepository->findByOltId($ponCapacity->oltId);
        if ($oltCapacity) {
            $oltCapacity->removeOnu($ponCapacity->ponPortNumber);
            $this->oltRepository->save($oltCapacity);
        }
    }

    public function getOltStats(Uuid $oltId): array
    {
        $capacity = $this->oltRepository->findByOltId($oltId);
        
        if (!$capacity) {
            return [
                'total_ports' => 0,
                'used_ports' => 0,
                'reserved_ports' => 0,
                'available_ports' => 0,
                'utilization' => 0.0,
                'status' => 'unknown',
                'total_onus' => 0
            ];
        }

        return [
            'total_ports' => $capacity->totalPonPorts,
            'used_ports' => $capacity->usedPonPorts,
            'reserved_ports' => $capacity->reservedPonPorts,
            'available_ports' => $capacity->getAvailablePonPorts(),
            'utilization' => $capacity->getUtilizationPercentage(),
            'status' => $capacity->status->value,
            'total_onus' => $capacity->getTotalOnuCount()
        ];
    }

    public function getPonStats(Uuid $ponPortId): array
    {
        $capacity = $this->ponRepository->findByPonPortId($ponPortId);
        
        if (!$capacity) {
            return [
                'total_capacity' => 0,
                'used_capacity' => 0,
                'reserved_capacity' => 0,
                'available_capacity' => 0,
                'utilization' => 0.0,
                'status' => 'unknown',
                'port_status' => 'unknown'
            ];
        }

        return [
            'total_capacity' => $capacity->totalOnuCapacity,
            'used_capacity' => $capacity->usedOnuCapacity,
            'reserved_capacity' => $capacity->reservedOnuCapacity,
            'available_capacity' => $capacity->getAvailableOnuCapacity(),
            'utilization' => $capacity->getUtilizationPercentage(),
            'status' => $capacity->status->value,
            'port_status' => $capacity->portStatus->value
        ];
    }

    public function findAvailablePonPort(Uuid $oltId): ?array
    {
        $ponPorts = $this->ponRepository->findAvailablePonPorts($oltId);
        
        if (empty($ponPorts)) {
            return null;
        }

        usort($ponPorts, function ($a, $b) {
            return $b->getAvailableOnuCapacity() <=> $a->getAvailableOnuCapacity();
        });

        $selected = $ponPorts[0];
        
        return [
            'pon_port_id' => $selected->ponPortId->value,
            'pon_port_number' => $selected->ponPortNumber,
            'available_onu_slots' => $selected->getAvailableOnuCapacity()
        ];
    }

    public function getOltWithAvailableCapacity(): array
    {
        return $this->oltRepository->findWithAvailableCapacity();
    }
}
