<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\PhotoDocumentation;

interface PhotoDocumentationRepositoryInterface {
    public function save(PhotoDocumentation $photo): PhotoDocumentation;
    public function findById(Uuid $id): ?PhotoDocumentation;
    public function findByTaskId(Uuid $taskId): array;
    public function delete(Uuid $id): void;
}
