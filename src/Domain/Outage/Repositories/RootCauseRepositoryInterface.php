<?php

namespace Src\Domain\Outage\Repositories;

use Src\Domain\Outage\RootCause;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface RootCauseRepositoryInterface
{
    public function save(RootCause $rootCause): void;
    
    public function findById(Uuid $id): ?RootCause;
    
    public function findByOutageId(Uuid $outageId): ?RootCause;
    
    public function findByCauseCode(string $causeCode): array;
    
    public function findByConfidenceRange(int $minScore, int $maxScore): array;
    
    public function findSimilarRootCauses(string $causeCode, int $limit = 10): array;
    
    public function delete(Uuid $id): void;
}
