<?php

namespace App\Repositories\Workforce;

use App\Models\Workforce\QCChecklist as QCChecklistModel;
use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\QCChecklist;
use Src\Domain\Workforce\Repositories\QCChecklistRepositoryInterface;

class QCChecklistRepository extends BaseRepository implements QCChecklistRepositoryInterface
{
    public function __construct(QCChecklistModel $model)
    {
        parent::__construct($model);
    }

    public function save(QCChecklist $checklist): QCChecklist
    {
        $this->model->updateOrCreate(
            ['uuid' => $checklist->id->value],
            [
                'inspection_id' => $checklist->inspectionId->value,
                'item' => $checklist->item,
                'is_required' => $checklist->isRequired,
            ]
        );

        return $checklist;
    }

    public function findById(Uuid $id): ?QCChecklist
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new QCChecklist(
            Uuid::fromString($model->uuid),
            Uuid::fromString($model->inspection_id),
            $model->item,
            $model->is_required,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable()
        );
    }

    public function findByInspectionId(Uuid $inspectionId): array
    {
        $models = $this->model->where('inspection_id', $inspectionId->value)
            ->orderBy('created_at')
            ->get();

        return $models->map(function ($model) {
            return new QCChecklist(
                Uuid::fromString($model->uuid),
                Uuid::fromString($model->inspection_id),
                $model->item,
                $model->is_required,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable()
            );
        })->all();
    }

    public function delete(Uuid $id): void
    {
        $this->model->where('uuid', $id->value)->delete();
    }
}
