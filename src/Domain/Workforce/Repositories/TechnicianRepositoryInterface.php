<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Technician;

interface TechnicianRepositoryInterface {
    public function save(Technician $technician): Technician;
    public function findById(Uuid $id): ?Technician;
    public function findByUserId(Uuid $userId): ?Technician;
    public function findAvailableTechnicians(): array;
}
