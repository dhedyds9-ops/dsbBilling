<?php

namespace Src\Domain\ProductCatalog;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findByType(ProductType $type): array;
    public function findAllActive(): array;
}
