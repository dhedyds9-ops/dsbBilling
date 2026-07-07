<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCChecklist;

interface QCChecklistRepositoryInterface {
    public function save(QCChecklist $checklist): QCChecklist;
    public function findById(Uuid $id): ?QCChecklist;
    public function findByInspectionId(Uuid $inspectionId): array;
    public function delete(Uuid $id): void;
}
