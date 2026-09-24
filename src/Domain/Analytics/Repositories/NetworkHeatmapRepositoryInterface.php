<?php

namespace Src\Domain\Analytics\Repositories;

use Src\Domain\Analytics\Enums\HeatmapLayer;
use Src\Domain\Analytics\NetworkHeatmap;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface NetworkHeatmapRepositoryInterface
{
    public function save(NetworkHeatmap $heatmap): void;
    
    public function findById(Uuid $id): ?NetworkHeatmap;
    
    public function findByAreaId(string $areaId): array;
    
    public function findByLayer(HeatmapLayer $layer): array;
    
    public function findLatestByAreaAndLayer(string $areaId, HeatmapLayer $layer): ?NetworkHeatmap;
    
    public function findUpdatedSince(\DateTimeImmutable $since): array;
    
    public function delete(Uuid $id): void;
}
