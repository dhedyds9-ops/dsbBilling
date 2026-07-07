<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'source_type',
        'parameter',
        'operator',
        'threshold',
        'level',
        'is_active',
        'cooldown',
        'notification_channels',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cooldown' => 'integer',
        'notification_channels' => 'array',
    ];

    public function matches($value)
    {
        $value = (float) $value;
        $threshold = (float) $this->threshold;

        return match ($this->operator) {
            '>' => $value > $threshold,
            '>=' => $value >= $threshold,
            '<' => $value < $threshold,
            '<=' => $value <= $threshold,
            '==' => $value == $threshold,
            '!=' => $value != $threshold,
            default => false,
        };
    }

    public static function getActiveRules(string $sourceType)
    {
        return self::where('source_type', $sourceType)
            ->where('is_active', true)
            ->get();
    }
}
