<?php

namespace App\Services\ISP;

use App\Integration\MikroTik\Services\RouterOSService;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\Customer\CustomerService;
use App\Repositories\ISP\HotspotUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HotspotService
{
    public function __construct(
        protected HotspotUserRepository $hotspotUserRepository,
        protected RouterOSService $routerOSService,
    ) {}

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
                    } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }
                }
            }
            if (empty($routers)) {
                $fallback = Router::active()->limit(10)->get();
                foreach ($fallback as $r) $routers[$r->id] = $r;
            }
        } catch (Throwable $e) {
            Log::warning('HotspotService resolveRouters fallback all active', [
                'profile_id' => $profile?->id,
                'err' => $e->getMessage(),
            ]);
            $fallback = Router::active()->limit(10)->get();
            $routers = [];
            foreach ($fallback as $r) $routers[$r->id] = $r;
        }
        return array_values($routers);
    }

    public function createHotspotUser(
        ?CustomerService $customerService,
        int $serviceProfileId,
        ?int $voucherPoolId,
        int $userId,
        ?string $username = null,
        ?string $password = null,
        bool $provisionOnCreate = true,
        array $networkData = []
    ): HotspotUser {
        return DB::transaction(function () use (
            $customerService,
            $serviceProfileId,
            $voucherPoolId,
            $userId,
            $username,
            $password,
            $provisionOnCreate,
            $networkData
        ) {
            $hotspotUser = $this->hotspotUserRepository->create(array_merge([
                'uuid' => (string) Str::uuid(),
                'username' => $username ?? 'hs_user_' . time(),
                'password' => $password ?? Str::random(12),
                'customer_service_id' => $customerService?->id,
                'service_profile_id' => $serviceProfileId,
                'voucher_pool_id' => $voucherPoolId,
                'status' => 'pending',
                'reseller_id' => $customerService?->reseller_id ?? ($networkData['reseller_id'] ?? null),
                'created_by' => $userId,
                'updated_by' => $userId,
            ], $networkData));

            return $hotspotUser;
        });
    }

    public function activateHotspotUser(int $hotspotUserId, int $userId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUserId, $userId) {
            $hotspotUser = $this->hotspotUserRepository->find($hotspotUserId);
            $oldStatus = $hotspotUser->status;

            $hotspotUser->update([
                'status' => 'active',
                'activated_at' => $hotspotUser->activated_at ?? now(),
                'suspended_at' => null,
                'updated_by' => $userId,
            ]);

            // Sinkronisasi status ke CustomerService dan Customer
            if ($hotspotUser->customer_service_id) {
                $cs = \App\Models\Customer\CustomerService::find($hotspotUser->customer_service_id);
                if ($cs) {
                    $cs->update([
                        'status' => 'active',
                        'suspended_at' => null,
                    ]);
                    if ($cs->customer_id) {
                        \App\Models\CRM\Customer::where('id', $cs->customer_id)->update([
                            'status' => 'active',
                        ]);
                    }
                }
            }

            if ($oldStatus !== 'pending') {
                $this->provisionToMikrotik($hotspotUser);
            }

            try {
                if (class_exists(\App\Events\ISP\HotspotUserStatusChangedEvent::class)) {
                    Event::dispatch(new \App\Events\ISP\HotspotUserStatusChangedEvent($hotspotUser, $oldStatus, 'active'));
                }
            } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }

            return $hotspotUser;
        });
    }

    public function suspendHotspotUser(int $hotspotUserId, int $userId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUserId, $userId) {
            $hotspotUser = $this->hotspotUserRepository->find($hotspotUserId);
            $oldStatus = $hotspotUser->status;

            $hotspotUser->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'updated_by' => $userId,
            ]);

            // Sinkronisasi status ke CustomerService dan Customer
            if ($hotspotUser->customer_service_id) {
                $cs = \App\Models\Customer\CustomerService::find($hotspotUser->customer_service_id);
                if ($cs) {
                    $cs->update([
                        'status' => 'suspended',
                        'suspended_at' => now(),
                    ]);
                    if ($cs->customer_id) {
                        \App\Models\CRM\Customer::where('id', $cs->customer_id)->update([
                            'status' => 'suspend',
                        ]);
                    }
                }
            }

            $this->provisionIsolationToMikrotik($hotspotUser);

            try {
                if (class_exists(\App\Events\ISP\HotspotUserStatusChangedEvent::class)) {
                    Event::dispatch(new \App\Events\ISP\HotspotUserStatusChangedEvent($hotspotUser, $oldStatus, 'suspended'));
                }
            } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }

            return $hotspotUser;
        });
    }

    public function terminateHotspotUser(int $hotspotUserId, int $userId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUserId, $userId) {
            $hotspotUser = $this->hotspotUserRepository->find($hotspotUserId);
            $oldStatus = $hotspotUser->status;

            $hotspotUser->update([
                'status' => 'terminated',
                'terminated_at' => now(),
                'suspended_at' => null,
                'updated_by' => $userId,
            ]);

            $this->removeFromMikrotik($hotspotUser);

            try {
                if (class_exists(\App\Events\ISP\HotspotUserStatusChangedEvent::class)) {
                    Event::dispatch(new \App\Events\ISP\HotspotUserStatusChangedEvent($hotspotUser, $oldStatus, 'terminated'));
                }
            } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }

            return $hotspotUser;
        });
    }

    public function updateHotspotUserCredentials(
        int $hotspotUserId,
        int $userId,
        ?string $newUsername = null,
        ?string $newPassword = null
    ): HotspotUser {
        return DB::transaction(function () use (
            $hotspotUserId,
            $userId,
            $newUsername,
            $newPassword
        ) {
            $hotspotUser = $this->hotspotUserRepository->find($hotspotUserId);

            $updateData = ['updated_by' => $userId];
            if ($newUsername) {
                $updateData['username'] = $newUsername;
            }
            if ($newPassword) {
                $updateData['password'] = $newPassword;
            }

            $hotspotUser->update($updateData);

            $this->provisionToMikrotik($hotspotUser);
            $this->disconnectFromMikrotik($hotspotUser);

            try {
                if (class_exists(\App\Events\ISP\HotspotUserCredentialsChangedEvent::class)) {
                    Event::dispatch(new \App\Events\ISP\HotspotUserCredentialsChangedEvent($hotspotUser, array_keys($updateData)));
                }
            } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }

            return $hotspotUser;
        });
    }

    public function provisionToMikrotik(HotspotUser $hotspotUser, bool $isCreate = false): void
    {
        if ($isCreate || $hotspotUser->wasRecentlyCreated) return;

        $routers = $this->resolveRouters($hotspotUser->serviceProfile);
        try {
            if (!empty($hotspotUser->router_id) && $hotspotUser->router) {
                $routers[$hotspotUser->router_id] = $hotspotUser->router;
            }
        } catch (\Throwable) {}

        if (empty($routers)) {
            $routers = Router::active()->get();
        }

        $normalProfile = $hotspotUser->serviceProfile?->hotspot_profile ?: ($hotspotUser->serviceProfile?->user_profile ?: 'default');
        $username = $hotspotUser->username;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if ($driver->connect()) {
                    $driver->setHotspotUserProfile($username, $normalProfile);
                    $driver->disconnect();
                }
            } catch (\Throwable $e) {}
            $this->kickSessionOnRouter($router, $username);
        }
    }

    public function provisionIsolationToMikrotik(HotspotUser $hotspotUser): void
    {
        $routers = $this->resolveRouters($hotspotUser->serviceProfile);
        try {
            if (!empty($hotspotUser->router_id) && $hotspotUser->router) {
                $routers[$hotspotUser->router_id] = $hotspotUser->router;
            }
        } catch (\Throwable) {}

        if (empty($routers)) {
            $routers = Router::active()->get();
        }

        $isolirProfile = \App\Models\Setting::getValue('billing.isolir_profile', 'ISOLIR');
        $username = $hotspotUser->username;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if ($driver->connect()) {
                    $driver->setHotspotUserProfile($username, $isolirProfile);
                    $driver->disconnect();
                }
            } catch (\Throwable $e) {}
            $this->kickSessionOnRouter($router, $username);
        }
    }

    public function disconnectFromMikrotik(HotspotUser $hotspotUser): void
    {
        $this->kickActiveSession($hotspotUser);
    }

    public function removeFromMikrotik(HotspotUser $hotspotUser): void
    {
        $this->kickActiveSession($hotspotUser);
    }

    public function kickActiveSession(HotspotUser $hotspotUser): void
    {
        if ($hotspotUser->wasRecentlyCreated) return;

        $routers = $this->resolveRouters($hotspotUser->serviceProfile);
        try {
            if (!empty($hotspotUser->router_id) && $hotspotUser->router) {
                $routers[$hotspotUser->router_id] = $hotspotUser->router;
            }
        } catch (\Throwable) {}

        if (empty($routers)) {
            $routers = Router::active()->get();
        }
        if (empty($routers)) return;

        $username = $hotspotUser->username;

        foreach ($routers as $router) {
            $this->kickSessionOnRouter($router, $username);
        }
    }

    private function kickSessionOnRouter(Router $router, string $username): void
    {
        // 1. Kirim RADIUS PoD (Packet of Disconnect RFC 5176) via UDP port 3799
        try {
            $nas = \App\Models\ISP\RadiusNas::withoutGlobalScopes()
                ->where('nas_ip_address', $router->ip_address)
                ->first();
            if ($nas) {
                app(\App\Services\ISP\Radius\RFC5176DisconnectService::class)->sendDisconnect($nas, [
                    \App\Services\ISP\Radius\RFC5176DisconnectService::ATTR_USER_NAME => $username,
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Radius PoD hotspot kick failed", ['router' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
        }

        // 2. Kirim RouterOS API (/ip hotspot active remove)
        try {
            $driver = $this->routerOSService->getDriver($router);
            if ($driver->connect()) {
                $driver->disconnectHotspotUser($username);
                $driver->disconnect();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("RouterOS API hotspot kick failed", ['router' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
        }
    }
}
