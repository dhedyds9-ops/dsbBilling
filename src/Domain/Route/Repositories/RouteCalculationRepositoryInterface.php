<?php

namespace Src\Domain\Route\Repositories;

use Src\Domain\Route\RouteCalculation;
use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface RouteCalculationRepositoryInterface
{
    public function save(RouteCalculation $calculation): void;
    
    public function findById(Uuid $id): ?RouteCalculation;
    
    public function findByNodes(string $sourceNodeId, string $targetNodeId): ?RouteCalculation;
    
    public function findByAlgorithm(AlgorithmType $algorithm): array;
    
    public function findRecentCalculations(int $limit = 10): array;
    
    public function findFailedCalculations(): array;
    
    public function delete(Uuid $id): void;
}
