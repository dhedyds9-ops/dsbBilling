<?php

namespace App\Repositories\Workforce;

use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\InstallationTask;
use Src\Domain\Workforce\Repositories\InstallationTaskRepositoryInterface;

class InstallationTaskRepository extends BaseRepository implements InstallationTaskRepositoryInterface {
    public function save(InstallationTask $task): InstallationTask {
        // TODO: Implement Eloquent persistence
        return $task;
    }

    public function findById(Uuid $id): ?InstallationTask {
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
