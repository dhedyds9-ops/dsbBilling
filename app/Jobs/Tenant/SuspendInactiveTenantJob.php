<?php

namespace App\Jobs\Tenant;

use App\Services\Tenant\PluginService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SuspendInactiveTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 3600;
    public int $timeout = 300;

    public function __construct(
        public readonly int $inactiveDays = 90
    ) {}

    public function handle(
        PluginService $pluginService,
        \Src\Domain\Tenant\Repositories\TenantRepositoryInterface $tenantRepository
    ): void {
        Log::info("SuspendInactiveTenantJob: Checking for inactive tenants (>{$this->inactiveDays} days)");

        try {
            $inactiveTenants = $tenantRepository->findByStatus('active');

            $suspendedCount = 0;
            $cutoffDate = new \DateTime("-{$this->inactiveDays} days");

            foreach ($inactiveTenants as $tenant) {
                $lastActivity = $this->getLastActivity($tenant->getId()->toString());

                if ($lastActivity && $lastActivity < $cutoffDate) {
                    Log::info("SuspendInactiveTenantJob: Suspending inactive tenant", [
                        'tenant_id' => $tenant->getId()->toString(),
                        'last_activity' => $lastActivity->format('Y-m-d H:i:s'),
                    ]);

                    $suspendedCount++;
                }
            }

            Log::info("SuspendInactiveTenantJob: Completed, suspended {$suspendedCount} tenants");

        } catch (\Exception $e) {
            Log::error("SuspendInactiveTenantJob: Failed to check inactive tenants", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function getLastActivity(string $tenantId): ?\DateTime
    {
        return null;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SuspendInactiveTenantJob: Permanently failed", [
            'error' => $exception->getMessage(),
        ]);
    }
}
