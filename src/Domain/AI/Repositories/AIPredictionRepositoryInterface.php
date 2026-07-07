<?php

namespace Src\Domain\AI\Repositories;

use Src\Domain\AI\AIPrediction;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AIPredictionRepositoryInterface
{
    public function findById(Uuid $id): ?AIPrediction;

    public function save(AIPrediction $prediction): void;

    public function delete(Uuid $id): void;

    public function findByEntityId(string $entityId): array;

    public function findByType(string $type): array;

    public function findByStatus(string $status): array;

    public function findByModelId(Uuid $modelId): array;

    public function findByConfidenceAbove(float $threshold): array;

    public function findRecent(int $limit = 10): array;

    public function findHistorical(string $entityId, int $days = 30): array;
}
