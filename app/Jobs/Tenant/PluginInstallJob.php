<?php

namespace App\Jobs\Tenant;

use App\Services\Tenant\PluginService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PluginInstallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;
    public int $timeout = 600;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $pluginSlug,
        public readonly string $version
    ) {}

    public function handle(PluginService $pluginService): void
    {
        Log::info("PluginInstallJob: Installing plugin {$this->pluginSlug} for tenant {$this->tenantId}");

        try {
            $plugin = $pluginService->installPlugin(
                $this->tenantId,
                $this->pluginSlug,
                $this->version
            );

            Log::info("PluginInstallJob: Plugin {$this->pluginSlug} installed successfully", [
                'plugin_id' => $plugin->getId()->toString(),
            ]);

        } catch (\Exception $e) {
            Log::error("PluginInstallJob: Failed to install plugin", [
                'plugin_slug' => $this->pluginSlug,
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("PluginInstallJob: Plugin installation permanently failed", [
            'plugin_slug' => $this->pluginSlug,
            'tenant_id' => $this->tenantId,
            'error' => $exception->getMessage(),
        ]);
    }
}
