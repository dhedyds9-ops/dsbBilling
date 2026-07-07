<?php

namespace Src\Domain\AI\Repositories;

use Src\Domain\AI\AIConversation;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AIConversationRepositoryInterface
{
    public function findById(Uuid $id): ?AIConversation;

    public function save(AIConversation $conversation): void;

    public function delete(Uuid $id): void;

    public function findByUserId(Uuid $userId): array;

    public function findByModule(string $module): array;

    public function findByStatus(string $status): array;

    public function findActiveByUser(Uuid $userId): ?AIConversation;

    public function findRecent(int $limit = 10): array;

    public function findBySessionId(string $sessionId): ?AIConversation;
}
