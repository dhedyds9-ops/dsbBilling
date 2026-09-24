<?php

namespace Src\Domain\AI\Repositories;

use Src\Domain\AI\AIModel;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AIModelRepositoryInterface
{
    public function findById(Uuid $id): ?AIModel;

    public function save(AIModel $model): void;

    public function delete(Uuid $id): void;

    public function findByType(string $type): array;

    public function findByProvider(string $provider): array;

    public function findActiveByType(string $type): ?AIModel;

    public function findDefaultModel(string $type): ?AIModel;

    public function findByModule(string $module): array;
}
