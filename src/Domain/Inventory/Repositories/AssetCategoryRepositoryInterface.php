<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\AssetCategory;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AssetCategoryRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?AssetCategory;
    public function findByCode(string $code): ?AssetCategory;
    public function findRootCategories(): array;
    public function findByParent(Uuid $parentId): array;
    public function findByType(string $type): array;
    public function save(AssetCategory $category): void;
    public function delete(AssetCategory $category): void;
}
