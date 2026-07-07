<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\DigitalSignature;

interface DigitalSignatureRepositoryInterface {
    public function save(DigitalSignature $signature): DigitalSignature;
    public function findById(Uuid $id): ?DigitalSignature;
    public function findByTaskId(Uuid $taskId): array;
}
