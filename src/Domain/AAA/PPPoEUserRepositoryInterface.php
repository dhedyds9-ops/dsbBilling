<?php

namespace Src\Domain\AAA;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface PPPoEUserRepositoryInterface extends RepositoryInterface
{
    public function findByUsername(string $username): ?PPPoEUser;
    public function findByCustomerServiceId(Uuid $customerServiceId): ?PPPoEUser;
}
