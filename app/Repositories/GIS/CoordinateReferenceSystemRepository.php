<?php

namespace App\Repositories\GIS;

use App\Models\GIS\CoordinateReferenceSystem as CRSModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\CoordinateReferenceSystem;
use Src\Domain\GIS\Repositories\CoordinateReferenceSystemRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoordinateReferenceSystemRepository extends BaseRepository implements CoordinateReferenceSystemRepositoryInterface
{
    public function __construct(CRSModel $model)
    {
        parent::__construct($model);
    }

    public function save(CoordinateReferenceSystem $entity): void
    {
        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'system' => $entity->system->value,
                'srs_name' => $entity->srsName,
                'parameters' => $entity->parameters,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?CoordinateReferenceSystem
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new CoordinateReferenceSystem(
            Uuid::fromString($model->uuid),
            $model->system,
            $model->srs_name,
            $model->parameters,
            $model->description,
            $model->metadata,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }

    public function findAll(): array
    {
        return $this->model->all()->map(function ($model) {
            return new CoordinateReferenceSystem(
                Uuid::fromString($model->uuid),
                $model->system,
                $model->srs_name,
                $model->parameters,
                $model->description,
                $model->metadata,
                $model->created_at?->toDateTimeImmutable(),
                $model->updated_at?->toDateTimeImmutable(),
            );
        })->all();
    }

    public function delete(Uuid $id): void
    {
        $this->model->where('uuid', $id->value)->delete();
    }
}
