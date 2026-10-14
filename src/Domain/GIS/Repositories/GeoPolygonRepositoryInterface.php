<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\GeoPolygon;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface GeoPolygonRepositoryInterface {
    public function save(GeoPolygon $entity): void;
    public function findById(Uuid $id): ?GeoPolygon;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
