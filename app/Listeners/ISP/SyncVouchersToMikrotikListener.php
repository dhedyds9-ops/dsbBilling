<?php

namespace App\Listeners\ISP;

use App\Events\ISP\Voucher\VouchersGeneratedEvent;
use App\Integration\MikroTik\Services\RouterOSService;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SyncVouchersToMikrotikListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private RouterOSService $routerOSService
    ) {}

    public function handle(VouchersGeneratedEvent $event): void
    {
        $vouchers = $event->vouchers;
        if ($vouchers->isEmpty()) {
            return;
        }

        // Ambil service profile dari voucher pertama (asumsinya 1 batch sama)
        $serviceProfileId = $vouchers->first()->service_profile_id;
        $profile = ServiceProfile::find($serviceProfileId);
        
        $routers = $this->resolveRouters($profile);

        if (empty($routers)) {
            Log::warning('SyncVouchersToMikrotikListener: Tidak ada router untuk di-sync.');
            return;
        }

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if (!$driver->connect()) {
                    continue;
                }

                foreach ($vouchers as $voucher) {
                    $username = $voucher->code;
                    $password = $voucher->login_method === 'username_password' ? $voucher->password : '';
                    $profileName = $profile?->name ?: 'default';
                    
                    // Push to mikrotik
                    $options = [];
                    $okUpsert = $driver->addHotspotUser($username, $password, $profileName, $options);
                }

                $driver->disconnect();
            } catch (\Throwable $e) {
                Log::error('SyncVouchersToMikrotikListener error', [
                    'router_id' => $router->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function resolveRouters(?ServiceProfile $profile): array
    {
        $routers = [];
        try {
            if ($profile) {
                if (!empty($profile->pop_id)) {
                    $fromPop = Router::active()->where('pop_id', $profile->pop_id)->get();
                    if ($fromPop->isNotEmpty()) {
                        foreach ($fromPop as $r) $routers[$r->id] = $r;
                    }
                }
                if (method_exists($profile, 'router')) {
                    try {
                        $r = $profile->router;
                        if ($r && $r instanceof Router) {
                            $routers[$r->id] = $r;
                        }
                    } catch (\Throwable $e) {}
                }
            }
            if (empty($routers)) {
                $fallback = Router::active()->limit(10)->get();
                foreach ($fallback as $r) $routers[$r->id] = $r;
            }
        } catch (\Throwable $e) {
            Log::warning('SyncVouchersToMikrotikListener resolveRouters fallback', [
                'err' => $e->getMessage(),
            ]);
            $fallback = Router::active()->limit(10)->get();
            $routers = [];
            foreach ($fallback as $r) $routers[$r->id] = $r;
        }
        return array_values($routers);
    }
}
