<?php

namespace Src\Domain\Pelanggan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserReactivatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $userType,
        public readonly ?int $customerId,
        public readonly int $reactivatedBy,
        public readonly ?\DateTimeInterface $reactivatedAt = null,
    ) {}
}
