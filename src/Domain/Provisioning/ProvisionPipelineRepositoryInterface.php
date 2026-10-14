<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\Repositories\RepositoryInterface;

interface ProvisionPipelineRepositoryInterface extends RepositoryInterface
{
    public function findActive(): array;
    public function findByName(string $name): ?ProvisionPipeline;
}
