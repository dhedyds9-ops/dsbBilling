<?php

namespace Src\Domain\GIS\Repositories;

use Src\Domain\GIS\ServiceArea;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface ServiceAreaRepositoryInterface {
    public function save(ServiceArea $entity): void;
    public function findById(Uuid $id): ?ServiceArea;
    public function findAll(): array;
    public function delete(Uuid $id): void;
}
