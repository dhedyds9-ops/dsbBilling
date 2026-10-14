<?php

namespace Src\Domain\Route\Repositories;

use Src\Domain\Route\NearestNode;
use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface NearestNodeRepositoryInterface
{
    public function save(NearestNode $nearestNode): void;
    
    public function findById(Uuid $id): ?NearestNode;
    
    public function findBySearchNode(string $nodeId): array;
    
    public function findByAlgorithm(AlgorithmType $algorithm): array;
    
    public function findRecentSearches(int $limit = 10): array;
    
    public function delete(Uuid $id): void;
}
