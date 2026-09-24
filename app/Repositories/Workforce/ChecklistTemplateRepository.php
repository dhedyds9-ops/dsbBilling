<?php

namespace App\Repositories\Workforce;

use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ChecklistTemplate;
use Src\Domain\Workforce\Enums\TaskType;
use Src\Domain\Workforce\Repositories\ChecklistTemplateRepositoryInterface;

class ChecklistTemplateRepository extends BaseRepository implements ChecklistTemplateRepositoryInterface {
    public function save(ChecklistTemplate $template): ChecklistTemplate {
        // TODO: Implement Eloquent persistence
        return $template;
    }

    public function findById(Uuid $id): ?ChecklistTemplate {
        // TODO: Implement Eloquent retrieval
        return null;
    }

    public function findByTaskType(TaskType $taskType): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }

    public function findAllActive(): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }
}
