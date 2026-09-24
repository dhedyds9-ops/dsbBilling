<?php

namespace App\Services\Monitoring;

use App\Models\Alarm;
use Illuminate\Support\Str;

class AlertEngine
{
    public function createAlert(
        string $level,
        $source,
        string $title,
        ?string $description = null,
        array $metadata = []
    ): Alarm {
        $uuid = (string) Str::uuid();
        
        return Alarm::firstOrCreate([
            'source_type' => get_class($source),
            'source_id' => $source->id,
            'title' => $title,
            'status' => 'open',
        ], [
            'uuid' => $uuid,
            'level' => $level,
            'source_name' => $source->name ?? $source->user ?? null,
            'description' => $description,
            'metadata' => $metadata,
            'started_at' => now(),
        ]);
    }
    
    public function resolveAlert(Alarm $alarm): void
    {
        $alarm->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }
    
    public function acknowledgeAlert(Alarm $alarm, int $userId, ?string $note = null): void
    {
        $alarm->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $userId,
            'acknowledged_note' => $note,
            'acknowledged_at' => now(),
        ]);
    }
}
