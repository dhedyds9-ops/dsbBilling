<?php

namespace Src\Domain\AI\Repositories;

use Src\Domain\AI\AIAnomaly;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AIAnomalyRepositoryInterface
{
    public function findById(Uuid $id): ?AIAnomaly;

    public function save(AIAnomaly $anomaly): void;

    public function delete(Uuid $id): void;

    public function findByEntityId(string $entityId): array;

    public function findByType(string $type): array;

    public function findBySeverity(string $severity): array;

    public function findByStatus(string $status): array;

    public function findUnacknowledged(): array;

    public function findByAcknowledgedBy(Uuid $userId): array;

    public function findRecent(int $limit = 10): array;

    public function findByTimeRange(\DateTime $start, \DateTime $end): array;
}
