<?php

namespace Src\Domain\SharedKernel\Repositories;

use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface RepositoryInterface
{
    public function save(AggregateRoot $aggregate): void;
    public function findById(Uuid $id): ?AggregateRoot;
    public function delete(AggregateRoot $aggregate): void;
}
