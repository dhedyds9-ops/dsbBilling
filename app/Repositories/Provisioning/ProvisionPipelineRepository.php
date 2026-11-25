<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\ProvisionPipeline as EloquentProvisionPipeline;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Provisioning\PipelineStatus;
use Src\Domain\Provisioning\PipelineStep;
use Src\Domain\Provisioning\ProvisionPipeline as DomainProvisionPipeline;
use Src\Domain\Provisioning\ProvisionPipelineRepositoryInterface;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ProvisionPipelineRepository implements ProvisionPipelineRepositoryInterface
{
    protected EloquentProvisionPipeline $model;

    public function __construct(?EloquentProvisionPipeline $model = null)
    {
        $this->model = $model ?? new EloquentProvisionPipeline();
    }

    // =========================================================================
    // Domain RepositoryInterface & ProvisionPipelineRepositoryInterface
    // =========================================================================

    public function save(AggregateRoot $aggregate): void
    {
        if (!$aggregate instanceof DomainProvisionPipeline) {
            throw new \InvalidArgumentException('Aggregate must be an instance of ' . DomainProvisionPipeline::class);
        }

        $eloquent = $this->model->newQuery()->where('uuid', $aggregate->id->value)->first();
        if ($eloquent) {
            $eloquent->update([
                'status' => $aggregate->status->value,
            ]);
        } else {
            $this->model->newQuery()->create([
                'uuid' => $aggregate->id->value,
                'status' => $aggregate->status->value,
            ]);
        }
    }

    public function findById(Uuid $id): ?AggregateRoot
    {
        $eloquent = $this->model->newQuery()->where('uuid', $id->value)->first();
        if (!$eloquent) {
            return null;
        }

        return $this->toDomainEntity($eloquent);
    }

    public function delete(AggregateRoot $aggregate): void
    {
        if ($aggregate instanceof DomainProvisionPipeline) {
            $this->model->newQuery()->where('uuid', $aggregate->id->value)->delete();
        }
    }

    public function findActive(): array
    {
        $items = $this->model->newQuery()
            ->whereIn('status', ['active', 'pending'])
            ->get();

        return $items->map(fn($item) => $this->toDomainEntity($item))->all();
    }

    public function findByName(string $name): ?DomainProvisionPipeline
    {
        $eloquent = $this->model->newQuery()
            ->where('notes', 'like', "%{$name}%")
            ->orWhere('uuid', $name)
            ->first();

        return $eloquent ? $this->toDomainEntity($eloquent) : null;
    }

    protected function toDomainEntity(EloquentProvisionPipeline $eloquent): DomainProvisionPipeline
    {
        $steps = [];
        if ($eloquent->relationLoaded('steps') || $eloquent->steps()->exists()) {
            foreach ($eloquent->steps as $s) {
                $stepEnum = PipelineStep::tryFrom($s->step_type ?? $s->step_name);
                if ($stepEnum) {
                    $steps[] = [
                        'step' => $stepEnum,
                        'order' => $s->order ?? 0,
                    ];
                }
            }
        }

        return new DomainProvisionPipeline(
            id: Uuid::fromString($eloquent->uuid ?? (string) \Illuminate\Support\Str::uuid()),
            name: $eloquent->uuid ?? 'Default Pipeline',
            steps: $steps,
            status: PipelineStatus::tryFrom($eloquent->status) ?? PipelineStatus::ACTIVE,
            description: $eloquent->notes,
            createdAt: $eloquent->created_at ? DateTimeImmutable::createFromMutable($eloquent->created_at) : new DateTimeImmutable(),
            updatedAt: $eloquent->updated_at ? DateTimeImmutable::createFromMutable($eloquent->updated_at) : null,
        );
    }

    // =========================================================================
    // Eloquent Compatibility Methods (BaseRepository Parity)
    // =========================================================================

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->newQuery()->with($relations)->get($columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = []): ?EloquentProvisionPipeline
    {
        return $this->model->newQuery()->with($relations)->find($id, $columns);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): ?EloquentProvisionPipeline
    {
        return $this->model->newQuery()->with($relations)->where('uuid', $uuid)->first($columns);
    }

    public function create(array $attributes): EloquentProvisionPipeline
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(int $id, array $attributes): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->update($attributes);
    }

    public function deleteById(int $id): bool
    {
        return $this->model->newQuery()->where('id', $id)->delete() > 0;
    }

    public function where(string $column, $value = null, string $operator = '='): Builder
    {
        if ($value === null) {
            return $this->model->newQuery()->whereNull($column);
        }
        return $this->model->newQuery()->where($column, $operator, $value);
    }

    public function whereIn(string $column, array $values): Builder
    {
        return $this->model->newQuery()->whereIn($column, $values);
    }
}
