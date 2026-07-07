<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncQueue;

interface SyncQueueRepositoryInterface {
    public function save(SyncQueue $queue): SyncQueue;
    public function findById(Uuid $id): ?SyncQueue;
    public function findByUserId(Uuid $userId): ?SyncQueue;
    public function delete(Uuid $id): void;
}
