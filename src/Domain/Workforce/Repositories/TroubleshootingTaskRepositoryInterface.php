<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\TroubleshootingTask;

interface TroubleshootingTaskRepositoryInterface {
    public function save(TroubleshootingTask $task): TroubleshootingTask;
    public function findById(Uuid $id): ?TroubleshootingTask;
    public function findByWorkOrderId(Uuid $workOrderId): array;
    public function findByAssignmentId(Uuid $assignmentId): array;
    public function findByCustomerId(Uuid $customerId): array;
    public function findByTicketId(Uuid $ticketId): array;
}
