<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncConflict;

interface SyncConflictRepositoryInterface {
    public function save(SyncConflict $conflict): SyncConflict;
    public function findById(Uuid $id): ?SyncConflict;
    public function findByUserId(Uuid $userId): array;
    public function findUnresolvedByUserId(Uuid $userId): array;
    public function delete(Uuid $id): void;
}
