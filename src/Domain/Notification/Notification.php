<?php

namespace Src\Domain\Notification;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

enum NotificationType: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH = 'push';
    case IN_APP = 'in_app';
}

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case DELIVERED = 'delivered';
    case READ = 'read';
}

class Notification extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $recipientId,
        public string $title,
        public string $message,
        public NotificationType $type,
        public NotificationStatus $status = NotificationStatus::PENDING,
        public ?DateTimeImmutable $sentAt = null,
        public array $data = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $recipientId,
        string $title,
        string $message,
        NotificationType $type,
        array $data = []
    ): self {
        return new self($id, $recipientId, $title, $message, $type, NotificationStatus::PENDING, null, $data);
    }

    public function markAsSent(): void
    {
        $this->status = NotificationStatus::SENT;
        $this->sentAt = new DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = NotificationStatus::FAILED;
    }

    public function markAsDelivered(): void
    {
        $this->status = NotificationStatus::DELIVERED;
    }

    public function markAsRead(): void
    {
        $this->status = NotificationStatus::READ;
    }
}
