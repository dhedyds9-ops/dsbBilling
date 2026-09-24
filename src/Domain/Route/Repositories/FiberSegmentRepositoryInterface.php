<?php

namespace Src\Domain\Route\Repositories;

use Src\Domain\Route\FiberSegment;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface FiberSegmentRepositoryInterface
{
    public function save(FiberSegment $segment): void;
    
    public function findById(Uuid $id): ?FiberSegment;
    
    public function findByStartNode(string $nodeId): array;
    
    public function findByEndNode(string $nodeId): array;
    
    public function findByNodes(string $startNodeId, string $endNodeId): ?FiberSegment;
    
    public function findActiveSegments(): array;
    
    public function findAvailableSegments(): array;
    
    public function findByBoundingBox(float $minLat, float $minLon, float $maxLat, float $maxLon): array;
    
    public function delete(Uuid $id): void;
}
