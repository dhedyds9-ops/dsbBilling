<?php

namespace App\Services\ISP;

use App\Events\ISP\PPPoEUserStatusChangedEvent;
use App\Integration\MikroTik\Services\RouterOSService;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\Customer\CustomerService;
use App\Repositories\ISP\PPPoEUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PPPoEService
{
    public function __construct(
        protected PPPoEUserRepository $pppoeUserRepository,
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
            Log::warning('PPPoEService resolveRouters fallback all active', [
                'profile_id' => $profile?->id,
                'err' => $e->getMessage(),
            ]);
            $fallback = Router::active()->limit(10)->get();
            $routers = [];
            foreach ($fallback as $r) $routers[$r->id] = $r;
        }
        return array_values($routers);
    }

    public function createPPPoEUser(
        CustomerService $customerService,
        int $serviceProfileId,
        ?int $ipAllocationId,
        int $userId,
        ?string $username = null,
        ?string $password = null,
        array $networkData = []
    ): PPPoEUser {
        return DB::transaction(function () use (
            $customerService,
            $serviceProfileId,
            $ipAllocationId,
            $userId,
            $username,
            $password,
            $networkData
        ) {
            $pppoeUser = $this->pppoeUserRepository->create(array_merge([
                'uuid' => (string) Str::uuid(),
                'username' => $username ?? 'user_' . $customerService->id,
                'password' => $password ?? Str::random(12),
                'customer_service_id' => $customerService->id,
                'service_profile_id' => $serviceProfileId,
                'ip_allocation_id' => $ipAllocationId,
                'status' => 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ], $networkData));

            try {
                $this->provisionToMikrotik($pppoeUser, true);
            } catch (Throwable $e) {
                Log::warning('Create PPPoE initial provision non-fatal', [
                    'user_id' => $pppoeUser->id,
                    'err' => $e->getMessage(),
                ]);
            }

            return $pppoeUser;
        });
    }

    public function activatePPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $oldStatus = $pppoeUser->status;

            $pppoeUser->update([
                'status' => 'active',
                'activated_at' => $pppoeUser->activated_at ?? now(),
                'suspended_at' => null,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new PPPoEUserStatusChangedEvent($pppoeUser, $oldStatus, 'active'));

            if ($oldStatus !== 'pending') {
                $this->provisionToMikrotik($pppoeUser);
            }

            return $pppoeUser;
        });
    }

    public function suspendPPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $oldStatus = $pppoeUser->status;

            $pppoeUser->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'updated_by' => $userId,
            ]);

            // Sinkronisasi status ke CustomerService dan Customer
            if ($pppoeUser->customer_service_id) {
                $cs = \App\Models\Customer\CustomerService::find($pppoeUser->customer_service_id);
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

            Event::dispatch(new PPPoEUserStatusChangedEvent($pppoeUser, $oldStatus, 'suspended'));

            $this->provisionIsolationToMikrotik($pppoeUser);

            return $pppoeUser;
        });
    }

    public function reactivatePPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $oldStatus = $pppoeUser->status;

            $pppoeUser->update([
                'status' => 'active',
                'suspended_at' => null,
                'updated_by' => $userId,
            ]);

            // Sinkronisasi status ke CustomerService dan Customer
            if ($pppoeUser->customer_service_id) {
                $cs = \App\Models\Customer\CustomerService::find($pppoeUser->customer_service_id);
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

            Event::dispatch(new PPPoEUserStatusChangedEvent($pppoeUser, $oldStatus, 'active'));

            $this->provisionToMikrotik($pppoeUser);

            return $pppoeUser;
        });
    }

    public function terminatePPPoEUser(int $pppoeUserId, int $userId): PPPoEUser
    {
        return DB::transaction(function () use ($pppoeUserId, $userId) {
            $pppoeUser = $this->pppoeUserRepository->find($pppoeUserId);
            $oldStatus = $pppoeUser->status;

            $pppoeUser->update([
                'status' => 'terminated',
                'terminated_at' => now(),
                'suspended_at' => null,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new PPPoEUserStatusChangedEvent($pppoeUser, $oldStatus, 'terminated'));

            $this->removeFromMikrotik($pppoeUser);

            return $pppoeUser;
        });
    }

    public function provisionToMikrotik(PPPoEUser $pppoeUser, bool $isCreate = false): void
    {
        if ($isCreate || $pppoeUser->wasRecentlyCreated) return;

        $routers = $this->resolveRouters($pppoeUser->serviceProfile);
        try {
            if (!empty($pppoeUser->router_id) && $pppoeUser->router) {
                $routers[$pppoeUser->router_id] = $pppoeUser->router;
            }
        } catch (\Throwable) {}

        if (empty($routers)) {
            $routers = Router::active()->get();
        }

        $normalProfile = $pppoeUser->serviceProfile?->mikrotik_profile ?: ($pppoeUser->serviceProfile?->name ?: 'default');
        $username = $pppoeUser->username;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if ($driver->connect()) {
                    $driver->setPppSecretProfile($username, $normalProfile);
                    $driver->disconnect();
                }
            } catch (\Throwable $e) {}
            $this->kickSessionOnRouter($router, $username);
        }
    }

    public function provisionIsolationToMikrotik(PPPoEUser $pppoeUser): void
    {
        $routers = $this->resolveRouters($pppoeUser->serviceProfile);
        try {
            if (!empty($pppoeUser->router_id) && $pppoeUser->router) {
                $routers[$pppoeUser->router_id] = $pppoeUser->router;
            }
        } catch (\Throwable) {}

        if (empty($routers)) {
            $routers = Router::active()->get();
        }

        $isolirProfile = \App\Models\Setting::getValue('billing.isolir_profile', 'ISOLIR');
        $username = $pppoeUser->username;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if ($driver->connect()) {
                    $driver->setPppSecretProfile($username, $isolirProfile);
                    $driver->disconnect();
                }
            } catch (\Throwable $e) {}
            $this->kickSessionOnRouter($router, $username);
        }
    }

    public function removeFromMikrotik(PPPoEUser $pppoeUser): void
    {
        $this->kickActiveSession($pppoeUser);
    }

    public function kickActiveSession(PPPoEUser $pppoeUser): void
    {
        if ($pppoeUser->wasRecentlyCreated) return;

        $routers = $this->resolveRouters($pppoeUser->serviceProfile);
        try {
            if (!empty($pppoeUser->router_id) && $pppoeUser->router) {
                $routers[$pppoeUser->router_id] = $pppoeUser->router;
            }
        } catch (\Throwable) {}

        if (empty($routers)) {
            $routers = Router::active()->get();
        }
        if (empty($routers)) return;

        $username = $pppoeUser->username;

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
            \Illuminate\Support\Facades\Log::warning("Radius PoD kick failed", ['router' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
        }

        // 2. Kirim RouterOS API (/ppp active remove)
        try {
            $driver = $this->routerOSService->getDriver($router);
            if ($driver->connect()) {
                $driver->disconnectPppoeUser($username);
                $driver->disconnect();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("RouterOS API kick failed", ['router' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
        }
    }
}
