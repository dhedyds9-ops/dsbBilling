<?php

namespace Src\Domain\Tenant\Repositories;

use Src\Domain\Tenant\Aggregates\TenantUser;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface TenantUserRepositoryInterface
{
    public function findById(Uuid $id): ?TenantUser;

    public function findByUserId(Uuid $tenantId, Uuid $userId): ?TenantUser;

    public function save(TenantUser $tenantUser): void;

    public function delete(Uuid $id): void;

    public function findByTenantId(Uuid $tenantId): array;

    public function findOwners(Uuid $tenantId): array;

    public function findByRole(Uuid $tenantId, string $roleId): array;
}
