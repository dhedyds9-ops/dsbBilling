<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\CoverageArea;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface CoverageAreaRepositoryInterface {
    public function save(CoverageArea $entity): void;
    public function findById(Uuid $id): ?CoverageArea;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
