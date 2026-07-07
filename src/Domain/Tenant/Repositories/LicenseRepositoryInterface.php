<?php

namespace Src\Domain\Tenant\Repositories;

use Src\Domain\Tenant\Aggregates\License;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface LicenseRepositoryInterface
{
    public function findById(Uuid $id): ?License;

    public function findByTenantId(Uuid $tenantId): ?License;

    public function save(License $license): void;

    public function delete(Uuid $id): void;

    public function findActiveLicenses(): array;

    public function findExpiringLicenses(int $days = 7): array;

    public function findExpiredLicenses(): array;
}
