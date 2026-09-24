<?php

namespace App\Jobs\Tenant;

use App\Services\Tenant\LicensingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LicenseExpirationCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 3600;
    public int $timeout = 600;

    public function __construct(
        public readonly int $checkDaysAhead = 7
    ) {}

    public function handle(LicensingService $licensingService): void
    {
        Log::info("LicenseExpirationCheckJob: Checking licenses expiring within {$this->checkDaysAhead} days");

        try {
            $expiringLicenses = $licensingService->checkExpiringLicenses($this->checkDaysAhead);

            Log::info("LicenseExpirationCheckJob: Found " . count($expiringLicenses) . " expiring licenses");

            foreach ($expiringLicenses as $license) {
                Log::warning("LicenseExpirationCheckJob: License expiring soon", [
                    'license_id' => $license->getId()->toString(),
                    'tenant_id' => $license->getTenantId()->toString(),
                    'remaining_days' => $license->getRemainingDays(),
                    'expires_at' => $license->getExpiresAt()->format('Y-m-d H:i:s'),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("LicenseExpirationCheckJob: Failed to check license expirations", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("LicenseExpirationCheckJob: License check permanently failed", [
            'error' => $exception->getMessage(),
        ]);
    }
}
