<?php

namespace Src\Domain\ServiceCatalog;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;

interface ServiceCatalogRepositoryInterface extends RepositoryInterface
{
    public function findByName(string $name): ?ServiceCatalog;
    public function findAllActive(): array;
}
