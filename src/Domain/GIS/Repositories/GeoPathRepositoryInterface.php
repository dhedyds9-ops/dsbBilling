<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\GeoPath;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface GeoPathRepositoryInterface {
    public function save(GeoPath $entity): void;
    public function findById(Uuid $id): ?GeoPath;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
