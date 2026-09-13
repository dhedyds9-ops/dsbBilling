<?php

namespace App\Repositories\Voucher;

use App\Models\ISP\Voucher;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Voucher\Repositories\VoucherRepositoryInterface;

class EloquentVoucherRepository extends BaseRepository implements VoucherRepositoryInterface
{
    public function __construct(Voucher $model)
    {
        parent::__construct($model);
    }

    public function find(int $id, array $columns = ['*'], array $relations = []): ?Voucher
    {
        return parent::find($id, $columns, $relations);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function withTrashedQuery(): Builder
    {
        return $this->model->newQuery()->withTrashed();
    }

    public function onlyTrashedQuery(): Builder
    {
        return $this->model->newQuery()->onlyTrashed();
    }

    public function create(array $attributes): Voucher
    {
        /** @var Voucher $voucher */
        $voucher = parent::create($attributes);

        return $voucher;
    }

    public function existsByCode(string $code): bool
    {
        return $this->model->newQuery()->where('code', $code)->exists();
    }
}
