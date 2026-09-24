<?php

namespace Src\Domain\Tenant\Repositories;

use Src\Domain\Tenant\Aggregates\TenantRole;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface TenantRoleRepositoryInterface
{
    public function findById(Uuid $id): ?TenantRole;

    public function findBySlug(Uuid $tenantId, string $slug): ?TenantRole;

    public function save(TenantRole $role): void;

    public function delete(Uuid $id): void;

    public function findByTenantId(Uuid $tenantId): array;

    public function findSystemRoles(Uuid $tenantId): array;
}
