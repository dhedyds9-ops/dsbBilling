<?php

namespace Src\Domain\Route\Repositories;

use Src\Domain\Route\FiberRoute;
use Src\Domain\Route\Enums\RouteStatus;
use Src\Domain\Route\Enums\RouteType;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface FiberRouteRepositoryInterface
{
    public function save(FiberRoute $route): void;
    
    public function findById(Uuid $id): ?FiberRoute;
    
    public function findByNodes(string $sourceNodeId, string $targetNodeId): ?FiberRoute;
    
    public function findByStatus(RouteStatus $status): array;
    
    public function findByType(RouteType $type): array;
    
    public function findActiveRoutes(): array;
    
    public function findBySourceNode(string $nodeId): array;
    
    public function findByTargetNode(string $nodeId): array;
    
    public function delete(Uuid $id): void;
}
