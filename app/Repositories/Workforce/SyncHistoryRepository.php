<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\SyncHistory as SyncHistoryModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncHistory;
use Src\Domain\Workforce\Enums\SyncTaskType;
use Src\Domain\Workforce\Repositories\SyncHistoryRepositoryInterface;

class SyncHistoryRepository extends BaseRepository implements SyncHistoryRepositoryInterface
{
    public function __construct(SyncHistoryModel $model)
    {
        parent::__construct($model);
    }

    public function save(SyncHistory $history): SyncHistory
    {
        $this->model->updateOrCreate(
            ['uuid' => $history->id->value],
            [
                'user_id' => $history->userId->value,
                'type' => $history->type,
                'items_synced' => $history->itemsSynced,
                'items_failed' => $history->itemsFailed,
                'started_at' => $history->startedAt,
                'completed_at' => $history->completedAt,
            ]
        );

        return $history;
    }

    public function findById(Uuid $id): ?SyncHistory
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new SyncHistory(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->user_id),
            $model->type,
            $model->items_synced,
            $model->items_failed,
            $model->started_at?->toDateTimeImmutable(),
            $model->completed_at?->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable()
        );
    }

    public function findByUserId(Uuid $userId, ?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): array
    {
        $query = $this->model->where('user_id', $userId->value);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $models = $query->orderBy('created_at')->get();

        return $models->map(function ($model) {
            return new SyncHistory(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->user_id),
                $model->type,
                $model->items_synced,
                $model->items_failed,
                $model->started_at?->toDateTimeImmutable(),
                $model->completed_at?->toDateTimeImmutable(),
                $model->created_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function findLatestByUserId(Uuid $userId): ?SyncHistory
    {
        $model = $this->model->where('user_id', $userId->value)->latest('created_at')->first();
        if (!$model) {
            return null;
        }

        return new SyncHistory(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->user_id),
            $model->type,
            $model->items_synced,
            $model->items_failed,
            $model->started_at?->toDateTimeImmutable(),
            $model->completed_at?->toDateTimeImmutable(),
            $model->created_at?->toDateTimeImmutable()
        );
    }
}
