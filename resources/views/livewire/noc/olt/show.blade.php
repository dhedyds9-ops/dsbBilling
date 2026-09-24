{{--
 NOC: OLT Detail View
 Tabs: Overview | PON | ONUs | Traffic | Alarms | Events
 Polling: 60 seconds
--}}
<div class="h-full flex flex-col overflow-hidden noc-bg" wire:poll.60000ms x-data="{ tab: '{{ $activeTab }}' }">

{{-- HEADER BAR --}}
<div class="flex-none px-3 py-2 border-b noc-border flex items-center gap-3 flex-wrap noc-panel-bg">
    <div class="flex items-center gap-2">
        <a href="{{ route('noc.olts.index') }}" class="noc-muted hover:noc-text-secondary text-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-base font-bold noc-text">{{ $olt->name }}</h1>
        <span class="text-xs noc-muted noc-mono">{{ $olt->code ?? '' }}</span>
        <x-noc.stat-badge :status="$status" />
    </div>

    <div class="flex items-center gap-3 text-xs ml-auto">
        <span class="noc-muted">IP: <span class="noc-mono noc-text-secondary">{{ $olt->ip_address }}</span></span>
        <span class="noc-muted">Model: <span class="noc-text-secondary">{{ $olt->vendor->name ?? '-' }} {{ $olt->model ?? '' }}</span></span>
        <span class="noc-muted">Uptime: <span class="noc-mono noc-text-secondary">{{ $olt->uptime_text ?? '-' }}</span></span>
        <span class="noc-muted">Temp:
            <span class="noc-mono {{ $olt->temperature > 60 ? 'text-red-500' : 'text-emerald-500' }}">{{ $olt->temperature ?? '-' }} °C</span>
        </span>
        
        <button wire:click="syncOlt" wire:loading.attr="disabled" class="btn btn-sm btn-primary ml-2 py-0.5 px-2 text-xs">
            <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin"></i> Sync Status
        </button>
    </div>
</div>

{{-- TABS --}}
<div class="flex-none px-3 border-b noc-border flex items-center gap-1 overflow-x-auto noc-scroll noc-panel-bg">
    @foreach(['overview' => 'Overview', 'pon' => 'PON Ports', 'onus' => 'ONUs', 'traffic' => 'Traffic', 'alarms' => 'Alarms', 'events' => 'Events'] as $key => $label)
    <button wire:click="setTab('{{ $key }}')"
        class="px-3 py-2 text-xs whitespace-nowrap border-b-2 transition-colors
               {{ $activeTab === $key ? 'noc-tab-active' : 'noc-tab-inactive' }}">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- CONTENT --}}
<div class="flex-1 overflow-hidden noc-scroll">

    @if($activeTab === 'overview')
    <div class="p-3 flex gap-3 overflow-auto noc-scroll h-full">
        <x-noc.card class="flex-none w-80">
            <x-slot name="header">
                <span class="text-xs font-bold noc-text-secondary uppercase tracking-widest">Device Info</span>
            </x-slot>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Code</dt><dd class="noc-mono noc-text-secondary">{{ $olt->code ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">IP Address</dt><dd class="noc-mono noc-text-secondary">{{ $olt->ip_address }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">SNMP Port</dt><dd class="noc-mono noc-text-secondary">{{ $olt->snmp_port ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Firmware</dt><dd class="noc-mono noc-text-secondary">{{ $olt->firmware_version ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">POP</dt><dd class="noc-text-secondary">{{ $olt->pop->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Last Polled</dt><dd class="noc-text-secondary">{{ $olt->last_polled_at ? $olt->last_polled_at->diffForHumans() : '-' }}</dd></div>
            </dl>
        </x-noc.card>

        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3 content-start">
            <x-noc.card>
                <div class="noc-summary-label">PON Ports</div>
                <div class="noc-summary-value text-blue-500 noc-mono">{{ $olt->pon_ports_count ?? 0 }}</div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">Total ONU</div>
                <div class="noc-summary-value noc-mono">{{ $olt->onus_count ?? 0 }}</div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">Active ONU</div>
                <div class="noc-summary-value text-emerald-500 noc-mono">{{ $olt->active_onu_count ?? 0 }}</div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">Temperature</div>
                <div class="noc-summary-value noc-mono {{ $olt->temperature > 60 ? 'text-red-500' : 'text-emerald-500' }}">{{ $olt->temperature ?? '-' }}<span class="text-sm">°C</span></div>
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
            </dl>
        </x-noc.card>
        @endif
    </div>
    @endif

    @if($activeTab === 'pon')
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Port Name</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Code</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Type</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);">Total ONU</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);">Active ONU</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @foreach($ponPorts as $port)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  font-medium noc-text">{{ $port->name }}</td>
                        <td class="p-3  font-mono noc-muted">{{ $port->code }}</td>
                        <td class="p-3  uppercase noc-muted">{{ $port->type }}</td>
                        <td class="p-3">
                            <x-noc.stat-badge :status="$port->status === 'active' ? 'ONLINE' : 'OFFLINE'">
                                {{ $port->status === 'active' ? 'UP' : 'DOWN' }}
                            </x-noc.stat-badge>
                        </td>
                        <td class="p-3  text-right noc-mono">{{ $port->onus_count }}</td>
                        <td class="p-3  text-right noc-mono text-emerald-500">{{ $port->active_onu_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </x-noc.card>
    </div>
    @endif

    @if($activeTab === 'onus')
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Name</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">SN</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Port / ODP</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Rx Power</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Last Seen</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($onus as $onu)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  font-medium noc-text">{{ $onu->name }}</td>
                        <td class="p-3  font-mono noc-muted">{{ $onu->serial_number }}</td>
                        <td class="p-3  noc-muted">
                            <div>{{ $onu->ponPort->name ?? '-' }}</div>
                            <div class="text-[10px]">{{ $onu->odp->name ?? '' }}</div>
                        </td>
                        <td class="p-3">
                            <x-noc.stat-badge :status="$this->getOnuStatus($onu)" />
                        </td>
                        <td class="p-3  font-mono {{ ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -27) ? 'text-red-500' : 'text-emerald-500' }}">
                            {{ $onu->rx_power_dbm ?? '-' }} dBm
                        </td>
                        <td class="p-3  noc-muted">{{ $onu->last_seen_at?->diffForHumans(short: true) ?? '-' }}</td>
                        <td class="p-3  text-right">
                            <a href="{{ route('noc.onus.show', $onu->id) }}" class="text-primary-600 hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="7" class="p-3  text-center noc-muted">No ONUs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
        <div class="mt-2 flex-none">
            {{ $onus->links(data: ['scrollTo' => false]) }}
        </div>
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
                            <div class="text-[10px] noc-muted">{{ $alarm->source_name }}</div>
                        </td>
                        <td class="p-3  noc-muted">{{ $alarm->started_at->format('M d H:i') }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium noc-badge-info">OPEN</span>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="4" class="p-3  text-center noc-muted">No active alarms.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
    </div>
    @endif

    @if($activeTab === 'traffic' || $activeTab === 'events')
    <div class="p-3 h-full overflow-hidden flex flex-col justify-center items-center text-center">
        <i class="bi bi-tools text-4xl noc-muted mb-2 opacity-50"></i>
        <div class="noc-muted opacity-70">{{ ucfirst($activeTab) }} monitoring module is under construction.</div>
    </div>
    @endif

</div>

</div>






