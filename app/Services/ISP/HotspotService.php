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
                    } catch (Throwable $e) {}
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
        ?string $password = null
    ): HotspotUser {
        return DB::transaction(function () use (
            $customerService,
            $serviceProfileId,
            $voucherPoolId,
            $userId,
            $username,
            $password
        ) {
            $hotspotUser = $this->hotspotUserRepository->create([
                'uuid' => (string) Str::uuid(),
                'username' => $username ?? 'hs_user_' . time(),
                'password' => $password ?? Str::random(12),
                'customer_service_id' => $customerService?->id,
                'service_profile_id' => $serviceProfileId,
                'voucher_pool_id' => $voucherPoolId,
                'status' => 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            try {
                $this->provisionToMikrotik($hotspotUser, true);
            } catch (Throwable $e) {
                Log::warning('Create Hotspot initial provision non-fatal', [
                    'user_id' => $hotspotUser->id,
                    'err' => $e->getMessage(),
                ]);
            }

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

            $this->provisionToMikrotik($hotspotUser);

            try {
                if (class_exists(\App\Events\ISP\HotspotUserStatusChangedEvent::class)) {
                    Event::dispatch(new \App\Events\ISP\HotspotUserStatusChangedEvent($hotspotUser, $oldStatus, 'active'));
                }
            } catch (Throwable $e) {}

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

            $this->provisionIsolationToMikrotik($hotspotUser);

            try {
                if (class_exists(\App\Events\ISP\HotspotUserStatusChangedEvent::class)) {
                    Event::dispatch(new \App\Events\ISP\HotspotUserStatusChangedEvent($hotspotUser, $oldStatus, 'suspended'));
                }
            } catch (Throwable $e) {}

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
            } catch (Throwable $e) {}

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
            } catch (Throwable $e) {}

            return $hotspotUser;
        });
    }

    protected function provisionToMikrotik(HotspotUser $hotspotUser, bool $isCreate = false): void
    {
        $routers = $this->resolveRouters($hotspotUser->serviceProfile);
        if (empty($routers)) {
            Log::warning('HotspotService provision: tidak ada Router aktif', ['user_id' => $hotspotUser->id]);
            return;
        }

        $profileName = $hotspotUser->serviceProfile?->name ?: 'default';
        $username = $hotspotUser->username;
        $password = $hotspotUser->password;

        $timelimit = null;
        $datalimit = null;
        try {
            $hours = (int)($hotspotUser->time_limit_hours ?? 0);
            if ($hours > 0) {
                $timelimit = sprintf('%02d:00:00', $hours);
            }
            $gb = (float)($hotspotUser->quota_gb ?? 0);
            if ($gb > 0) {
                $datalimit = (int)round($gb * 1024 * 1024 * 1024);
            }
        } catch (Throwable $e) {}

        $successEnabled = 0;
        $successUpsert = 0;
        $kicked = 0;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if (!$driver->connect()) {
                    continue;
                }
            } catch (Throwable $e) {
                Log::warning('HotspotService provision connect gagal', ['router_id' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
                continue;
            }

            try {
                $okEnable = $driver->enableHotspotUser($username);
                if ($okEnable) $successEnabled++;

                $options = [];
                if ($timelimit !== null) $options['limit-uptime'] = $timelimit;
                if ($datalimit !== null) $options['limit-bytes-total'] = $datalimit;

                $okUpsert = $driver->updateHotspotUser($username, $password, $profileName, $options);
                if (!$okUpsert) {
                    $okUpsert = $driver->addHotspotUser($username, $password, $profileName, $options);
                }
                if ($okUpsert) $successUpsert++;

                if (!$isCreate) {
                    $okKick = $driver->disconnectHotspotUser($username);
                    if ($okKick) $kicked++;
                }
            } catch (Throwable $e) {
                Log::error('HotspotService provision per-router gagal', [
                    'router_id' => $router->id,
                    'user' => $username,
                    'err' => $e->getMessage(),
                ]);
            }

            try { $driver->disconnect(); } catch (Throwable $e) {}
        }

        Log::info('HotspotService provisionToMikrotik selesai', [
            'user' => $username,
            'routers_checked' => count($routers),
            'enabled' => $successEnabled,
            'upserted' => $successUpsert,
            'kicked' => $kicked,
            'is_create' => $isCreate,
            'timelimit' => $timelimit,
            'datalimit' => $datalimit,
        ]);
    }

    protected function provisionIsolationToMikrotik(HotspotUser $hotspotUser): void
    {
        $routers = $this->resolveRouters($hotspotUser->serviceProfile);
        if (empty($routers)) {
            Log::warning('HotspotService isolir: tidak ada Router aktif', ['user_id' => $hotspotUser->id]);
            return;
        }

        $username = $hotspotUser->username;
        $successDisable = 0;
        $kicked = 0;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if (!$driver->connect()) {
                    continue;
                }
            } catch (Throwable $e) {
                Log::warning('HotspotService isolir connect gagal', ['router_id' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
                continue;
            }

            try {
                $okDisable = $driver->disableHotspotUser($username);
                if ($okDisable) $successDisable++;

                $okKick = $driver->disconnectHotspotUser($username);
                if ($okKick) $kicked++;
            } catch (Throwable $e) {
                Log::error('HotspotService isolir per-router gagal', [
                    'router_id' => $router->id,
                    'user' => $username,
                    'err' => $e->getMessage(),
                ]);
            }

            try { $driver->disconnect(); } catch (Throwable $e) {}
        }

        Log::info('HotspotService provisionIsolationToMikrotik selesai', [
            'user' => $username,
            'routers_checked' => count($routers),
            'disabled' => $successDisable,
            'kicked' => $kicked,
        ]);
    }

    protected function disconnectFromMikrotik(HotspotUser $hotspotUser): void
    {
        $routers = $this->resolveRouters($hotspotUser->serviceProfile);
        if (empty($routers)) {
            Log::warning('HotspotService disconnect: tidak ada Router aktif', ['user_id' => $hotspotUser->id]);
            return;
        }

        $username = $hotspotUser->username;
        $kicked = 0;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if (!$driver->connect()) {
                    continue;
                }
            } catch (Throwable $e) {
                Log::warning('HotspotService disconnect connect gagal', ['router_id' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
                continue;
            }

            try {
                $okKick = $driver->disconnectHotspotUser($username);
                if ($okKick) $kicked++;
            } catch (Throwable $e) {
                Log::error('HotspotService disconnect per-router gagal', [
                    'router_id' => $router->id,
                    'user' => $username,
                    'err' => $e->getMessage(),
                ]);
            }

            try { $driver->disconnect(); } catch (Throwable $e) {}
        }

        Log::info('HotspotService disconnectFromMikrotik selesai', [
            'user' => $username,
            'routers_checked' => count($routers),
            'kicked' => $kicked,
        ]);
    }

    protected function removeFromMikrotik(HotspotUser $hotspotUser): void
    {
        $routers = $this->resolveRouters($hotspotUser->serviceProfile);
        if (empty($routers)) {
            Log::warning('HotspotService remove: tidak ada Router aktif', ['user_id' => $hotspotUser->id]);
            return;
        }

        $username = $hotspotUser->username;
        $successRemove = 0;
        $kicked = 0;

        foreach ($routers as $router) {
            try {
                $driver = $this->routerOSService->getDriver($router);
                if (!$driver->connect()) {
                    continue;
                }
            } catch (Throwable $e) {
                Log::warning('HotspotService remove connect gagal', ['router_id' => $router->id, 'user' => $username, 'err' => $e->getMessage()]);
                continue;
            }

            try {
                $okKick = $driver->disconnectHotspotUser($username);
                if ($okKick) $kicked++;

                $okRemove = $driver->removeHotspotUser($username);
                if ($okRemove) $successRemove++;
            } catch (Throwable $e) {
                Log::error('HotspotService remove per-router gagal', [
                    'router_id' => $router->id,
                    'user' => $username,
                    'err' => $e->getMessage(),
                ]);
            }

            try { $driver->disconnect(); } catch (Throwable $e) {}
        }

        Log::info('HotspotService removeFromMikrotik selesai', [
            'user' => $username,
            'routers_checked' => count($routers),
            'removed' => $successRemove,
            'kicked' => $kicked,
        ]);
    }
}
