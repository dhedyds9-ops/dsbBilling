<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\InstallationTask;

interface InstallationTaskRepositoryInterface {
    public function save(InstallationTask $task): InstallationTask;
    public function findById(Uuid $id): ?InstallationTask;
    public function findByWorkOrderId(Uuid $workOrderId): ?InstallationTask;
    public function findByAssignmentId(Uuid $assignmentId): ?InstallationTask;
    public function findByCustomerId(Uuid $customerId): array;
}
