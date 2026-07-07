<?php

namespace Src\Domain\Outage\Repositories;

use Src\Domain\Outage\RecoveryPlan;
use Src\Domain\Outage\Enums\RecoveryStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface RecoveryPlanRepositoryInterface
{
    public function save(RecoveryPlan $plan): void;
    
    public function findById(Uuid $id): ?RecoveryPlan;
    
    public function findByOutageId(Uuid $outageId): ?RecoveryPlan;
    
    public function findByTechnicianId(string $technicianId): array;
    
    public function findByStatus(RecoveryStatus $status): array;
    
    public function findActivePlans(): array;
    
    public function findCompletedPlans(\DateTimeImmutable $since = null): array;
    
    public function delete(Uuid $id): void;
}
