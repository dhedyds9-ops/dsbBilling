<?php

namespace App\Repositories\Workforce;

use App\Repositories\BaseRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\DigitalSignature;
use Src\Domain\Workforce\Repositories\DigitalSignatureRepositoryInterface;

class DigitalSignatureRepository extends BaseRepository implements DigitalSignatureRepositoryInterface {
    public function save(DigitalSignature $signature): DigitalSignature {
        // TODO: Implement Eloquent persistence
        return $signature;
    }

    public function findById(Uuid $id): ?DigitalSignature {
        // TODO: Implement Eloquent retrieval
        return null;
    }

    public function findByTaskId(Uuid $taskId): array {
        // TODO: Implement Eloquent retrieval
        return [];
    }
}
