<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\SyncQueue as SyncQueueModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\SyncQueue;
use Src\Domain\Workforce\Repositories\SyncQueueRepositoryInterface;

class SyncQueueRepository extends BaseRepository implements SyncQueueRepositoryInterface
{
    public function __construct(SyncQueueModel $model)
    {
        parent::__construct($model);
    }

    public function save(SyncQueue $queue): SyncQueue
    {
        $this->model->updateOrCreate(
            ['uuid' => $queue->id->value],
            [
                'user_id' => $queue->userId->value,
                'task_ids' => array_map(fn($id) => $id->value, $queue->taskIds),
                'last_synced_at' => $queue->lastSyncedAt,
                'pending_count' => $queue->pendingCount,
                'is_online' => $queue->isOnline,
            ]
        );

        return $queue;
    }

    public function findById(Uuid $id): ?SyncQueue
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        $taskIds = array_map(fn($id) => Uuid::fromString($id), $model->task_ids ?? []);

        return new SyncQueue(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->user_id),
            $taskIds,
            $model->last_synced_at?->toDateTimeImmutable(),
            $model->pending_count,
            $model->is_online,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByUserId(Uuid $userId): ?SyncQueue
    {
        $model = $this->model->where('user_id', $userId->value)->first();
        if (!$model) {
            return null;
        }

        $taskIds = array_map(fn($id) => Uuid::fromString($id), $model->task_ids ?? []);

        return new SyncQueue(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->user_id),
            $taskIds,
            $model->last_synced_at?->toDateTimeImmutable(),
            $model->pending_count,
            $model->is_online,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function delete(Uuid $id): void
    {
        $this->model->where('uuid', $id->value)->delete();
    }
}
