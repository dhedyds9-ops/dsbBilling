<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public const CACHE_KEY = 'settings.all';
    public const CACHE_TTL = 3600;

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $all = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::all()->pluck('value', 'key')->all();
        });

        return $all[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value, string $type = 'string', ?string $group = null, ?string $description = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
        Cache::forget(self::CACHE_KEY);
    }

    public static function setMany(array $settings, ?string $group = null): void
    {
        foreach ($settings as $key => $item) {
            $value = is_array($item) ? ($item['value'] ?? null) : $item;
            $type = is_array($item) ? ($item['type'] ?? 'string') : 'string';
            $desc = is_array($item) ? ($item['description'] ?? null) : null;
            self::setValue($key, $value, $type, $group, $desc);
        }
    }

    public static function getGroup(string $prefix): array
    {
        $all = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::all()->pluck('value', 'key')->all();
        });

        $result = [];
        $len = strlen($prefix) + 1;
        foreach ($all as $key => $value) {
            if (str_starts_with($key, $prefix . '.')) {
                $result[substr($key, $len)] = $value;
            }
        }
        return $result;
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget(self::CACHE_KEY));
        static::deleted(fn() => Cache::forget(self::CACHE_KEY));
    }
}
