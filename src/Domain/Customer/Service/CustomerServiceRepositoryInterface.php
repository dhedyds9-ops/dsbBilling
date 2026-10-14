<?php

namespace Src\Domain\Customer\Service;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;

interface CustomerServiceRepositoryInterface extends RepositoryInterface
{
    public function findByCustomerId(Uuid $customerId): array;
    public function findByContractId(Uuid $contractId): array;
    public function findByUsername(string $username): ?CustomerService;
    public function findAllActive(): array;
}
