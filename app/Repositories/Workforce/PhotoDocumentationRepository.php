<?php

namespace App\Repositories\Workforce;

use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\PhotoDocumentation;
use Src\Domain\Workforce\Repositories\PhotoDocumentationRepositoryInterface;

class PhotoDocumentationRepository extends BaseRepository implements PhotoDocumentationRepositoryInterface {
    public function save(PhotoDocumentation $photo): PhotoDocumentation {
        // TODO: Implement Eloquent persistence
        return $photo;
    }

    public function findById(Uuid $id): ?PhotoDocumentation {
        // TODO: Implement Eloquent retrieval
        return null;
    }

    public function findByTaskId(Uuid $taskId): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }

    public function delete(Uuid $id): void {
        // TODO: Implement Eloquent deletion
    }
}
