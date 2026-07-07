<?php

namespace Src\Domain\Tenant\Repositories;

use Src\Domain\Tenant\Aggregates\Tenant;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface TenantRepositoryInterface
{
    public function findById(Uuid $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;

    public function save(Tenant $tenant): void;

    public function delete(Uuid $id): void;

    public function findActive(): array;

    public function findByStatus(string $status): array;

    public function findByTier(string $tier): array;
}
