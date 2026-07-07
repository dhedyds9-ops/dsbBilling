<?php

namespace Src\Domain\Customer\Contract;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;

interface ContractRepositoryInterface extends RepositoryInterface
{
    public function findByCustomerId(Uuid $customerId): array;
    public function findByContractNumber(string $contractNumber): ?Contract;
    public function findAllActive(): array;
}
