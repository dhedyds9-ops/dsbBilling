<?php

namespace Src\Domain\Outage\Repositories;

use Src\Domain\Outage\AffectedService;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AffectedServiceRepositoryInterface
{
    public function save(AffectedService $service): void;
    
    public function findById(Uuid $id): ?AffectedService;
    
    public function findByOutageId(Uuid $outageId): array;
    
    public function findByCustomerId(string $customerId): array;
    
    public function findByServiceId(string $serviceId): array;
    
    public function findByImpactLevel(ImpactLevel $impactLevel): array;
    
    public function findRecoveredServices(Uuid $outageId): array;
    
    public function findUnrecoveredServices(Uuid $outageId): array;
    
    public function delete(Uuid $id): void;
}
