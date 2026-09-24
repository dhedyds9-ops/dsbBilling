<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface ServiceInstanceRepositoryInterface extends RepositoryInterface
{
    public function findByCustomerServiceId(Uuid $customerServiceId): ?ServiceInstance;
    public function findByStatus(ServiceInstanceStatus $status): array;
    public function findAllActive(): array;
}
