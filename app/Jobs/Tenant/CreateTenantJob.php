<?php

namespace App\Jobs\Tenant;

use App\Services\Tenant\TenantManagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $domain,
        public readonly string $timezone,
        public readonly string $locale,
        public readonly array $branding = [],
        public readonly array $limits = []
    ) {}

    public function handle(TenantManagerService $tenantManager): void
    {
        Log::info("CreateTenantJob: Creating tenant {$this->slug}");

        try {
            $tenant = $tenantManager->createTenant(
                $this->name,
                $this->slug,
                $this->domain,
                $this->timezone,
                $this->locale
            );

            Log::info("CreateTenantJob: Tenant {$this->slug} created successfully", [
                'tenant_id' => $tenant->getId()->toString(),
            ]);

        } catch (\Exception $e) {
            Log::error("CreateTenantJob: Failed to create tenant {$this->slug}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("CreateTenantJob: Tenant creation permanently failed", [
            'slug' => $this->slug,
            'error' => $exception->getMessage(),
        ]);
    }
}
