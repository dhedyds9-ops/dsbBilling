<?php

namespace Src\Domain\Outage\Repositories;

use Src\Domain\Outage\AffectedCustomer;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AffectedCustomerRepositoryInterface
{
    public function save(AffectedCustomer $customer): void;
    
    public function findById(Uuid $id): ?AffectedCustomer;
    
    public function findByOutageId(Uuid $outageId): array;
    
    public function findByCustomerId(string $customerId): array;
    
    public function findByImpactLevel(ImpactLevel $impactLevel): array;
    
    public function findNotifiedCustomers(Uuid $outageId): array;
    
    public function findUnnotifiedCustomers(Uuid $outageId): array;
    
    public function findRecoveredCustomers(Uuid $outageId): array;
    
    public function findUnrecoveredCustomers(Uuid $outageId): array;
    
    public function delete(Uuid $id): void;
}
