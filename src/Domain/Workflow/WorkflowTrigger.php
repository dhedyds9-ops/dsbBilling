<?php

namespace Src\Domain\Workflow;

readonly class WorkflowTrigger
{
    public function __construct(
        public string $name,
        public string $event,
        public array $conditions = []
    ) {}
}
