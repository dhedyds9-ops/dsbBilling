<?php

namespace App\Services\ISP\Session;

use App\Integration\MikroTik\Services\RouterOSService;
use App\Models\AuditLog;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\RadiusAccounting;
use App\Models\ISP\RadiusNas;
use App\Models\User;
use App\Services\ISP\Radius\RFC5176DisconnectService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SessionKickService
{
    public function __construct(
        private readonly RFC5176DisconnectService $disconnectService,
        private readonly RouterOSService $routerOS,
    ) {}

    public function kickPppoe(PppActiveSession $session, ?User $operator): array
    {
        $router = $session->router;
        if (!$router) {
            return ['success' => false, 'error' => 'Router reference missing'];
        }

        $identifiers = [];
        if (!empty($session->name)) {
            $identifiers[RFC5176DisconnectService::ATTR_USER_NAME] = $session->name;
        }
        if (!empty($session->caller_id)) {
            $identifiers[RFC5176DisconnectService::ATTR_CALLING_STATION_ID] = $session->caller_id;
        }
        if (!empty($session->address) && filter_var($session->address, FILTER_VALIDATE_IP)) {
            $identifiers[RFC5176DisconnectService::ATTR_FRAMED_IP_ADDRESS] = $session->address;
        }

        $podResult = $this->tryPoD($router, $identifiers);

        $apiResult = null;
        try {
            $driver = $this->routerOS->getDriver($router);
            if (method_exists($driver, 'disconnectPppoeUser')) {
                $name = (string)($session->name ?? '');
                if ($name !== '') {
                    $apiResult = $driver->disconnectPppoeUser($name);
                }
            }
        } catch (Throwable $e) {
            Log::warning('SessionKick PPPoE ROS API cleanup gagal', ['id' => $session->id, 'err' => $e->getMessage()]);
        }

        $success = $podResult['success'] ?? false || (is_array($apiResult) && ($apiResult['success'] ?? false));
        if (!is_bool($success) && !$success) {
            $success = $podResult['success'] ?? false;
        }

        $this->writeAccountingTerminate(
            username: $session->name,
            acctSessionId: null,
            cause: 'Admin-Reset',
            causeId: 6,
            framedIp: $session->address,
            nasIp: $router->ip_address,
        );

        $this->writeAudit('kick_pppoe', $session, $operator, [
            'pod' => $podResult,
            'ros_api' => $apiResult,
            'success' => $success,
        ]);

        try {
            $session->delete();
        } catch (Throwable $e) {
        }

        $this->deleteOnlineSessionsFor(
            protocol: 'pppoe',
            username: $session->name ?? null,
            routerId: $session->router_id ?? null,
            address: $session->address ?? null,
            callerId: $session->caller_id ?? null,
        );

        return [
            'success' => (bool)$success,
            'protocol' => 'pppoe',
            'pod' => $podResult,
            'ros_api_cleanup' => $apiResult,
            'session_id' => $session->id,
        ];
    }

    public function kickHotspot(HotspotActiveSession $session, ?User $operator): array
    {
        $router = $session->router;
        if (!$router) {
            return ['success' => false, 'error' => 'Router reference missing'];
        }

        $identifiers = [];
        if (!empty($session->user)) {
            $identifiers[RFC5176DisconnectService::ATTR_USER_NAME] = $session->user;
        }
        if (!empty($session->mac_address)) {
            $identifiers[RFC5176DisconnectService::ATTR_CALLING_STATION_ID] = $session->mac_address;
        }
        if (!empty($session->address) && filter_var($session->address, FILTER_VALIDATE_IP)) {
            $identifiers[RFC5176DisconnectService::ATTR_FRAMED_IP_ADDRESS] = $session->address;
        }

        $podResult = $this->tryPoD($router, $identifiers);

        $apiResult = null;
        try {
            $driver = $this->routerOS->getDriver($router);
            if (method_exists($driver, 'disconnectHotspotUser')) {
                $user = (string)($session->user ?? '');
                if ($user !== '') {
                    $apiResult = $driver->disconnectHotspotUser($user, $session->mac_address ?? null);
                }
            }
        } catch (Throwable $e) {
            Log::warning('SessionKick Hotspot ROS API cleanup gagal', ['id' => $session->id, 'err' => $e->getMessage()]);
        }

        $success = ($podResult['success'] ?? false) || (is_array($apiResult) && ($apiResult['success'] ?? false));

        $this->writeAccountingTerminate(
            username: $session->user,
            acctSessionId: null,
            cause: 'Admin-Reset',
            causeId: 6,
            framedIp: $session->address,
            nasIp: $router->ip_address,
            callingStationId: $session->mac_address,
        );

        $this->writeAudit('kick_hotspot', $session, $operator, [
            'pod' => $podResult,
            'ros_api' => $apiResult,
            'success' => $success,
        ]);

        try {
            $session->delete();
        } catch (Throwable $e) {
        }

        $this->deleteOnlineSessionsFor(
            protocol: 'hotspot',
            username: $session->user ?? null,
            routerId: $session->router_id ?? null,
            address: $session->address ?? null,
            callerId: $session->mac_address ?? null,
        );

        return [
            'success' => (bool)$success,
            'protocol' => 'hotspot',
            'pod' => $podResult,
            'ros_api_cleanup' => $apiResult,
            'session_id' => $session->id,
        ];
    }

    public function kickUsernameGlobally(string $username, ?User $operator): array
    {
        $results = [];

        $ppp = PppActiveSession::query()->where('name', $username)->with('router')->get();
        foreach ($ppp as $s) {
            $results[] = $this->kickPppoe($s, $operator);
        }

        $hs = HotspotActiveSession::query()->where('user', $username)->with('router')->get();
        foreach ($hs as $s) {
            $results[] = $this->kickHotspot($s, $operator);
        }

        if (count($results) === 0) {
            $identifiers = [RFC5176DisconnectService::ATTR_USER_NAME => $username];
            $nases = RadiusNas::active()->get();
            foreach ($nases as $nas) {
                $results[] = [
                    'protocol' => 'global_broadcast_pod',
                    'nas' => $nas->nas_name,
                    'pod' => $this->disconnectService->sendDisconnect($nas, $identifiers),
                ];
            }
        }

        return [
            'success' => count($results) > 0,
            'count' => count($results),
            'results' => $results,
        ];
    }

    private function deleteOnlineSessionsFor(
        string $protocol,
        ?string $username,
        ?int $routerId = null,
        ?string $address = null,
        ?string $callerId = null,
    ): void {
        try {
            $query = \App\Models\ISP\OnlineSession::query()
                ->where('protocol', $protocol);

            if ($username !== null && $username !== '') {
                $query->where('username', $username);
            }
            if ($routerId !== null) {
                $query->where('router_id', $routerId);
            }
            if ($address !== null && $address !== '') {
                $query->where('address', $address);
            }
            if ($callerId !== null && $callerId !== '') {
                $query->where(function ($q) use ($callerId) {
                    $q->where('mac_address', $callerId)
                        ->orWhere('caller_id', $callerId);
                });
            }

            if (!$query->getQuery()->wheres) {
                return;
            }
            $query->delete();
        } catch (\Throwable $e) {
            Log::warning('Kick OnlineSession delete non-fatal', ['err' => $e->getMessage()]);
        }
    }

    private function tryPoD($router, array $identifiers): ?array
    {
        if (count($identifiers) === 0) {
            return null;
        }

        try {
            $nas = RadiusNas::active()
                ->where(function ($q) use ($router) {
                    $q->where('nas_ip_address', $router->ip_address)
                        ->orWhereHas('nasDevice', fn($sub) => $sub->where('ip_address', $router->ip_address));
                })
                ->first();
            if (!$nas) {
                return null;
            }
            return $this->disconnectService->sendDisconnect($nas, $identifiers);
        } catch (Throwable $e) {
            Log::warning('PoD send gagal', ['router' => $router->name, 'err' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function writeAccountingTerminate(
        ?string $username,
        ?string $acctSessionId,
        string $cause,
        int $causeId,
        ?string $framedIp = null,
        ?string $nasIp = null,
        ?string $callingStationId = null,
    ): void {
        if (!$username) {
            return;
        }

        try {
            DB::transaction(function () use (
                $username,
                $acctSessionId,
                $cause,
                $causeId,
                $framedIp,
                $nasIp,
                $callingStationId,
            ) {
                $query = RadiusAccounting::query()
                    ->where('username', $username)
                    ->whereNull('acct_stop_time');

                if ($acctSessionId) {
                    $query->where('acct_session_id', $acctSessionId);
                }

                $query->orderBy('id', 'desc')->limit(20)->update([
                    'acct_stop_time' => now(),
                    'acct_terminate_cause' => $cause,
                    'terminate_cause_id' => $causeId,
                    'updated_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            Log::warning('Accounting terminate write gagal', ['u' => $username, 'err' => $e->getMessage()]);
        }
    }

    private function writeAudit(string $event, $subject, ?User $operator, array $meta): void
    {
        try {
            $row = [
                'auditable_type' => $subject ? get_class($subject) : null,
                'auditable_id' => $subject?->id,
                'event' => $event,
                'old_values' => $subject?->getOriginal(),
                'new_values' => $meta,
                'user_id' => $operator?->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];
            if (function_exists('audit_log_create')) {
                audit_log_create($row);
            } else {
                AuditLog::create($row);
            }
        } catch (Throwable $e) {
            Log::warning('Kick session audit log gagal', ['err' => $e->getMessage()]);
        }
    }
}
