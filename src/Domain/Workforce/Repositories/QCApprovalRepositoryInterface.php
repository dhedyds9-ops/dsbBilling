<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCApproval;

interface QCApprovalRepositoryInterface {
    public function save(QCApproval $approval): QCApproval;
    public function findById(Uuid $id): ?QCApproval;
    public function findByInspectionId(Uuid $inspectionId): ?QCApproval;
}
