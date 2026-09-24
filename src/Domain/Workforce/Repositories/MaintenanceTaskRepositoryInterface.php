<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\MaintenanceTask;

interface MaintenanceTaskRepositoryInterface {
    public function save(MaintenanceTask $task): MaintenanceTask;
    public function findById(Uuid $id): ?MaintenanceTask;
    public function findByWorkOrderId(Uuid $workOrderId): array;
    public function findByAssignmentId(Uuid $assignmentId): array;
    public function findByCustomerId(Uuid $customerId): array;
}
