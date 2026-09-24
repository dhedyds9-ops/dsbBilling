<?php

namespace App\Jobs\Tenant;

use App\Services\Tenant\TenantManagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TenantLimitsCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 1800;
    public int $timeout = 600;

    public function __construct(
        public readonly string $tenantId
    ) {}

    public function handle(TenantManagerService $tenantManager): void
    {
        Log::info("TenantLimitsCheckJob: Checking limits for tenant {$this->tenantId}");

        try {
            $violations = $tenantManager->checkLimits($this->tenantId);

            if (!empty($violations)) {
                Log::warning("TenantLimitsCheckJob: Tenant {$this->tenantId} exceeded limits", [
                    'violations' => $violations,
                ]);
            } else {
                Log::info("TenantLimitsCheckJob: Tenant {$this->tenantId} within limits");
            }

        } catch (\Exception $e) {
            Log::error("TenantLimitsCheckJob: Failed to check tenant limits", [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("TenantLimitsCheckJob: Limits check permanently failed", [
            'tenant_id' => $this->tenantId,
            'error' => $exception->getMessage(),
        ]);
    }
}
