{{--
 NOC: Router Detail View
 Tabs: Overview | Sessions | Health | Traffic | Alarms | Interfaces | Logs
 Polling: 60 seconds
--}}
<div class="h-full flex flex-col overflow-hidden noc-bg" wire:poll.60000ms>

<div class="flex-none px-3 py-2 border-b noc-border flex items-center gap-3 flex-wrap noc-panel-bg">
    <div class="flex items-center gap-2">
        <a href="{{ route('noc.routers.index') }}" class="noc-muted hover:noc-text-secondary text-sm"><i class="bi bi-arrow-left"></i></a>
        <h1 class="text-base font-bold noc-text">{{ $router->name }}</h1>
        <span class="text-xs noc-muted noc-mono">{{ $router->code ?? '' }}</span>
        <x-noc.stat-badge :status="$status" />
    </div>
    <div class="flex items-center gap-3 text-xs ml-auto">
        <span class="noc-muted">IP: <span class="noc-mono noc-text-secondary">{{ $router->ip_address }}</span></span>
        <span class="noc-muted">API: <span class="noc-mono noc-text-secondary">{{ $router->api_port ?? '-' }}</span></span>
        <span class="noc-muted">Model: <span class="noc-text-secondary">{{ $router->vendor->name ?? '-' }} {{ $router->model ?? '' }}</span></span>
        <span class="noc-muted">RouterOS: <span class="noc-mono noc-text-secondary">{{ $router->routeros_version ?? '-' }}</span></span>
        
        <button wire:click="rebootRouter" wire:confirm="Are you sure you want to reboot this router ?? " class="btn btn-sm btn-danger ml-2 py-0.5 px-2 text-xs">
            <i class="bi bi-power"></i> Reboot
        </button>
    </div>
</div>

{{-- Tabs --}}
<div class="flex-none px-3 border-b noc-border flex items-center gap-1 overflow-x-auto noc-scroll noc-panel-bg">
    @foreach(['overview' => 'Overview', 'interfaces' => 'Net Monitor', 'sessions' => 'Active Sessions', 'health' => 'Health History', 'traffic' => 'Traffic', 'alarms' => 'Alarms', 'logs' => 'Logs'] as $k => $l)
    <button wire:click="setTab('{{ $k }}')"
        class="px-3 py-2 text-xs whitespace-nowrap border-b-2 transition-colors
               {{ $activeTab === $k ? 'noc-tab-active' : 'noc-tab-inactive' }}">
        {{ $l }}
    </button>
    @endforeach
</div>

{{-- Content --}}
<div class="flex-1 overflow-hidden noc-scroll">

    @if($activeTab === 'overview')
    <div class="p-3 flex gap-3 overflow-auto noc-scroll h-full">
        <x-noc.card class="flex-none w-80">
            <x-slot name="header">
                <span class="text-xs font-bold noc-text-secondary uppercase tracking-widest">Device Info</span>
            </x-slot>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Code</dt><dd class="noc-mono noc-text-secondary">{{ $router->code ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Hostname</dt><dd class="noc-text-secondary">{{ $router->hostname ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">IP Address</dt><dd class="noc-mono noc-text-secondary">{{ $router->ip_address }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">API Port</dt><dd class="noc-mono noc-text-secondary">{{ $router->api_port ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">COA Port</dt><dd class="noc-mono noc-text-secondary">{{ $router->coa_port ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Model</dt><dd class="noc-text-secondary">{{ $router->model ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Vendor</dt><dd class="noc-text-secondary">{{ $router->vendor->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">POP</dt><dd class="noc-text-secondary">{{ $router->pop->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">RouterOS</dt><dd class="noc-mono noc-text-secondary">{{ $router->routeros_version ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Last Seen</dt><dd class="noc-text-secondary">{{ $router->last_seen_at ? $router->last_seen_at->diffForHumans() : '-' }}</dd></div>
            </dl>
        </x-noc.card>

        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3 content-start">
            <x-noc.card>
                <div class="noc-summary-label">CPU Load</div>
                <div class="noc-summary-value noc-mono {{ $log && $log?->cpu_load > 80 ? 'text-red-500' : 'text-emerald-500' }}">{{ $log?->cpu_load ?? '-' }}<span class="text-sm">%</span></div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">Free Memory</div>
                <div class="noc-summary-value noc-mono text-blue-500">
                    @if($log && $log?->total_memory)
                        {{ round($log?->free_memory / 1024 / 1024- 1) }}<span class="text-sm"> MB</span>
                    @else - @endif
                </div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">Uptime</div>
                <div class="text-xl font-bold noc-text noc-mono" style="font-size:1.1rem;">{{ $log?->uptime ?? '-' }}</div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">Identity</div>
                <div class="text-sm font-bold noc-text truncate" title="{{ $log?->identity }}">{{ $log?->identity ?? '-' }}</div>
            </x-noc.card>
        </div>
        
        @if(($impact['customer_count'] ?? 0) > 0)
        <x-noc.card class="flex-none w-80 border-red-500">
            <x-slot name="header">
                <span class="text-xs font-bold text-red-500 uppercase tracking-widest"><i class="bi bi-exclamation-triangle-fill"></i> Impact if Offline</span>
            </x-slot>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Customers</dt><dd class="noc-mono text-red-500 font-bold">{{ $impact['customer_count'] }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Services</dt><dd class="noc-mono text-red-500 font-bold">{{ $impact['service_count'] }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">PPPoE</dt><dd class="noc-mono noc-text-secondary">{{ $impact['breakdown']['pppoe'] ?? 0 }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Hotspot</dt><dd class="noc-mono noc-text-secondary">{{ $impact['breakdown']['hotspot'] ?? 0 }}</dd></div>
            </dl>
        </x-noc.card>
        @endif
    </div>
    @endif

    @if($activeTab === 'interfaces')
    <div class="h-full flex flex-col md:flex-row bg-[#0b1120]" wire:poll.2s>
        <!-- Sidebar: List of interfaces -->
        <div class="w-full md:w-64 border-r border-gray-800 flex flex-col h-full bg-[#111827]">
            <div class="p-3 border-b border-gray-800 flex justify-between items-center bg-[#1e293b]">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest"><i class="bi bi-diagram-3 mr-2"></i> Interfaces</h3>
            </div>
            <div class="flex-1 overflow-y-auto noc-scroll">
                @foreach($liveInterfaces as $iface)
                <div wire:click="toggleInterfaceSelection('{{ $iface['name'] }}')" class="p-2 border-b border-gray-800 cursor-pointer hover:bg-gray-800 flex justify-between items-center transition-colors {{ in_array($iface['name'], $selectedInterfaces) ? 'bg-gray-800 border-l-2 border-blue-500' : 'border-l-2 border-transparent' }}">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $iface['status'] === 'link-up' ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                        <div>
                            <div class="text-xs font-bold text-gray-200">{{ $iface['name'] }}</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ $iface['type'] }}</div>
                        </div>
                    </div>
                    @if(in_array($iface['name'], $selectedInterfaces))
                        <span class="text-[9px] bg-blue-500/20 text-blue-400 px-1.5 rounded">MONITOR</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Main Panel: Graphs -->
        <div class="flex-1 flex flex-col bg-[#0f172a] overflow-hidden" >
            <div class="p-3 border-b border-gray-800 flex justify-between items-center bg-[#1e293b]">
                <div class="flex gap-4">
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Router</div>
                        <div class="text-sm font-bold text-gray-200"><i class="bi bi-router mr-1 text-blue-500"></i> {{ $router->hostname ?? $router->name }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">IP</div>
                        <div class="text-sm font-bold text-gray-200">{{ $router->ip_address }}</div>
                    </div>
                </div>
                <div class="flex gap-4 text-right">
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Total Interfaces</div>
                        <div class="text-sm font-bold text-emerald-400">{{ count($liveInterfaces) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto noc-scroll p-4 space-y-4">
                @if(empty($selectedInterfaces))
                    <div class="h-full flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-activity text-4xl mb-3 text-gray-700 dark:text-gray-300"></i>
                        <p class="text-sm">Pilih satu atau lebih interface dari sidebar untuk memonitor traffic.</p>
                    </div>
                @endif
                
                @foreach($selectedInterfaces as $ifaceName)
                    @php
                        $stat = collect($liveInterfaces)->firstWhere('name', $ifaceName);
                        $rx = $stat['rx_bps'] ?? 0;
                        $tx = $stat['tx_bps'] ?? 0;
                        
                        if (!function_exists('formatBps')) {
                            function formatBps($bps) {
                                if ($bps >= 1000000) return round($bps / 1000000, 2) . ' Mbps';
                                if ($bps >= 1000) return round($bps / 1000, 2) . ' Kbps';
                                return round($bps) . ' bps';
                            }
                        }
                    @endphp
                    <div class="bg-[#111827] border border-gray-800 rounded-lg p-3">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded text-xs font-bold font-mono"># {{ $ifaceName }}</span>
                            </div>
                            <div class="flex gap-4 text-xs font-mono">
                                <div class="text-emerald-400" id="rx-label-{{ md5($ifaceName) }}">RX (In): {{ formatBps($rx) }}</div>
                                <div class="text-blue-400" id="tx-label-{{ md5($ifaceName) }}">TX (Out): {{ formatBps($tx) }}</div>
                            </div>
                        </div>
                        
                        <div class="w-full h-[200px]" id="chart-{{ md5($ifaceName) }}" data-iface="{{ $ifaceName }}" wire:ignore></div>
                        
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
        
            @script
<script>
    window.nocSeriesData = window.nocSeriesData || {};

    const formatBps = (bps) => {
        if (bps >= 1000000) return (bps / 1000000).toFixed(2) + ' Mbps';
        if (bps >= 1000) return (bps / 1000).toFixed(2) + ' Kbps';
        return Math.round(bps) + ' bps';
    };

    Livewire.on('traffic-updated', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const trafficList = payload.traffic || [];
        const time = new Date().getTime();
        
        if (typeof window.echarts === 'undefined') {
            if (!window.loadingEcharts) {
                window.loadingEcharts = true;
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js';
                document.head.appendChild(script);
            }
            return;
        }

        const chartDivs = document.querySelectorAll('[id^="chart-"]');
        chartDivs.forEach(el => {
            const id = el.id;
            const ifaceName = el.getAttribute('data-iface');
            const stat = trafficList.find(i => i.name === ifaceName);
            if (!stat) return;
            
            const rx = stat.rx_bps || 0;
            const tx = stat.tx_bps || 0;
            
            const rxLabel = document.getElementById(id.replace('chart-', 'rx-label-'));
            const txLabel = document.getElementById(id.replace('chart-', 'tx-label-'));
            if (rxLabel) rxLabel.innerText = 'RX (In): ' + formatBps(rx);
            if (txLabel) txLabel.innerText = 'TX (Out): ' + formatBps(tx);
            
            // Get the ECharts instance attached to this exact DOM element
            let chartInstance = window.echarts.getInstanceByDom(el);
            
            if (!chartInstance) {
                // This DOM element is new (maybe tab was switched or Livewire replaced it)
                chartInstance = window.echarts.init(el, 'dark');
            }
            
            if (!window.nocSeriesData[id]) {
                window.nocSeriesData[id] = { rx: [], tx: [], times: [] };
            }
            
            let dt = new Date(time);
            let timeStr = dt.getHours().toString().padStart(2, '0') + ':' + dt.getMinutes().toString().padStart(2, '0') + ':' + dt.getSeconds().toString().padStart(2, '0');
            
            window.nocSeriesData[id].rx.push(rx);
            window.nocSeriesData[id].tx.push(tx);
            window.nocSeriesData[id].times.push(timeStr);
            
            if (window.nocSeriesData[id].rx.length > 60) {
                window.nocSeriesData[id].rx.shift();
                window.nocSeriesData[id].tx.shift();
                window.nocSeriesData[id].times.shift();
            }
            
            const option = {
                backgroundColor: 'transparent',
                tooltip: { trigger: 'axis' },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', boundaryGap: false, data: window.nocSeriesData[id].times, axisLine: { show: false }, splitLine: { show: false } },
                yAxis: { type: 'value', axisLabel: { formatter: (val) => formatBps(val) }, splitLine: { lineStyle: { color: '#1f2937', type: 'dashed' } } },
                series: [
                    {
                        name: 'RX (In)',
                        type: 'line',
                        smooth: true,
                        showSymbol: false,
                        lineStyle: { color: '#34d399', width: 2 },
                        areaStyle: { color: new window.echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(52, 211, 153, 0.2)' }, { offset: 1, color: 'rgba(52, 211, 153, 0)' }]) },
                        data: window.nocSeriesData[id].rx
                    },
                    {
                        name: 'TX (Out)',
                        type: 'line',
                        smooth: true,
                        showSymbol: false,
                        lineStyle: { color: '#60a5fa', width: 2 },
                        areaStyle: { color: new window.echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(96, 165, 250, 0.2)' }, { offset: 1, color: 'rgba(96, 165, 250, 0)' }]) },
                        data: window.nocSeriesData[id].tx
                    }
                ]
            };
            
            chartInstance.setOption(option);
        });
    });
</script>
@endscript
    @endif

    @if($activeTab === 'logs')
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold w-32" style="border-color: var(--noc-border); color: var(--noc-muted);">Time</th>
                        <th class="p-3 font-semibold w-32" style="border-color: var(--noc-border); color: var(--noc-muted);">Topics</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($liveLogs as $logItem)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  font-mono noc-muted whitespace-nowrap">{{ $logItem['time'] ?? '-' }}</td>
                        <td class="p-3  font-mono noc-muted">{{ $logItem['topics'] ?? '-' }}</td>
                        <td class="p-3  noc-text">{{ $logItem['message'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="3" class="p-3  text-center noc-muted">No logs found or router offline.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
    </div>
    @endif

    @if($activeTab === 'sessions')
    <div class="p-3 h-full overflow-hidden flex flex-col gap-2">
        <div class="flex-none flex items-center gap-2 border-b noc-border pb-2">
            <button wire:click="setSessionSubTab('pppoe')"
                class="px-3 py-1 text-xs rounded border transition-colors {{ $sessionSubTab === 'pppoe' ? 'border-blue-500 text-blue-400 bg-blue-500/10' : 'border-gray-700 noc-muted hover:noc-text-secondary' }}">
                PPPoE Active <span class="ml-1 font-mono font-bold">{{ $sessionCounts['pppoe'] }}</span>
            </button>
            <button wire:click="setSessionSubTab('hotspot')"
                class="px-3 py-1 text-xs rounded border transition-colors {{ $sessionSubTab === 'hotspot' ? 'border-emerald-500 text-emerald-400 bg-emerald-500/10' : 'border-gray-700 noc-muted hover:noc-text-secondary' }}">
                Hotspot Active <span class="ml-1 font-mono font-bold">{{ $sessionCounts['hotspot'] }}</span>
            </button>
            <button wire:click="setSessionSubTab('all')"
                class="px-3 py-1 text-xs rounded border transition-colors {{ $sessionSubTab === 'all' ? 'border-amber-500 text-amber-400 bg-amber-500/10' : 'border-gray-700 noc-muted hover:noc-text-secondary' }}">
                All Sessions <span class="ml-1 font-mono font-bold">{{ $sessionCounts['all'] }}</span>
            </button>
            
            <div class="ml-auto">
                <input wire:model.live.debounce.300ms="searchSession" type="text" placeholder="Search by username, IP, or MAC..." 
                class="bg-[#1e293b] border border-gray-700 text-xs rounded-md px-3 py-1.5 text-gray-300 focus:outline-none focus:border-blue-500 w-64 placeholder-gray-500 dark:bg-slate-900 dark:text-slate-100">
            </div>
        </div>

        @if($sessionSubTab === 'pppoe')
        <x-noc.card :noPadding="true" class="flex-1 overflow-auto">
            <table class="w-full text-left text-sm">
                <thead style="background-color: var(--noc-subpanel);">
                    <tr>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Username</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Service</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">IP Address</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">MAC (Caller-ID)</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Uptime</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rx / Tx</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rate ↓ / ↑</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($pppSessions as $s)
                    <tr class="noc-row-hover transition-colors">
                        <td class="p-3 font-medium noc-text font-mono text-xs">{{ $s->name }}</td>
                        <td class="p-3 noc-muted text-xs">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" style="background-color: var(--noc-subpanel); color: var(--noc-muted);">{{ $s->service ?? 'ppp' }}</span>
                        </td>
                        <td class="p-3 font-mono noc-muted text-xs">{{ $s->address ?? '-' }}</td>
                        <td class="p-3 font-mono noc-muted text-xs">{{ $s->caller_id ?? '-' }}</td>
                        <td class="p-3 font-mono noc-muted text-xs">{{ $s->uptime ?? '-' }}</td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ {{ number_format(($s->bytes_in ?? 0) / 1024 / 1024, 1) }} MB</span><br>
                            <span class="text-blue-400">↑ {{ number_format(($s->bytes_out ?? 0) / 1024 / 1024, 1) }} MB</span>
                        </td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ {{ $s->rate_down ?? '-' }}</span><br>
                            <span class="text-blue-400">↑ {{ $s->rate_up ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-6 text-center text-xs noc-muted">Tidak ada sesi PPPoE aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
        <div class="flex-none mt-1">{{ $pppSessions?->links(data: ['scrollTo' => false]) }}</div>
        @endif

        {{-- Hotspot Table --}}
        @if($sessionSubTab === 'hotspot')
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm">
                <thead style="background-color: var(--noc-subpanel);">
                    <tr>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">User</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">IP Address</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">MAC Address</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Server</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Login By</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Uptime</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rx / Tx</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($hotspotSessions as $s)
                    <tr class="noc-row-hover transition-colors">
                        <td class="p-3 font-medium noc-text font-mono text-xs">{{ $s->user }}</td>
                        <td class="p-3 font-mono noc-muted text-xs">{{ $s->address ?? '-' }}</td>
                        <td class="p-3 font-mono noc-muted text-xs">{{ $s->mac_address ?? '-' }}</td>
                        <td class="p-3 noc-muted text-xs">{{ $s->server ?? '-' }}</td>
                        <td class="p-3 text-xs">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" style="background-color: var(--noc-subpanel); color: var(--noc-muted);">{{ $s->login_by ?? '-' }}</span>
                        </td>
                        <td class="p-3 font-mono noc-muted text-xs">{{ $s->uptime ?? '-' }}</td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ {{ number_format(($s->bytes_in ?? 0) / 1024 / 1024, 1) }} MB</span><br>
                            <span class="text-blue-400">↑ {{ number_format(($s->bytes_out ?? 0) / 1024 / 1024, 1) }} MB</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-6 text-center text-xs noc-muted">Tidak ada sesi Hotspot aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
        <div class="flex-none mt-1">{{ $hotspotSessions?->links(data: ['scrollTo' => false]) }}</div>
        @endif

        {{-- All Sessions Table --}}
        @if($sessionSubTab === 'all')
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm">
                <thead style="background-color: var(--noc-subpanel);">
                    <tr>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Protocol</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">User</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">IP / MAC</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Uptime</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rx/Tx Rate</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Started</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($sessions as $sess)
                    <tr class="noc-row-hover transition-colors">
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-xs font-medium uppercase" style="background-color: var(--noc-subpanel); color: var(--noc-muted);">{{ $sess->protocol }}</span></td>
                        <td class="p-3 font-medium noc-text text-xs">{{ $sess->username }}</td>
                        <td class="p-3 font-mono noc-muted text-xs">
                            <div>{{ $sess->address }}</div>
                            <div class="text-[10px]">{{ $sess->caller_id }}</div>
                        </td>
                        <td class="p-3 font-mono noc-muted text-xs">{{ $sess->uptime }}</td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ {{ $sess->rate_down ?? '-' }}</span><br>
                            <span class="text-blue-500">↑ {{ $sess->rate_up ?? '-' }}</span>
                        </td>
                        <td class="p-3 noc-muted text-xs">{{ $sess->session_started_at?->format('d M H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-6 text-center text-xs noc-muted">No active sessions.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
        <div class="flex-none mt-1">{{ $sessions?->links(data: ['scrollTo' => false]) }}</div>
        @endif
    </div>
    @endif
    
    @if($activeTab === 'health')
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Time</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">CPU</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Free RAM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($history as $h)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  noc-muted">{{ $h->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="p-3">
                            <x-noc.stat-badge :status="$h->is_online ? 'ONLINE' : 'OFFLINE'" />
                        </td>
                        <td class="p-3  font-mono noc-muted">{{ $h->cpu_load }}%</td>
                        <td class="p-3  font-mono noc-muted">
                            @if($h->total_memory)
                                {{ round($h->free_memory / 1024 / 1024- 1) }} MB
                            @else - @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="4" class="p-3  text-center noc-muted">No health history available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
    </div>
    @endif
    
    @if($activeTab === 'alarms')
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Level</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Title</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Started</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($alarms as $alarm)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3">
                            <x-noc.stat-badge :status="strtoupper($alarm->level)" />
                        </td>
                        <td class="p-3  font-medium noc-text">
                            {{ $alarm->title }}
                            <div class="text-xs noc-muted font-normal truncate max-w-md">{{ $alarm->description }}</div>
                        </td>
                        <td class="p-3  noc-muted">{{ $alarm->started_at->format('M d H:i') }} ({{ $alarm->started_at->diffForHumans(short: true) }})</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium noc-badge-info">OPEN</span>
                        </td>
                        <td class="p-3  text-right">
                            <a href="{{ route('noc.alarms.show', $alarm->id) }}" class="text-primary-600 hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="5" class="p-3  text-center noc-muted">No active alarms. Router is healthy.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
    </div>
    @endif
    
    @if($activeTab === 'traffic')
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <x-noc.card class="flex-1 w-full h-full" :noPadding="true">
            <x-noc.traffic-graph 
                :traffic="$traffic" 
                :period="$trafficPeriod" 
                heightClass="h-[350px]" 
            />
        </x-noc.card>
    </div>
    @endif
</div>
</div>















