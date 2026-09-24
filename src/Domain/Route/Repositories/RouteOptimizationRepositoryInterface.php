<?php

namespace Src\Domain\Route\Repositories;

use Src\Domain\Route\RouteOptimization;
use Src\Domain\Route\Enums\RouteStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface RouteOptimizationRepositoryInterface
{
    public function save(RouteOptimization $optimization): void;
    
    public function findById(Uuid $id): ?RouteOptimization;
    
    public function findByRouteId(Uuid $routeId): ?RouteOptimization;
    
    public function findByStatus(RouteStatus $status): array;
    
    public function findSuccessfulOptimizations(int $limit = 10): array;
    
    public function delete(Uuid $id): void;
}
