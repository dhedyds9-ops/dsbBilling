<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\GeoRoute;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface GeoRouteRepositoryInterface {
    public function save(GeoRoute $entity): void;
    public function findById(Uuid $id): ?GeoRoute;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
