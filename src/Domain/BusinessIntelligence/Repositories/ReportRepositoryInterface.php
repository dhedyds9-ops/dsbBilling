<?php

namespace Src\Domain\BusinessIntelligence\Repositories;

use Src\Domain\BusinessIntelligence\Report;
use Src\Domain\BusinessIntelligence\Enums\ReportStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface ReportRepositoryInterface
{
    public function findById(Uuid $id): ?Report;

    public function findByName(string $name): ?Report;

    public function save(Report $report): void;

    public function delete(Uuid $id): void;

    public function findByStatus(ReportStatus $status): array;

    public function findByModule(string $module): array;

    public function findByCreator(Uuid $creatorId): array;

    public function findScheduledReports(): array;

    public function findRecentReports(int $limit = 10): array;
}
