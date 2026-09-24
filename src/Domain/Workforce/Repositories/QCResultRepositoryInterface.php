<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCResult;

interface QCResultRepositoryInterface {
    public function save(QCResult $result): QCResult;
    public function findById(Uuid $id): ?QCResult;
    public function findByChecklistId(Uuid $checklistId): ?QCResult;
    public function delete(Uuid $id): void;
}
