<?php

namespace Src\Domain\BusinessIntelligence\Repositories;

use Src\Domain\BusinessIntelligence\DataCube;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface DataCubeRepositoryInterface
{
    public function findById(Uuid $id): ?DataCube;

    public function findByName(string $name): ?DataCube;

    public function save(DataCube $dataCube): void;

    public function delete(Uuid $id): void;

    public function findByModule(string $module): array;

    public function findByTable(string $table): array;

    public function findByCreator(Uuid $creatorId): array;

    public function findStaleCubes(int $staleMinutes = 60): array;
}
