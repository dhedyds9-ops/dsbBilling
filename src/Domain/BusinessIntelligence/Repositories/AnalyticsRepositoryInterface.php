<?php

namespace Src\Domain\BusinessIntelligence\Repositories;

use Src\Domain\BusinessIntelligence\Analytics;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AnalyticsRepositoryInterface
{
    public function findById(Uuid $id): ?Analytics;

    public function save(Analytics $analytics): void;

    public function delete(Uuid $id): void;

    public function findByModule(string $module): array;

    public function findByType(string $type): array;

    public function findByCreator(Uuid $creatorId): array;

    public function findRecent(int $limit = 10): array;
}
