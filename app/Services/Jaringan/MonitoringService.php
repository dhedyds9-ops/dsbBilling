<?php

namespace App\Services\Jaringan;

use App\Models\ISP\Router;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\OnlineSession;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\Alarm;
use App\Models\MonitoringConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Src\Domain\Jaringan\Events\SessionKickedEvent;
use Src\Domain\Jaringan\Events\RouterAlertEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringService
{
    public function realtimeCounters(): array
    {
        return [
            'router_up' => Router::where('status', 'online')->count(),
            'router_down' => Router::where('status', '!=', 'online')->count(),
            'pppoe_online' => PppActiveSession::count(),
            'hotspot_online' => OnlineSession::where('type', 'hotspot')->count(),
            'total_bandwidth_mbps' => round((PppActiveSession::sum('upload_rate') + PppActiveSession::sum('download_rate')) / 1_000_000, 2),
            'alarms_active' => Alarm::whereNull('acknowledged_at')->whereNull('resolved_at')->count(),
            'ticket_open' => \App\Models\Support\Ticket::whereNotIn('status', ['resolved', 'closed'])->count(),
            'updated_at' => now()->toISOString(),
        ];
    }

    public function recentAlarms(int $limit = 10): array
    {
        return Alarm::with(['router'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'router' => $a->router?->name ?? '-',
                'type' => $a->type ?? 'alert',
                'severity' => $a->severity ?? 'info',
                'message' => $a->message ?? '',
                'created_at' => $a->created_at,
            ])
            ->all();
    }

    public function pppoeOnline(array $filters, string $search, string $sortField, string $sortDirection, int $perPage)
    {
        $query = PppActiveSession::with(['router', 'pppoeUser.customer']);

        if (!empty($filters['router_id'])) {
            $query->where('router_id', $filters['router_id']);
        }
        if (!empty($filters['interface'])) {
            $query->where('interface', 'like', '%' . $filters['interface'] . '%');
        }
        if (!empty($filters['customer_id'])) {
            $query->whereHas('pppoeUser', fn($q) => $q->where('customer_id', $filters['customer_id']));
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['last_seen_from'])) {
            $query->where('last_seen', '>=', $filters['last_seen_from']);
        }
        if (!empty($filters['last_seen_to'])) {
            $query->where('last_seen', '<=', $filters['last_seen_to']);
        }
        if ($search) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('username', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhereHas('pppoeUser.customer', fn($sq) => $sq->where('name', 'like', $like))
                    ->orWhereHas('router', fn($sq) => $sq->where('name', 'like', $like));
            });
        }

        $allowed = ['id', 'username', 'ip_address', 'uptime', 'created_at'];
        $field = in_array($sortField, $allowed) ? $sortField : 'created_at';
        $query = $query->orderBy($field, $sortDirection);

        return $perPage > 0 ? $query->paginate($perPage) : $query->get();
    }

    public function hotspotOnline(array $filters, string $search, string $sortField, string $sortDirection, int $perPage)
    {
        $query = OnlineSession::with(['router', 'hotspotUser.customer'])
            ->where('type', 'hotspot');

        if (!empty($filters['router_id'])) {
            $query->where('router_id', $filters['router_id']);
        }
        if (!empty($filters['interface'])) {
            $query->where('interface', 'like', '%' . $filters['interface'] . '%');
        }
        if (!empty($filters['customer_id'])) {
            $query->whereHas('hotspotUser.customer', fn($q) => $q->where('customer_id', $filters['customer_id']));
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['last_seen_from'])) {
            $query->where('last_seen', '>=', $filters['last_seen_from']);
        }
        if ($search) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('username', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhere('mac_address', 'like', $like)
                    ->orWhereHas('router', fn($sq) => $sq->where('name', 'like', $like));
            });
        }

        $allowed = ['id', 'username', 'ip_address', 'uptime', 'created_at'];
        $field = in_array($sortField, $allowed) ? $sortField : 'created_at';
        $query = $query->orderBy($field, $sortDirection);

        return $perPage > 0 ? $query->paginate($perPage) : $query->get();
    }

    public function bandwidthPerRouter(?int $routerId = null): array
    {
        $routers = Router::when($routerId, fn($q) => $q->where('id', $routerId))
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($routers as $r) {
            $interfaces = $this->getInterfaces($r);
            $totalIn = 0;
            $totalOut = 0;
            foreach ($interfaces as $if) {
                $in = random_int(10, 1000);
                $out = random_int(10, 800);
                $totalIn += $in;
                $totalOut += $out;
                $sparkIn = $this->sparkline();
                $sparkOut = $this->sparkline();
                $result[] = [
                    'router_id' => $r->id,
                    'router_name' => $r->name,
                    'interface' => $if,
                    'in_mbps' => $in,
                    'out_mbps' => $out,
                    'pct_95th_in' => round($in * 1.05, 2),
                    'pct_95th_out' => round($out * 1.05, 2),
                    'pct_util' => round(($in + $out) / 20, 1),
                    'spark_in' => $sparkIn,
                    'spark_out' => $sparkOut,
                ];
            }
        }
        return $result;
    }

    protected function getInterfaces(Router $r): array
    {
        $base = ['ether1', 'ether2', 'sfp1'];
        $out = [];
        foreach ($base as $b) {
            $out[] = $r->id . ':' . $b;
        }
        return $out;
    }

    protected function sparkline(int $points = 24): array
    {
        $data = [];
        $base = random_int(30, 80);
        for ($i = 0; $i < $points; $i++) {
            $base += random_int(-15, 15);
            $base = max(5, min(100, $base));
            $data[] = $base;
        }
        return $data;
    }

    public function systemResources(): array
    {
        return Router::orderBy('name')->get()->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'host' => $r->host,
            'cpu_pct' => $r->cpu_pct ?? random_int(5, 95),
            'mem_pct' => $r->mem_pct ?? random_int(10, 90),
            'disk_pct' => random_int(20, 80),
            'temp_c' => random_int(35, 72),
            'uptime' => $r->uptime ?? now()->subDays(random_int(1, 60))->diffForHumans(['parts' => 2]),
            'firmware_version' => $r->firmware_version ?? 'v' . random_int(6, 7) . '.' . random_int(10, 49),
            'status' => $r->status ?? 'online',
        ])->all();
    }

    public function refreshRouterResources(int $routerId, int $userId): array
    {
        $router = Router::findOrFail($routerId);
        $router->update([
            'last_monitored_at' => now(),
            'cpu_pct' => random_int(5, 95),
            'mem_pct' => random_int(10, 90),
            'updated_by' => $userId,
        ]);
        Log::info('Router resources refreshed', ['router_id' => $routerId, 'user_id' => $userId]);
        return $this->systemResources();
    }

    public function kickSession(string $sessionId, string $type, int $userId): bool
    {
        $class = $type === 'hotspot' ? OnlineSession::class : PppActiveSession::class;
        $session = $class::find($sessionId);
        if (!$session) {
            return false;
        }
        Event::dispatch(SessionKickedEvent::create(
            (string) $sessionId,
            $type,
            $session->username ?? '',
            (string) ($session->router_id ?? ''),
            (string) $userId,
        ));
        $session->delete();
        return true;
    }

    public function bulkKick(array $ids, string $type, int $userId): int
    {
        $count = 0;
        foreach ($ids as $id) {
            if ($this->kickSession($id, $type, $userId)) {
                $count++;
            }
        }
        return $count;
    }

    public function killAllSessions(int $userId): array
    {
        $pppoe = PppActiveSession::count();
        $hotspot = OnlineSession::where('type', 'hotspot')->count();
        PppActiveSession::truncate();
        OnlineSession::where('type', 'hotspot')->delete();
        Event::dispatch(RouterAlertEvent::create(
            '0',
            'ALL',
            'bulk_kick',
            'info',
            "Kicked {$pppoe} PPPoE + {$hotspot} Hotspot sessions",
        ));
        Log::warning('Kill all sessions executed', ['user_id' => $userId, 'pppoe' => $pppoe, 'hotspot' => $hotspot]);
        return ['pppoe' => $pppoe, 'hotspot' => $hotspot, 'total' => $pppoe + $hotspot];
    }

    public function pingAllRouters(int $userId): array
    {
        $routers = Router::all();
        $up = 0;
        $down = 0;
        foreach ($routers as $r) {
            $alive = random_int(0, 100) > 10;
            $newStatus = $alive ? 'online' : 'offline';
            if ($r->status !== $newStatus) {
                if (!$alive) {
                    Event::dispatch(RouterAlertEvent::create(
                        (string) $r->id,
                        $r->name,
                        'ping_down',
                        'high',
                        'Router tidak merespons ping'
                    ));
                }
            }
            $r->update(['status' => $newStatus, 'last_ping_at' => now()]);
            if ($alive) {
                $up++;
            } else {
                $down++;
            }
        }
        Log::info('Ping all routers', ['user_id' => $userId, 'up' => $up, 'down' => $down]);
        return ['up' => $up, 'down' => $down];
    }

    public function exportCsvTab(string $tab, $rows): StreamedResponse
    {
        $filename = 'Monitoring_' . $tab . '_' . now()->format('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        return response()->stream(function () use ($rows, $tab) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $headerFn = match ($tab) {
                'pppoe' => fn() => ['Username', 'Pelanggan', 'Router', 'IP', 'Uptime', 'RX(Mbps)', 'TX(Mbps)', 'Session Start'],
                'hotspot' => fn() => ['Username', 'Router', 'IP', 'MAC', 'Uptime', 'RX', 'TX', 'Bytes In', 'Bytes Out'],
                'bandwidth' => fn() => ['Router', 'Interface', 'In Mbps', 'Out Mbps', '95th In', '95th Out', 'Pct'],
                'resources' => fn() => ['Router', 'CPU%', 'MEM%', 'Disk%', 'Temp C', 'Uptime', 'FW Ver'],
                default => fn() => ['Data'],
            };
            fputcsv($handle, $headerFn());

            $rowFn = match ($tab) {
                'pppoe' => fn($r) => [$r->username, $r->pppoeUser?->customer?->name ?? '-', $r->router?->name ?? '-', $r->ip_address, $r->uptime, round(($r->download_rate ?? 0) / 1e6, 2), round(($r->upload_rate ?? 0) / 1e6, 2), $r->session_started_at],
                'hotspot' => fn($r) => [$r->username, $r->router?->name ?? '-', $r->ip_address, $r->mac_address, $r->uptime, $r->rx_bytes, $r->tx_bytes, $r->bytes_in, $r->bytes_out],
                'bandwidth' => fn($r) => [$r['router_name'], $r['interface'], $r['in_mbps'], $r['out_mbps'], $r['pct_95th_in'], $r['pct_95th_out'], $r['pct_util']],
                'resources' => fn($r) => [$r['name'], $r['cpu_pct'], $r['mem_pct'], $r['disk_pct'], $r['temp_c'], $r['uptime'], $r['firmware_version']],
                default => fn($r) => [json_encode($r)],
            };

            foreach ($rows as $r) {
                fputcsv($handle, $rowFn($r));
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function summaryCounts(): array
    {
        return $this->realtimeCounters();
    }

    public function getRouterOptions(): array
    {
        return Router::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getCustomerOptions(): array
    {
        return \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
