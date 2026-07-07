<?php

namespace Src\Domain\ServiceCatalog;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;

interface ServiceRepositoryInterface extends RepositoryInterface
{
    public function findByServiceCatalogId(Uuid $serviceCatalogId): array;
    public function findAllActive(): array;
}
