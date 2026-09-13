<?php

namespace App\Jobs\ISP;

use App\Integration\MikroTik\Services\RouterOSService;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncServiceProfileToMikrotik implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $serviceProfile;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [15, 60, 180];
    }

    public function __construct(ServiceProfile $serviceProfile)
    {
        $this->serviceProfile = $serviceProfile;
    }

    public function handle(RouterOSService $routerOSService): void
    {
        Log::info('Mulai sinkronisasi ServiceProfile ke MikroTik', ['profile_id' => $this->serviceProfile->id, 'name' => $this->serviceProfile->name]);

        $routers = Router::active()->get();

        if ($routers->isEmpty()) {
            Log::warning('Tidak ada router aktif untuk disinkronisasi.', ['profile_id' => $this->serviceProfile->id]);
            return;
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($routers as $router) {
            try {
                $driver = $routerOSService->getDriver($router);
                if (!$driver->connect()) {
                    throw new \Exception("Gagal koneksi ke Router ID: {$router->id}");
                }

                $this->syncProfileToRouter($driver, $this->serviceProfile);

                $driver->disconnect();
                $successCount++;
            } catch (Throwable $e) {
                $failedCount++;
                $errors[] = "Router #{$router->id}: " . $e->getMessage();
                Log::error('Gagal sinkronisasi ServiceProfile ke Router', [
                    'router_id' => $router->id,
                    'profile_id' => $this->serviceProfile->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Tentukan Status Sinkronisasi
        $status = 'failed';
        if ($successCount === $routers->count()) {
            $status = 'success';
        } elseif ($successCount > 0) {
            $status = 'partial_success';
        }

        // Update status sinkronisasi jika kolom tersedia
        // if (in_array('provisioning_status', $this->serviceProfile->getFillable())) { ... }

        // Log hasil sinkronisasi
        $this->logProvisioningResult($status, $successCount, $failedCount, $errors);

        if ($failedCount > 0 && $this->attempts() < $this->tries) {
            // Lempar exception agar job di-retry oleh Laravel Queue
            throw new \Exception("Sinkronisasi gagal pada sebagian/semua router. Menunggu retry. Errors: " . implode(' | ', $errors));
        }
    }

    private function syncProfileToRouter($driver, ServiceProfile $profile): void
    {
        $options = [
            'rate-limit' => $profile->radius_rate_limit,
            // 'local-address' dan 'remote-address' bisa ditambahkan jika IP pool juga disinkronkan.
        ];

        // Sinkronisasi PPP Profile
        if (in_array($profile->service_type, ['pppoe', 'ftth', 'kombinasi'])) {
            $pppName = $profile->ppp_profile_name ?? ('PPP-PROFILE-' . preg_replace('/[^a-zA-Z0-9]/', '', $profile->name));
            
            $existingProfiles = $driver->getPppProfiles();
            $exists = collect($existingProfiles)->contains(function ($p) use ($pppName) {
                return isset($p['name']) && $p['name'] === $pppName;
            });

            if ($exists) {
                $driver->updatePppProfile($pppName, $options);
            } else {
                $driver->addPppProfile($pppName, $options);
            }
        }

        // Sinkronisasi Hotspot Profile
        if (in_array($profile->service_type, ['hotspot', 'voucher', 'kombinasi'])) {
            $hsName = $profile->target_hotspot_profile ?? ('HS-PROFILE-' . preg_replace('/[^a-zA-Z0-9]/', '', $profile->name));
            
            $existingHsProfiles = $driver->getHotspotUserProfiles();
            $exists = collect($existingHsProfiles)->contains(function ($p) use ($hsName) {
                return isset($p['name']) && $p['name'] === $hsName;
            });

            if ($exists) {
                $driver->updateHotspotUserProfile($hsName, $options);
            } else {
                $driver->addHotspotUserProfile($hsName, $options);
            }
        }
    }

    private function logProvisioningResult(string $status, int $success, int $failed, array $errors): void
    {
        try {
            AuditLog::create([
                'auditable_type' => get_class($this->serviceProfile),
                'auditable_id' => $this->serviceProfile->id,
                'event' => 'mikrotik_provisioning',
                'old_values' => [],
                'new_values' => [
                    'status' => $status,
                    'success_count' => $success,
                    'failed_count' => $failed,
                    'errors' => $errors,
                ],
                'user_id' => $this->serviceProfile->updated_by ?? $this->serviceProfile->created_by,
                'ip_address' => '127.0.0.1', // Background Job
                'user_agent' => 'QueueWorker',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menulis AuditLog provisioning: ' . $e->getMessage());
        }
    }
}
