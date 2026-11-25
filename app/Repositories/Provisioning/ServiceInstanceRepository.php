<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\ServiceInstance as EloquentServiceInstance;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Provisioning\ServiceInstance as DomainServiceInstance;
use Src\Domain\Provisioning\ServiceInstanceRepositoryInterface;
use Src\Domain\Provisioning\ServiceInstanceStatus;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ServiceInstanceRepository implements ServiceInstanceRepositoryInterface
{
    protected EloquentServiceInstance $model;

    public function __construct(?EloquentServiceInstance $model = null)
    {
        $this->model = $model ?? new EloquentServiceInstance();
    }

    // =========================================================================
    // Domain RepositoryInterface & ServiceInstanceRepositoryInterface Implementation
    // =========================================================================

    public function save(AggregateRoot $aggregate): void
    {
        if (!$aggregate instanceof DomainServiceInstance) {
            throw new \InvalidArgumentException('Aggregate must be an instance of ' . DomainServiceInstance::class);
        }

        $eloquent = $this->model->newQuery()->where('uuid', $aggregate->id->value)->first();
        if ($eloquent) {
            $eloquent->update([
                'status' => $aggregate->status->value,
                'provisioned_at' => $aggregate->provisionedAt,
                'activated_at' => $aggregate->activatedAt,
            ]);
        } else {
            $this->model->newQuery()->create([
                'uuid' => $aggregate->id->value,
                'status' => $aggregate->status->value,
                'provisioned_at' => $aggregate->provisionedAt,
                'activated_at' => $aggregate->activatedAt,
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
        if ($aggregate instanceof DomainServiceInstance) {
            $this->model->newQuery()->where('uuid', $aggregate->id->value)->delete();
        }
    }

    public function findByCustomerServiceId(Uuid $customerServiceId): ?DomainServiceInstance
    {
        $eloquent = $this->model->newQuery()
            ->whereHas('customerService', function ($q) use ($customerServiceId) {
                $q->where('uuid', $customerServiceId->value);
            })
            ->first();

        if (!$eloquent) {
            // Also check if customer_service_id matches numeric or direct id
            $eloquent = $this->model->newQuery()
                ->where('customer_service_id', $customerServiceId->value)
                ->first();
        }

        return $eloquent ? $this->toDomainEntity($eloquent) : null;
    }

    public function findByStatus(ServiceInstanceStatus $status): array
    {
        $items = $this->model->newQuery()
            ->where('status', $status->value)
            ->get();

        return $items->map(fn($item) => $this->toDomainEntity($item))->all();
    }

    public function findAllActive(): array
    {
        return $this->findByStatus(ServiceInstanceStatus::ACTIVE);
    }

    protected function toDomainEntity(EloquentServiceInstance $eloquent): DomainServiceInstance
    {
        $status = ServiceInstanceStatus::tryFrom($eloquent->status) ?? ServiceInstanceStatus::PENDING;
        $pipelineUuid = $eloquent->provisionPipeline?->uuid ?? (string) \Illuminate\Support\Str::uuid();
        $csUuid = $eloquent->customerService?->uuid ?? (string) \Illuminate\Support\Str::uuid();

        return new DomainServiceInstance(
            id: Uuid::fromString($eloquent->uuid),
            customerServiceId: Uuid::fromString($csUuid),
            provisionPipelineId: Uuid::fromString($pipelineUuid),
            status: $status,
            provisionedAt: $eloquent->provisioned_at ? DateTimeImmutable::createFromMutable($eloquent->provisioned_at) : null,
            activatedAt: $eloquent->activated_at ? DateTimeImmutable::createFromMutable($eloquent->activated_at) : null,
        );
    }

    // =========================================================================
    // Eloquent Compatibility Methods (BaseRepository Parity)
    // =========================================================================

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->newQuery()->with($relations)->get($columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = []): ?EloquentServiceInstance
    {
        return $this->model->newQuery()->with($relations)->find($id, $columns);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): ?EloquentServiceInstance
    {
        return $this->model->newQuery()->with($relations)->where('uuid', $uuid)->first($columns);
    }

    public function create(array $attributes): EloquentServiceInstance
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
