<?php

namespace Src\Domain\AI;

use Src\Domain\AI\Enums\AIProvider;
use Src\Domain\AI\Enums\ConversationStatus;
use Src\Domain\AI\ValueObjects\AIMessage;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use DateTimeImmutable;

class AIConversation extends AggregateRoot
{
    private array $messages = [];
    private array $context = [];
    private ?Uuid $modelId = null;
    private ?Uuid $userId = null;
    private ?string $entityType = null;
    private ?string $entityId = null;

    public function __construct(
        public readonly Uuid $id,
        public readonly string $name,
        public readonly string $module, // NOC, CRM, Billing, CustomerService
        public readonly AIProvider $provider,
        public readonly ConversationStatus $status,
        public readonly ?Uuid $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        string $name,
        string $module,
        AIProvider $provider,
        ?Uuid $createdBy = null
    ): self {
        $id = Uuid::generate();
        return new self(
            id: $id,
            name: $name,
            module: $module,
            provider: $provider,
            status: ConversationStatus::ACTIVE,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function addMessage(AIMessage $message): void
    {
        $this->messages[] = $message->toArray();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addUserMessage(string $content, ?array $metadata = null): void
    {
        $this->addMessage(AIMessage::user($content, $metadata));
    }

    public function addAssistantMessage(string $content, ?array $metadata = null, ?float $confidence = null): void
    {
        $this->addMessage(AIMessage::assistant($content, $metadata, $confidence));
    }

    public function addSystemMessage(string $content, ?array $metadata = null): void
    {
        $this->addMessage(AIMessage::system($content, $metadata));
    }

    public function setModel(Uuid $modelId): void
    {
        $this->modelId = $modelId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setUser(Uuid $userId): void
    {
        $this->userId = $userId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function linkEntity(string $entityType, string $entityId): void
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->status = ConversationStatus::COMPLETED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function abandon(): void
    {
        $this->status = ConversationStatus::ABANDONED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function escalate(): void
    {
        $this->status = ConversationStatus::ESCALATED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getLastMessage(): ?array
    {
        return end($this->messages) ?: null;
    }

    public function getMessageCount(): int
    {
        return count($this->messages);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getSummary(): string
    {
        $userMessages = array_filter($this->messages, fn($m) => $m['role'] === 'user');
        $lastUserMessage = end($userMessages);
        return $lastUserMessage['content'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'module' => $this->module,
            'provider' => $this->provider->value,
            'status' => $this->status->value,
            'model_id' => $this->modelId?->toString(),
            'user_id' => $this->userId?->toString(),
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'messages' => $this->messages,
            'message_count' => $this->getMessageCount(),
            'context' => $this->context,
            'created_by' => $this->createdBy?->toString(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
