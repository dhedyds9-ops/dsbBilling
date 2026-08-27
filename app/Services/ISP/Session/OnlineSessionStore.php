<?php

namespace App\Services\ISP\Session;

use App\Models\ISP\HotspotUser;
use App\Models\ISP\OnlineSession;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\RadiusAccounting;
use App\Models\ISP\Router;
use App\Support\UptimeParser;
use Illuminate\Support\Facades\Log;
use Throwable;

class OnlineSessionStore
{
    public function upsertPppoeFromRouter(Router $router, array $sessions, ?string $source = 'router_poller'): array
    {
        $stored = [];
        $keys = [];
        $now = now();
        foreach ($sessions as $s) {
            $name = (string)($s['name'] ?? '');
            $caller = (string)($s['caller_id'] ?? '');
            $address = (string)($s['address'] ?? '');
            if ($name === '' && $caller === '' && $address === '') {
                continue;
            }

            $key = OnlineSession::buildSessionKey('pppoe', $router->ip_address, $name, $caller, $address);
            $keys[] = $key;

            $startedAt = !empty($s['session_started_at']) && is_string($s['session_started_at'])
                ? \Illuminate\Support\Carbon::parse($s['session_started_at'])
                : (!empty($s['uptime']) ? UptimeParser::toDateTime((string)$s['uptime']) : null);

            try {
                $pppoeId = null;
                $customerServiceId = null;
                if ($name !== '') {
                    $u = PPPoEUser::query()->where('username', $name)->first(['id', 'customer_service_id']);
                    if ($u) {
                        $pppoeId = $u->id;
                        $customerServiceId = $u->customer_service_id;
                    }
                }

                $stored[] = OnlineSession::query()->updateOrCreate(
                    ['session_key' => $key],
                    [
                        'protocol' => 'pppoe',
                        'username' => $name,
                        'router_id' => $router->id,
                        'nas_device_id' => $router->nas_device_id ?? null,
                        'pppoe_user_id' => $pppoeId,
                        'customer_service_id' => $customerServiceId,
                        'service' => $s['service'] ?? null,
                        'caller_id' => $caller,
                        'mac_address' => $caller,
                        'address' => $address,
                        'uptime' => $s['uptime'] ?? null,
                        'bytes_in' => (int)($s['bytes_in'] ?? 0),
                        'bytes_out' => (int)($s['bytes_out'] ?? 0),
                        'packets_in' => (int)($s['packets_in'] ?? 0),
                        'packets_out' => (int)($s['packets_out'] ?? 0),
                        'rate_up' => $s['rate_up'] ?? null,
                        'rate_down' => $s['rate_down'] ?? null,
                        'source' => $source,
                        'session_started_at' => $startedAt,
                        'last_seen_at' => $now,
                    ]
                );
            } catch (Throwable $e) {
                Log::warning('OnlineSession PPPoE upsert gagal', ['name' => $name, 'err' => $e->getMessage()]);
            }
        }

        $this->removeStale('pppoe', $router, $keys);

        return $stored;
    }

    public function upsertHotspotFromRouter(Router $router, array $sessions, ?string $source = 'router_poller'): array
    {
        $stored = [];
        $keys = [];
        $now = now();
        foreach ($sessions as $s) {
            $user = (string)($s['user'] ?? '');
            $mac = (string)($s['mac_address'] ?? '');
            $address = (string)($s['address'] ?? '');
            $server = (string)($s['server'] ?? '');
            if ($user === '' && $mac === '') {
                continue;
            }

            $key = OnlineSession::buildSessionKey('hotspot', $router->ip_address, $user, $mac, $address . '|' . $server);
            $keys[] = $key;

            $startedAt = !empty($s['session_started_at']) && is_string($s['session_started_at'])
                ? \Illuminate\Support\Carbon::parse($s['session_started_at'])
                : (!empty($s['uptime']) ? UptimeParser::toDateTime((string)$s['uptime']) : null);

            try {
                $hotspotId = null;
                $customerServiceId = null;
                if ($user !== '') {
                    $u = HotspotUser::query()->where('username', $user)->first(['id', 'customer_service_id']);
                    if ($u) {
                        $hotspotId = $u->id;
                        $customerServiceId = $u->customer_service_id;
                    }
                }

                $stored[] = OnlineSession::query()->updateOrCreate(
                    ['session_key' => $key],
                    [
                        'protocol' => 'hotspot',
                        'username' => $user,
                        'router_id' => $router->id,
                        'nas_device_id' => $router->nas_device_id ?? null,
                        'hotspot_user_id' => $hotspotId,
                        'customer_service_id' => $customerServiceId,
                        'caller_id' => $mac,
                        'mac_address' => $mac,
                        'address' => $address,
                        'server' => $server,
                        'login_by' => $s['login_by'] ?? null,
                        'uptime' => $s['uptime'] ?? null,
                        'bytes_in' => (int)($s['bytes_in'] ?? 0),
                        'bytes_out' => (int)($s['bytes_out'] ?? 0),
                        'source' => $source,
                        'session_started_at' => $startedAt,
                        'last_seen_at' => $now,
                    ]
                );
            } catch (Throwable $e) {
                Log::warning('OnlineSession Hotspot upsert gagal', ['user' => $user, 'err' => $e->getMessage()]);
            }
        }

        $this->removeStale('hotspot', $router, $keys);

        return $stored;
    }

    public function upsertFromAccounting(RadiusAccounting $acct): ?OnlineSession
    {
        $type = (string)$acct->acct_status_type;
        $proto = strtolower((string)($acct->framed_protocol ?? ''));
        $isHotspot = false;
        $isPppoe = false;
        if ($proto === 'ppp' || $proto === '1' || $proto === 'pppoe') {
            $isPppoe = true;
        } elseif ($proto === 'hotspot' || $proto === 'hs') {
            $isHotspot = true;
        } else {
            $isPppoe = (int)$acct->pppoe_user_id > 0;
            $isHotspot = (int)$acct->hotspot_user_id > 0;
            if (!$isPppoe && !$isHotspot) {
                $isPppoe = true;
            }
        }
        $protocol = $isHotspot ? 'hotspot' : 'pppoe';
        $nasIp = (string)($acct->nas_ip_address ?? '');
        $username = (string)($acct->username ?? '');
        $caller = (string)($acct->calling_station_id ?? '');
        $address = (string)($acct->framed_ip_address ?? '');
        $key = OnlineSession::buildSessionKey($protocol, $nasIp, $username, $caller, $address);

        if ($type === 'stop' || $type === 'accounting_off' || $type === 'failed') {
            try {
                OnlineSession::query()
                    ->where('session_key', $key)
                    ->orWhere(function ($q) use ($username, $acct) {
                        $q->where('username', $username)->where('acct_session_id', $acct->acct_session_id);
                    })
                    ->delete();
            } catch (Throwable $e) {
                Log::debug('Accounting remove online session', ['err' => $e->getMessage()]);
            }
            return null;
        }

        try {
            $startedAt = $acct->acct_start_time ?? now();
            return OnlineSession::query()->updateOrCreate(
                ['session_key' => $key],
                [
                    'protocol' => $protocol,
                    'username' => $username,
                    'nas_device_id' => $acct->nas_device_id,
                    'radius_nas_id' => $acct->radius_nas_id,
                    'pppoe_user_id' => $acct->pppoe_user_id,
                    'hotspot_user_id' => $acct->hotspot_user_id,
                    'customer_service_id' => $acct->customer_service_id,
                    'caller_id' => $caller,
                    'mac_address' => $caller,
                    'address' => $address,
                    'uptime' => (string)($acct->acct_session_time ?? ''),
                    'bytes_in' => (int)($acct->acct_input_octets ?? 0),
                    'bytes_out' => (int)($acct->acct_output_octets ?? 0),
                    'acct_session_id' => $acct->acct_session_id,
                    'source' => 'radius_accounting',
                    'session_started_at' => $startedAt,
                    'last_seen_at' => $acct->received_at ?? now(),
                ]
            );
        } catch (Throwable $e) {
            Log::warning('OnlineSession accounting upsert gagal', ['u' => $username, 'err' => $e->getMessage()]);
            return null;
        }
    }

    private function removeStale(string $protocol, Router $router, array $freshKeys): void
    {
        try {
            if (count($freshKeys) === 0) {
                OnlineSession::query()
                    ->where('protocol', $protocol)
                    ->where('router_id', $router->id)
                    ->where('source', 'router_poller')
                    ->delete();
                return;
            }
            OnlineSession::query()
                ->where('protocol', $protocol)
                ->where('router_id', $router->id)
                ->where('source', 'router_poller')
                ->whereNotIn('session_key', $freshKeys)
                ->delete();
        } catch (Throwable $e) {
            Log::warning('OnlineSession removeStale gagal', ['err' => $e->getMessage()]);
        }
    }
}
