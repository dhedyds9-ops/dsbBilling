<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\MaterialConsumption;

interface MaterialConsumptionRepositoryInterface {
    public function save(MaterialConsumption $consumption): MaterialConsumption;
    public function findById(Uuid $id): ?MaterialConsumption;
    public function findByTaskId(Uuid $taskId): array;
    public function delete(Uuid $id): void;
}
