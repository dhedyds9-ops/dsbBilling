<?php

namespace App\Services\Provisioning\Pipeline;

use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Customer\CustomerService;

class PipelineContext
{
    public function __construct(
        public readonly ProvisionPipeline $pipeline,
        public readonly CustomerService $customerService,
        public readonly array $metadata = []
    ) {}

    public function getParameter(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }
}
