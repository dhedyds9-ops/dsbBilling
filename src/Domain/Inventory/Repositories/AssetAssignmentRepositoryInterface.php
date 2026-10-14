<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetAssignment;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetAssignmentRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetAssignment;
    public function findByAsset(Uuid $assetId): array;
    public function findByAssignedTo(Uuid $entityId): array;
    public function findActive(?Uuid $entityId = null): array;
    public function findReturned(): array;
    public function save(AssetAssignment $assignment): void;
    public function delete(AssetAssignment $assignment): void;
}
