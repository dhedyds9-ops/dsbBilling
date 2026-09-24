<?php

namespace Src\Domain\BusinessIntelligence\Repositories;

use Src\Domain\BusinessIntelligence\Forecast;
use Src\Domain\BusinessIntelligence\Enums\ForecastModel;
use Src\Domain\BusinessIntelligence\Enums\DataGranularity;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface ForecastRepositoryInterface
{
    public function findById(Uuid $id): ?Forecast;

    public function save(Forecast $forecast): void;

    public function delete(Uuid $id): void;

    public function findByMetricName(string $metricName): array;

    public function findByModel(ForecastModel $model): array;

    public function findByModule(string $module): array;

    public function findByCreator(Uuid $creatorId): array;

    public function findLatestForecasts(int $limit = 10): array;

    public function findExpiredForecasts(): array;
}
