<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\GeoPoint;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface GeoPointRepositoryInterface {
    public function save(GeoPoint $entity): void;
    public function findById(Uuid $id): ?GeoPoint;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
