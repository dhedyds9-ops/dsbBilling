<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCInspection;

interface QCInspectionRepositoryInterface {
    public function save(QCInspection $inspection): QCInspection;
    public function findById(Uuid $id): ?QCInspection;
    public function findByTaskId(Uuid $taskId): ?QCInspection;
    public function findByInspectorId(Uuid $inspectorId): array;
}
