<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncTask;

interface SyncTaskRepositoryInterface {
    public function save(SyncTask $task): SyncTask;
    public function findById(Uuid $id): ?SyncTask;
    public function findByUserId(Uuid $userId): array;
    public function findPendingByUserId(Uuid $userId): array;
    public function findFailedByUserId(Uuid $userId): array;
    public function delete(Uuid $id): void;
}
