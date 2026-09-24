<?php

namespace App\Repositories\GIS;

use App\Models\GIS\MapLayer as MapLayerModel;
use App\Repositories\BaseRepository;
use Src\Domain\GIS\MapLayer;
use Src\Domain\GIS\Repositories\MapLayerRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class MapLayerRepository extends BaseRepository implements MapLayerRepositoryInterface
{
    public function __construct(MapLayerModel $model)
    {
        parent::__construct($model);
    }

    public function save(MapLayer $entity): void
    {
        $this->model->updateOrCreate(
            ['uuid' => $entity->id->value],
            [
                'name' => $entity->name,
                'type' => $entity->type->value,
                'source_url' => $entity->sourceUrl,
                'layer_options' => $entity->layerOptions,
                'is_visible' => $entity->isVisible,
                'order_index' => $entity->orderIndex,
                'coordinate_system' => $entity->coordinateSystem->value,
                'description' => $entity->description,
                'metadata' => $entity->metadata,
            ]
        );
    }

    public function findById(Uuid $id): ?MapLayer
    {
        $model = $this->model->where('uuid', $id->value)->first();
        if (!$model) {
            return null;
        }

        return new MapLayer(
            Uuid::fromString($model->uuid),
            $model->name,
            $model->type,
            $model->source_url,
            $model->layer_options,
            $model->is_visible,
            $model->order_index,
            $model->coordinate_system,
            $model->description,
            $model->metadata,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }

    public function findAll(): array
    {
        return $this->model->orderBy('order_index')->get()->map(function ($model) {
            return new MapLayer(
                Uuid::fromString($model->uuid),
                $model->name,
                $model->type,
                $model->source_url,
                $model->layer_options,
                $model->is_visible,
                $model->order_index,
                $model->coordinate_system,
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
