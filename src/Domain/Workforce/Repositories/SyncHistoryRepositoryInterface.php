<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncHistory;

interface SyncHistoryRepositoryInterface {
    public function save(SyncHistory $history): SyncHistory;
    public function findById(Uuid $id): ?SyncHistory;
    public function findByUserId(Uuid $userId, ?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): array;
    public function findLatestByUserId(Uuid $userId): ?SyncHistory;
}
