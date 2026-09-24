<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\WorkAssignment;

interface WorkAssignmentRepositoryInterface {
    public function save(WorkAssignment $assignment): WorkAssignment;
    public function findById(Uuid $id): ?WorkAssignment;
    public function findByWorkOrderId(Uuid $workOrderId): ?WorkAssignment;
    public function findByTechnicianId(Uuid $technicianId): array;
}
