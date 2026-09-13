{{--
 NOC Overview — Main Control Room
 Layout: Left Health Panel | Center Traffic + Alarms | Bottom Device Table
 Polling: 30 seconds
--}}
<div
    class="h-full flex flex-col overflow-hidden"
    style="background:#0a0e1a;"
    wire:poll.30000ms="refreshData"
    x-data="{ trafficChart: null }"
>

{{-- ============================================================
     HEADER BAR — Network Health Summary
     ============================================================ --}}
<div class="flex-none px-3 py-2 border-b flex items-center gap-4 flex-wrap" style="background:#111827;border-color:#1f2937;">

    {{-- OLT --}}
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">OLT</span>
        <span class="text-sm font-bold noc-mono
            {{ ($header['olt']['online'] ?? 0) < ($header['olt']['total'] ?? 1) ? 'text-yellow-400' : 'text-green-400' }}">
            {{ $header['olt']['online'] ?? 0 }}/{{ $header['olt']['total'] ?? 0 }}
        </span>
    </div>

    <div class="text-gray-700 dark:text-gray-300">|</div>

    {{-- ONU --}}
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">ONU</span>
        <span class="text-sm font-bold noc-mono
            {{ ($header['onu']['online'] ?? 0) < ($header['onu']['total'] ?? 1) ? 'text-yellow-400' : 'text-green-400' }}">
            {{ $header['onu']['online'] ?? 0 }}/{{ $header['onu']['total'] ?? 0 }}
        </span>
    </div>

    <div class="text-gray-700 dark:text-gray-300">|</div>

    {{-- Router --}}
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Router</span>
        <span class="text-sm font-bold noc-mono
            {{ ($header['router']['online'] ?? 0) < ($header['router']['total'] ?? 1) ? 'text-red-400' : 'text-green-400' }}">
            {{ $header['router']['online'] ?? 0 }}/{{ $header['router']['total'] ?? 0 }}
        </span>
    </div>

    <div class="text-gray-700 dark:text-gray-300">|</div>

    {{-- PPPoE --}}
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">PPPoE</span>
        <span class="text-sm font-bold text-blue-400 noc-mono">{{ number_format($header['pppoe'] ?? 0) }}</span>
    </div>

    {{-- Active Alarms badge --}}
    @if(($health['alarms']['critical'] ?? 0) > 0)
    <div class="ml-auto flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold" style="background:#450a0a;color:#ef4444;border:1px solid #7f1d1d;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        {{ $health['alarms']['critical'] }} CRITICAL
    </div>
    @elseif(($health['alarms']['warning'] ?? 0) > 0)
    <div class="ml-auto flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold" style="background:#451a03;color:#f59e0b;border:1px solid #78350f;">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ $health['alarms']['warning'] }} WARNING
    </div>
    @else
    <div class="ml-auto flex items-center gap-1 px-2 py-0.5 rounded text-xs" style="background:#064e3b;color:#10b981;border:1px solid #065f46;">
        <i class="bi bi-check-circle-fill"></i>
        Network Healthy
    </div>
    @endif
</div>

{{-- ============================================================
     MAIN 3-COLUMN LAYOUT
     ============================================================ --}}
<div class="flex-1 flex overflow-hidden min-h-0">

    {{-- ==============================
         LEFT: NETWORK HEALTH PANEL
         ============================== --}}
    <aside class="flex-none w-44 border-r overflow-y-auto noc-scroll" style="background:#111827;border-color:#1f2937;">
        <div class="px-3 py-2 border-b" style="border-color:#1f2937;">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Network Health</span>
        </div>

        {{-- OLT --}}
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">OLT</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Online</span>
                    <span class="text-green-400 noc-mono font-medium">{{ $health['olt']['online'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Offline</span>
                    <span class="{{ ($health['olt']['offline'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ $health['olt']['offline'] ?? 0 }}
                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Warning</span>
                    <span class="{{ ($health['olt']['warning'] ?? 0) > 0 ? 'text-yellow-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ $health['olt']['warning'] ?? 0 }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ONU --}}
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">ONU</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Online</span>
                    <span class="text-green-400 noc-mono font-medium">{{ number_format($health['onu']['online'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Offline</span>
                    <span class="{{ ($health['onu']['offline'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ number_format($health['onu']['offline'] ?? 0) }}
                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">LOS</span>
                    <span class="{{ ($health['onu']['los'] ?? 0) > 0 ? 'text-purple-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ $health['onu']['los'] ?? 0 }}
                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Low RX</span>
                    <span class="{{ ($health['onu']['low_rx'] ?? 0) > 0 ? 'text-yellow-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ $health['onu']['low_rx'] ?? 0 }}
                    </span>
                </div>
            </div>
        </div>

        {{-- PON --}}
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">PON</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Healthy</span>
                    <span class="text-green-400 noc-mono font-medium">{{ $health['pon']['healthy'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Warning</span>
                    <span class="{{ ($health['pon']['warning'] ?? 0) > 0 ? 'text-yellow-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ $health['pon']['warning'] ?? 0 }}
                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Down</span>
                    <span class="{{ ($health['pon']['down'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ $health['pon']['down'] ?? 0 }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ROUTER --}}
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Router</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Online</span>
                    <span class="text-green-400 noc-mono font-medium">{{ $health['router']['online'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Offline</span>
                    <span class="{{ ($health['router']['offline'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400' }} noc-mono font-medium">
                        {{ $health['router']['offline'] ?? 0 }}
                    </span>
                </div>
            </div>
        </div>

        {{-- SERVICES --}}
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Services</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">PPPoE</span>
                    <span class="text-blue-400 noc-mono font-medium">{{ number_format($health['services']['pppoe'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Hotspot</span>
                    <span class="text-blue-400 noc-mono font-medium">{{ number_format($health['services']['hotspot'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        {{-- SERVICE HEALTH --}}
        <div class="px-3 py-2">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Svc Health</div>
            <div class="space-y-1">
                @foreach($services as $svc)
                <div class="flex items-center gap-1.5">
                    @php
                        $dotColor = match($svc['status'] ?? 'unknown') {
                            'healthy' => 'bg-green-500',
                            'warning' => 'bg-yellow-500',
                            'down'    => 'bg-red-500',
                            default   => 'bg-gray-600',
                        };
                    @endphp
                    <span class="w-1.5 h-1.5 rounded-full flex-none {{ $dotColor }}"></span>
                    <span class="text-xs text-gray-400 truncate" title="{{ $svc['detail'] ?? '' }}">
                        {{ $svc['name'] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </aside>

    {{-- ==============================
         CENTER: TRAFFIC + ALARMS
         ============================== --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- TRAFFIC PANEL --}}
        <div class="flex-none border-b" style="border-color:#1f2937;">
            <x-noc.traffic-graph 
                :traffic="$traffic" 
                :period="$trafficPeriod" 
                heightClass="h-48" 
            />
        </div>

        {{-- ACTIVE ALARMS --}}
        <div class="flex-none border-b" style="background:#0d1117;border-color:#1f2937;">
            <div class="flex items-center justify-between px-3 py-1.5 border-b" style="border-color:#1a2332;">
                <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Active Alarms</span>
                @if(Route::has('noc.alarms.index'))
                <a href="{{ route('noc.alarms.index') }}" class="text-xs text-blue-400 hover:text-blue-300">View All →</a>
                @endif
            </div>
            @if($alarms->isEmpty())
            <div class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                <i class="bi bi-check-circle text-green-500"></i> No active alarms
            </div>
            @else
            <div class="divide-y" style="border-color:#1a2332;">
                @foreach($alarms as $alarm)
                <div class="flex items-center gap-2 px-3 py-1.5">
                    @php
                        $levelColor = match($alarm->level) {
                            'critical' => 'text-red-400',
                            'warning'  => 'text-yellow-400',
                            default    => 'text-blue-400',
                        };
                    @endphp
                    <span class="w-12 text-xs font-bold {{ $levelColor }} uppercase noc-mono">{{ strtoupper($alarm->level) }}</span>
                    <span class="flex-1 text-xs text-gray-300 truncate">{{ $alarm->title }}</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 noc-mono flex-none">{{ $alarm->source_name }}</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 noc-mono flex-none">{{ $alarm->started_at?->diffForHumans() }}</span>
                    @if(Route::has('noc.alarms.show'))
                    <a href="{{ route('noc.alarms.show', $alarm->id) }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-300 flex-none">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- PROVISIONING SUMMARY --}}
        <div class="flex-none px-3 py-1.5 flex items-center gap-4 border-b text-xs" style="border-color:#1f2937;background:#0d1117;">
            <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Provisioning</span>
            <span class="text-gray-500 dark:text-gray-400">Queued: <span class="text-yellow-400 noc-mono">{{ $provisioning['queued'] ?? 0 }}</span></span>
            <span class="text-gray-500 dark:text-gray-400">Running: <span class="text-blue-400 noc-mono">{{ $provisioning['running'] ?? 0 }}</span></span>
            @if(($provisioning['failed'] ?? 0) > 0)
            <span class="text-red-400 font-medium">Failed: <span class="noc-mono">{{ $provisioning['failed'] }}</span></span>
            @else
            <span class="text-gray-500">Failed: <span class="text-gray-600 dark:text-gray-400 noc-mono">0</span></span>
            @endif
            @if(Route::has('noc.provisioning.index'))
            <a href="{{ route('noc.provisioning.index') }}" class="ml-auto text-blue-400 hover:text-blue-300">View →</a>
            @endif
        </div>
    </div>
</div>

{{-- ============================================================
     BOTTOM: DEVICE TABLE
     ============================================================ --}}
<div class="flex-none border-t" style="background:#111827;border-color:#1f2937;">

    {{-- Table Header --}}
    <div class="flex items-center gap-2 px-3 py-1.5 border-b" style="border-color:#1a2332;">
        <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Devices</span>

        {{-- Search --}}
        <input
            type="text"
            wire:model.live.debounce.400ms="deviceSearch"
            placeholder="Search device / IP / serial…"
            class="flex-1 max-w-xs px-2 py-0.5 text-xs rounded border dark:bg-slate-900 dark:text-slate-100"
            style="background:#0d1117;border-color:#374151;color:#e5e7eb;"
        >

        {{-- Filter --}}
        <div class="flex gap-1">
            @foreach(['all' => 'All', 'olt' => 'OLT', 'onu' => 'ONU', 'router' => 'Router', 'pppoe' => 'PPPoE'] as $key => $label)
            <button
                wire:click="setDeviceFilter('{{ $key }}')"
                class="px-2 py-0.5 text-xs rounded transition-colors
                       {{ $deviceFilter === $key ? 'bg-blue-800 text-blue-200' : 'text-gray-500 dark:text-gray-400 hover:text-gray-300 hover:bg-gray-800' }}"
            >{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto" style="max-height:200px;overflow-y:auto;">
        <table class="w-full text-xs">
            <thead>
                <tr style="background:#0d1117;color:#6b7280;">
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Device</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Type</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">IP</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Status</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Last Seen</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color:#1a2332;">
                @forelse($devices as $device)
                <tr class="hover:bg-gray-900/50 transition-colors">
                    <td class="px-3 py-1.5 font-medium text-gray-300">{{ $device->name ?? '—' }}</td>
                    <td class="px-3 py-1.5">
                        <span class="px-1.5 py-0.5 rounded text-xs noc-mono" style="background:#1f2937;color:#9ca3af;">
                            {{ $device->type ?? '—' }}
                        </span>
                    </td>
                    <td class="px-3 py-1.5 noc-mono text-gray-400">{{ $device->ip ?? '—' }}</td>
                    <td class="px-3 py-1.5">
                        @php
                            $st = strtolower($device->status ?? 'unknown');
                            $badge = match(true) {
                                $st === 'online'  => 'noc-badge-online',
                                $st === 'warning' => 'noc-badge-warning',
                                $st === 'offline' => 'noc-badge-offline',
                                default           => 'noc-badge-unknown',
                            };
                        @endphp
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $badge }}">
                            {{ strtoupper($device->status ?? 'UNKNOWN') }}
                        </span>
                    </td>
                    <td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 noc-mono">
                        @if($device->last_seen)
                            {{ \Carbon\Carbon::parse($device->last_seen)->diffForHumans() }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-3 py-1.5">
                        @php
                            $deviceType = strtolower($device->type ?? '');
                            $routeMap   = ['olt' => 'noc.olts.show', 'router' => 'noc.routers.show', 'onu' => 'noc.onus.show'];
                            $deviceRoute = $routeMap[$deviceType] ?? null;
                            // Extract numeric ID from prefixed IDs like 'olt_5'
                            $rawId = $device->id ?? null;
                            preg_match('/(\d+)$/', (string)$rawId, $matches);
                            $numericId = $matches[1] ?? null;
                        @endphp
                        @if($deviceRoute && $numericId && Route::has($deviceRoute))
                        <a href="{{ route($deviceRoute, $numericId) }}" class="text-blue-400 hover:text-blue-300 text-xs">View</a>
                        @else
                        <span class="text-gray-600 dark:text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-600 dark:text-gray-400">No devices found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($devices->hasPages())
    <div class="px-3 py-1.5 flex items-center justify-between border-t" style="border-color:#1a2332;">
        <span class="text-xs text-gray-600 dark:text-gray-400">
            {{ $devices->firstItem() }}–{{ $devices->lastItem() }} of {{ $devices->total() }}
        </span>
        <div class="flex gap-1">
            @if($devices->onFirstPage())
                <span class="px-2 py-0.5 text-xs text-gray-700 dark:text-gray-300">←</span>
            @else
                <button wire:click="previousPage" class="px-2 py-0.5 text-xs text-gray-400 hover:text-gray-200">←</button>
            @endif
            @if($devices->hasMorePages())
                <button wire:click="nextPage" class="px-2 py-0.5 text-xs text-gray-400 hover:text-gray-200">→</button>
            @else
                <span class="px-2 py-0.5 text-xs text-gray-700 dark:text-gray-300">→</span>
            @endif
        </div>
    </div>
    @endif
</div>

</div>






