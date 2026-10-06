<?php

namespace Src\Domain\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CompanySettingsUpdatedEvent
{
    use Dispatchable;

    public int $userId;
    public array $changedKeys;
    public string $updatedAt;

    public function __construct(int $userId, array $changedKeys, string $updatedAt)
    {
        $this->userId = $userId;
        $this->changedKeys = $changedKeys;
        $this->updatedAt = $updatedAt;
    }
}
