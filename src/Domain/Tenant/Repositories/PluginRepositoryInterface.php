<?php

namespace Src\Domain\Tenant\Repositories;

use Src\Domain\Tenant\Aggregates\Plugin;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface PluginRepositoryInterface
{
    public function findById(Uuid $id): ?Plugin;

    public function findBySlug(string $slug): ?Plugin;

    public function save(Plugin $plugin): void;

    public function delete(Uuid $id): void;

    public function findByStatus(string $status): array;

    public function findByType(string $type): array;

    public function findInstalledByTenant(Uuid $tenantId): array;
}
