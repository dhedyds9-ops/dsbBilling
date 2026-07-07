<?php

namespace App\Repositories\Workforce;

use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\MaterialConsumption;
use Src\Domain\Workforce\Repositories\MaterialConsumptionRepositoryInterface;

class MaterialConsumptionRepository extends BaseRepository implements MaterialConsumptionRepositoryInterface {
    public function save(MaterialConsumption $consumption): MaterialConsumption {
        // TODO: Implement Eloquent persistence
        return $consumption;
    }

    public function findById(Uuid $id): ?MaterialConsumption {
        // TODO: Implement Eloquent retrieval
        return null;
    }

    public function findByTaskId(Uuid $taskId): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }

    public function delete(Uuid $id): void {
        // TODO: Implement Eloquent deletion
    }
}
