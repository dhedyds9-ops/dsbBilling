<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\CoordinateReferenceSystem;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface CoordinateReferenceSystemRepositoryInterface {
    public function save(CoordinateReferenceSystem $entity): void;
    public function findById(Uuid $id): ?CoordinateReferenceSystem;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
