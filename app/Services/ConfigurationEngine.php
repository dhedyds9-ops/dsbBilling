<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ConfigurationEngine
{
    protected array $config = [];
    protected string $cacheKey = 'app_config';

    public function __construct()
    {
        $this->load();
    }

    public function load(): void
    {
        $this->config = Cache::get($this->cacheKey, config('app', []));
    }

    public function get(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    public function set(string $key, $value): void
    {
        data_set($this->config, $key, $value);
        $this->save();
    }

    public function save(): void
    {
        Cache::forever($this->cacheKey, $this->config);
    }

    public function all(): array
    {
        return $this->config;
    }
}
