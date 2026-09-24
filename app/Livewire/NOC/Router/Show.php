<?php

namespace App\Livewire\NOC\Router;

use App\Livewire\AdminComponent;
use App\Models\ISP\Router;
use App\Models\ISP\RouterMonitoringLog;
use App\Models\ISP\OnlineSession;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotActiveSession;
use App\Models\Alarm;
use App\Services\NOC\TrafficMonitoringService;
use App\Services\NOC\ImpactAnalysisService;
use Livewire\Attributes\Computed;

class Show extends AdminComponent
{
    public int $routerId;
    public string $activeTab = 'overview';
    public string $sessionSubTab = 'pppoe';
    public string $searchSession = '';
    public string $trafficPeriod = '1h';
    
    // Net Monitor (Live Interfaces) state
    public array $selectedInterfaces = [];
    public array $lastTrafficState = [];

    public function updatingSearchSession()
    {
        $this->resetPage();
    }

    public function configure(): void
    {
        }

    public function mount($router = null): void
    {
        parent::mount();
        $this->routerId     = (int) $router;
        $this->activeModule = 'noc';
        $this->activePage   = 'routers';
    }

    public function setTab(string $tab): void
    {
        $allowed = ['overview', 'sessions', 'health', 'traffic', 'alarms', 'interfaces', 'logs'];
        if (in_array($tab, $allowed)) {
            $this->activeTab = $tab;
        }
    }

    public function setSessionSubTab(string $sub): void
    {
        if (in_array($sub, ['pppoe', 'hotspot', 'all'])) {
            $this->sessionSubTab = $sub;
        }
    }

    public function setTrafficPeriod(string $period): void
    {
        if (in_array($period, ['15m', '1h', '6h', '24h'])) {
            $this->trafficPeriod = $period;
        }
    }

    #[Computed]
    public function router(): Router
    {
        return Router::withoutTrashed()
            ->with(['vendor:id,name', 'pop:id,name'])
            ->select([
                'id', 'name', 'code', 'model', 'ip_address', 'hostname',
                'status', 'last_seen_at', 'routeros_version',
                'vendor_id', 'pop_id', 'api_port', 'coa_port', 'username', 'password', 'use_ssl'
            ])
            ->findOrFail($this->routerId);
    }

    #[Computed]
    public function latestLog(): ?RouterMonitoringLog
    {
        return RouterMonitoringLog::where('router_id', $this->routerId)
            ->latest()
            ->select(['id', 'router_id', 'is_online', 'cpu_load', 'free_memory', 'total_memory', 'uptime', 'identity', 'version', 'error_message', 'created_at'])
            ->first();
    }

    #[Computed]
    public function healthHistory(): \Illuminate\Support\Collection
    {
        return RouterMonitoringLog::where('router_id', $this->routerId)
            ->select(['id', 'is_online', 'cpu_load', 'free_memory', 'total_memory', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function activeSessions(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = OnlineSession::where('router_id', $this->routerId);
        
        if (!empty($this->searchSession)) {
            $query->where(function($q) {
                $q->where('username', 'like', '%' . $this->searchSession . '%')
                  ->orWhere('address', 'like', '%' . $this->searchSession . '%')
                  ->orWhere('caller_id', 'like', '%' . $this->searchSession . '%');
            });
        }
        
        return $query->select([
            'id', 'protocol', 'username', 'address', 'caller_id',
                'uptime', 'rate_up', 'rate_down', 'session_started_at', 'last_seen_at',
            ])
            ->orderBy('session_started_at', 'desc')
            ->paginate(20);
    }

    #[Computed]
    public function pppSessions(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = PppActiveSession::where('router_id', $this->routerId);
        
        if (!empty($this->searchSession)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchSession . '%')
                  ->orWhere('address', 'like', '%' . $this->searchSession . '%')
                  ->orWhere('caller_id', 'like', '%' . $this->searchSession . '%');
            });
        }
        
        return $query->select(['id', 'name', 'service', 'caller_id', 'address', 'uptime', 'bytes_in', 'bytes_out', 'rate_up', 
'rate_down', 'session_started_at'])
            ->orderByDesc('session_started_at')
            ->paginate(25);
    }

    #[Computed]
    public function hotspotSessions(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = HotspotActiveSession::where('router_id', $this->routerId);
        
        if (!empty($this->searchSession)) {
            $query->where(function($q) {
                $q->where('user', 'like', '%' . $this->searchSession . '%')
                  ->orWhere('address', 'like', '%' . $this->searchSession . '%')
                  ->orWhere('mac_address', 'like', '%' . $this->searchSession . '%');
            });
        }
        
        return $query->select(['id', 'user', 'mac_address', 'address', 'server', 'login_by', 'uptime', 'bytes_in', 
'bytes_out', 'session_started_at'])
            ->orderByDesc('session_started_at')
            ->paginate(25);
    }

    #[Computed]
    public function sessionCounts(): array
    {
        return [
            'pppoe'   => PppActiveSession::where('router_id', $this->routerId)->count(),
            'hotspot' => HotspotActiveSession::where('router_id', $this->routerId)->count(),
            'all'     => OnlineSession::where('router_id', $this->routerId)->count(),
        ];
    }

    #[Computed]
    public function trafficData(): array
    {
        return app(TrafficMonitoringService::class)->getTrafficSeries(
            $this->trafficPeriod,
            'router',
            $this->routerId,
        );
    }

    #[Computed]
    public function activeAlarms(): \Illuminate\Database\Eloquent\Collection
    {
        $orderBy = \App\Services\Support\DbCompat::fieldOrder('level', ['critical', 'warning', 'info']);
        return Alarm::where('status', 'open')
            ->where('source_type', Router::class)
            ->where('source_id', $this->routerId)
            ->orderByRaw($orderBy)
            ->orderBy('started_at', 'desc')
            ->get(['id', 'level', 'title', 'description', 'started_at', 'status']);
    }

    #[Computed]
    public function impact(): array
    {
        return app(ImpactAnalysisService::class)->analyzeRouterImpact($this->routerId);
    }

    #[Computed]
    public function liveInterfaces(): array
    {
        if ($this->activeTab !== 'interfaces') return [];
        $stats = app(\App\Services\Adapters\Monitoring\MikroTikDriver::class)->getInterfaceStats($this->router);
        
        $now = microtime(true);
        $result = [];
        $newLastTraffic = [];
        
                foreach ($stats as $iface) {
            $name = $iface['name'];
            $rx = (int) ($iface['rx-byte'] ?? 0);
            $tx = (int) ($iface['tx-byte'] ?? 0);
            
            // Prefer Mikrotik's native monitor-traffic bps (matches Winbox exactly)
            $rx_bps = isset($iface['rx-bps']) && $iface['rx-bps'] > 0 ? (float) $iface['rx-bps'] : 0;
            $tx_bps = isset($iface['tx-bps']) && $iface['tx-bps'] > 0 ? (float) $iface['tx-bps'] : 0;
            
            // Fallback manual calculation only if native is 0
            if ($rx_bps == 0 && $tx_bps == 0 && isset($this->lastTrafficState[$name])) {
                $prev = $this->lastTrafficState[$name];
                $timeDiff = $now - $prev['time'];
                if ($timeDiff > 0) {
                    $rx_bps = max(0, ($rx - $prev['rx']) * 8 / $timeDiff);
                    $tx_bps = max(0, ($tx - $prev['tx']) * 8 / $timeDiff);
                }
            }
            
            $newLastTraffic[$name] = [
                'time' => $now,
                'rx' => $rx,
                'tx' => $tx,
            ];
            
            $iface['rx_bps'] = $rx_bps;
            $iface['tx_bps'] = $tx_bps;
            $result[] = $iface;
        }
        
        $this->lastTrafficState = $newLastTraffic;
        
        $this->dispatch('traffic-updated', traffic: $result);
        
        return $result;
    }

    public function toggleInterfaceSelection(string $name): void
    {
        if (in_array($name, $this->selectedInterfaces)) {
            $this->selectedInterfaces = array_values(array_filter($this->selectedInterfaces, fn($n) => $n !== $name));
        } else {
            $this->selectedInterfaces[] = $name;
        }
    }

    #[Computed]
    public function liveLogs(): array
    {
        if ($this->activeTab !== 'logs') return [];
        return app(\App\Services\Adapters\Monitoring\MikroTikDriver::class)->getLogs($this->router, 30);
    }

    public function rebootRouter(): void
    {
        try {
            if (app(\App\Services\Adapters\Monitoring\MikroTikDriver::class)->rebootRouter($this->router)) {
                $this->dispatch('toast', type: 'success', message: 'Reboot command sent to router.');
            } else {
                $this->dispatch('toast', type: 'error', message: 'Failed to send reboot command.');
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function getRouterStatus(): string
    {
        $log = $this->latestLog;
        if (!$log) return 'UNKNOWN';
        if (!$log->is_online) return 'OFFLINE';
        if ($log->created_at->diffInMinutes(now()) > 5) return 'STALE';
        if ($log->cpu_load > 80) return 'WARNING';
        return 'ONLINE';
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.router.show', [
            'router'          => $this->router,
            'log'             => $this->latestLog,
            'history'         => $this->activeTab === 'health' ? $this->healthHistory : collect(),
            'sessions'        => $this->activeTab === 'sessions' ? $this->activeSessions : null,
            'pppSessions'     => $this->activeTab === 'sessions' ? $this->pppSessions : null,
            'hotspotSessions' => $this->activeTab === 'sessions' ? $this->hotspotSessions : null,
            'sessionCounts'   => $this->activeTab === 'sessions' ? $this->sessionCounts : ['pppoe' => 0, 'hotspot' => 0, 'all' => 0],
            'traffic'         => $this->activeTab === 'traffic' ? $this->trafficData : [],
            'alarms'          => $this->activeAlarms,
            'impact'          => $this->impact,
            'status'          => $this->getRouterStatus(),
            'liveInterfaces'  => $this->liveInterfaces,
            'liveLogs'        => $this->liveLogs,
        ]);
    }
}







