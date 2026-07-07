<?php

namespace Src\Domain\Outage\Repositories;

use Src\Domain\Outage\Outage;
use Src\Domain\Outage\Enums\OutageStatus;
use Src\Domain\Outage\Enums\OutageSeverity;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface OutageRepositoryInterface
{
    public function save(Outage $outage): void;
    
    public function findById(Uuid $id): ?Outage;
    
    public function findByIncidentId(Uuid $incidentId): ?Outage;
    
    public function findByStatus(OutageStatus $status): array;
    
    public function findBySeverity(OutageSeverity $severity): array;
    
    public function findActiveOutages(): array;
    
    public function findByDateRange(\DateTimeImmutable $start, \DateTimeImmutable $end): array;
    
    public function findByOriginNode(string $nodeId): array;
    
    public function delete(Uuid $id): void;
}
