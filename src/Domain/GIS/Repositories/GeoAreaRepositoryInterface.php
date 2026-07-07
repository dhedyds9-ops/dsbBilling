<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\GeoArea;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface GeoAreaRepositoryInterface {
    public function save(GeoArea $entity): void;
    public function findById(Uuid $id): ?GeoArea;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
