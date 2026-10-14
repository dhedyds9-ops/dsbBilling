<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\GoodsIssue;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface GoodsIssueRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?GoodsIssue;
    public function findByNumber(string $number): ?GoodsIssue;
    public function findByWarehouse(Uuid $warehouseId): array;
    public function findByIssuedTo(Uuid $entityId): array;
    public function findByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function save(GoodsIssue $issue): void;
    public function delete(GoodsIssue $issue): void;
}
