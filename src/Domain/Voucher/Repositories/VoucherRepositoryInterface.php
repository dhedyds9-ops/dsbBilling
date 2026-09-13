<?php

namespace Src\Domain\Voucher\Repositories;

use App\Models\ISP\Voucher;
use Illuminate\Database\Eloquent\Builder;

interface VoucherRepositoryInterface
{
    public function query(): Builder;

    public function withTrashedQuery(): Builder;

    public function onlyTrashedQuery(): Builder;

    public function find(int $id, array $columns = ['*'], array $relations = []): ?Voucher;

    public function create(array $attributes): Voucher;

    public function existsByCode(string $code): bool;

    public function where(string $column, $value = null, string $operator = '='): Builder;
}
