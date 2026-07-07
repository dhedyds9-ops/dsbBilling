<?php

namespace Src\Domain\Workflow;

readonly class WorkflowStep
{
    public function __construct(
        public string $name,
        public string $action,
        public array $config = [],
        public int $order = 0
    ) {}
}
