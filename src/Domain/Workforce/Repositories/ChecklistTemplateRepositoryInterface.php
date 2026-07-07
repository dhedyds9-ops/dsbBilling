<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ChecklistTemplate;
use Src\Domain\Workforce\Enums\TaskType;

interface ChecklistTemplateRepositoryInterface {
    public function save(ChecklistTemplate $template): ChecklistTemplate;
    public function findById(Uuid $id): ?ChecklistTemplate;
    public function findByTaskType(TaskType $taskType): array;
    public function findAllActive(): array;
}
