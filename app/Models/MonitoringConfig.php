<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringConfig extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function get($key, $default = null)
    {
        $config = self::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }

        return match ($config->type) {
            'integer' => (int) $config->value,
            'boolean' => (bool) $config->value,
            'float' => (float) $config->value,
            default => $config->value,
        };
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
