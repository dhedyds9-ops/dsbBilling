<?php

namespace App\Repositories\Workforce;

use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\TroubleshootingTask;
use Src\Domain\Workforce\Repositories\TroubleshootingTaskRepositoryInterface;

class TroubleshootingTaskRepository extends BaseRepository implements TroubleshootingTaskRepositoryInterface {
    public function save(TroubleshootingTask $task): TroubleshootingTask {
        // TODO: Implement Eloquent persistence
        return $task;
    }

    public function findById(Uuid $id): ?TroubleshootingTask {
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

    public function findByTicketId(Uuid $ticketId): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }
}
