<?php

namespace App\Repositories\Workforce;

use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\MaintenanceTask;
use Src\Domain\Workforce\Repositories\MaintenanceTaskRepositoryInterface;

class MaintenanceTaskRepository extends BaseRepository implements MaintenanceTaskRepositoryInterface {
    public function save(MaintenanceTask $task): MaintenanceTask {
        // TODO: Implement Eloquent persistence
        return $task;
    }

    public function findById(Uuid $id): ?MaintenanceTask {
        // TODO: Implement Eloquent retrieval
        return null;
    }

    public function findByWorkOrderId(Uuid $workOrderId): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }

    public function findByAssignmentId(Uuid $assignmentId): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }

    public function findByCustomerId(Uuid $customerId): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }
}
