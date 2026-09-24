<?php

namespace App\Services\Monitoring;

use App\Services\Monitoring\Contracts\CollectorInterface;
use Illuminate\Support\Facades\Cache;

abstract class BaseCollector implements CollectorInterface
{
    protected array $data = [];
    
    protected function getCacheKey(string $type, string $id): string
    {
        return "monitoring:{$type}:{$id}";
    }
    
    protected function putToCache(string $key, array $data, int $ttlInSeconds = 300): void
    {
        Cache::put($key, $data, now()->addSeconds($ttlInSeconds));
    }
    
    protected function fireEvent(string $eventName, array $payload = []): void
    {
        // Will implement later with proper event classes
    }
    
    abstract public function collect(): void;
    
    abstract public function getName(): string;
}
