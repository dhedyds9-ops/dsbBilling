<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\MapLayer;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface MapLayerRepositoryInterface {
    public function save(MapLayer $entity): void;
    public function findById(Uuid $id): ?MapLayer;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
