<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\SerialNumberTracker;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface SerialNumberRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?SerialNumberTracker;
    public function findBySerialNumber(string $serialNumber): ?SerialNumberTracker;
    public function findByAsset(Uuid $assetId): array;
    public function findByProduct(Uuid $productId): array;
    public function search(string $query): array;
    public function save(SerialNumberTracker $serial): void;
    public function delete(SerialNumberTracker $serial): void;
}
