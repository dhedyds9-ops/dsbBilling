<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\RMARequest;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface RMARequestRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?RMARequest;
    public function findByNumber(string $number): ?RMARequest;
    public function findByAsset(Uuid $assetId): array;
    public function findByCustomer(Uuid $customerId): array;
    public function findByVendor(Uuid $vendorId): array;
    public function findPending(): array;
    public function findByStatus(string $status): array;
    public function save(RMARequest $rma): void;
    public function delete(RMARequest $rma): void;
}
