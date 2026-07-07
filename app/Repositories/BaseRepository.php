<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->where('uuid', $uuid)->first($columns);
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function update(int $id, array $attributes): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->update($attributes);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function where(string $column, $value = null, string $operator = '='): \Illuminate\Database\Eloquent\Builder
    {
        if ($value === null) {
            return $this->model->whereNull($column);
        }
        return $this->model->where($column, $operator, $value);
    }

    public function whereIn(string $column, array $values): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->whereIn($column, $values);
    }
}
