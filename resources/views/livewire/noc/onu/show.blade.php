{{--
 NOC: ONU Detail View
 Tabs: Overview | Optical | Service | Provisioning | Alarms
 Polling: 60 seconds
--}}
<div class="h-full flex flex-col overflow-hidden noc-bg" wire:poll.60000ms>

{{-- HEADER BAR --}}
<div class="flex-none px-3 py-2 border-b noc-border flex items-center gap-3 flex-wrap noc-panel-bg">
    <div class="flex items-center gap-2">
        <a href="{{ route('noc.onus.index') }}" class="noc-muted hover:noc-text-secondary text-sm"><i class="bi bi-arrow-left"></i></a>
        <h1 class="text-base font-bold noc-text">{{ $onu->name ?? $onu->serial_number }}</h1>
        <span class="text-xs noc-muted noc-mono">{{ $onu->serial_number }}</span>
        <x-noc.stat-badge :status="$status" />
    </div>
    <div class="flex items-center gap-3 text-xs ml-auto">
        <span class="noc-muted">OLT: <span class="noc-text-secondary">{{ $onu->olt->name ?? '-' }}</span></span>
        <span class="noc-muted">PON: <span class="noc-mono noc-text-secondary">{{ $onu->ponPort->name ?? '-' }}</span></span>
        <span class="noc-muted">ODP: <span class="noc-text-secondary">{{ $onu->odp->code ?? '-' }}</span></span>
        <span class="noc-muted">RX: <span class="noc-mono font-medium {{ $rxClass }}">{{ $onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm-1) . ' dBm' : '-' }}</span></span>
        
        @if($canManage)
        <button wire:click="syncTR069" wire:loading.attr="disabled" class="btn btn-sm btn-outline-secondary ml-2 py-0.5 px-2 text-xs">
            <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin"></i> TR-069
        </button>
        <button wire:click="rebootOnu" wire:confirm="Are you sure you want to reboot this ONU ?? " wire:loading.attr="disabled" class="btn btn-sm btn-danger py-0.5 px-2 text-xs">
            <i class="bi bi-power"></i> Reboot
        </button>
        @endif
    </div>
</div>

{{-- TABS --}}
<div class="flex-none px-3 border-b noc-border flex items-center gap-1 overflow-x-auto noc-scroll noc-panel-bg">
    @foreach(['overview' => 'Overview', 'optical' => 'Optical History', 'service' => 'Service', 'provisioning' => 'Provisioning', 'alarms' => 'Alarms'] as $k => $l)
    <button wire:click="setTab('{{ $k }}')"
        class="px-3 py-2 text-xs whitespace-nowrap border-b-2 transition-colors
               {{ $activeTab === $k ? 'noc-tab-active' : 'noc-tab-inactive' }}">
        {{ $l }}
    </button>
    @endforeach
</div>

{{-- TAB CONTENT --}}
<div class="flex-1 overflow-hidden noc-scroll">

    @if($activeTab === 'overview')
    <div class="p-3 flex gap-3 overflow-auto noc-scroll h-full">
        {{-- Device Info --}}
        <x-noc.card class="flex-none w-80">
            <x-slot name="header">
                <span class="text-xs font-bold noc-text-secondary uppercase tracking-widest">Device Info</span>
            </x-slot>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Serial</dt><dd class="noc-mono noc-text-secondary">{{ $onu->serial_number }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">MAC</dt><dd class="noc-mono noc-text-secondary">{{ $onu->mac_address ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Model</dt><dd class="noc-text-secondary">{{ $onu->model ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Vendor</dt><dd class="noc-text-secondary">{{ $onu->vendor->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Firmware</dt><dd class="noc-mono noc-text-secondary">{{ $onu->firmware_version ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Hardware</dt><dd class="noc-mono noc-text-secondary">{{ $onu->hardware_version ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Provision</dt><dd class="noc-text-secondary">{{ $onu->provision_status ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Provisioned</dt><dd class="noc-text-secondary">{{ $onu->provisioned_at ? $onu->provisioned_at->diffForHumans() : '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Last Seen</dt><dd class="noc-text-secondary">{{ $onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-' }}</dd></div>
            </dl>
        </x-noc.card>

        {{-- Signal KPI --}}
        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3 content-start">
            <x-noc.card>
                <div class="noc-summary-label">RX Power</div>
                <div class="noc-summary-value noc-mono {{ $rxClass }}">{{ $onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm-1) : '-' }}<span class="text-sm"> dBm</span></div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">TX Power</div>
                <div class="noc-summary-value noc-mono">{{ $onu->tx_power_dbm !== null ? number_format($onu->tx_power_dbm-1) : '-' }}<span class="text-sm"> dBm</span></div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">SNR</div>
                <div class="noc-summary-value text-blue-500 noc-mono">{{ $onu->snr_db ?? '-' }}<span class="text-sm"> dB</span></div>
            </x-noc.card>
            <x-noc.card>
                <div class="noc-summary-label">Temperature</div>
                <div class="noc-summary-value noc-mono">{{ $onu->temperature ?? '-' }}<span class="text-sm"> °C</span></div>
            </x-noc.card>
            
            @if($canManage)
            <x-noc.card class="col-span-2 border-red-500/30">
                <x-slot name="header">
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Danger Zone</span>
                </x-slot>
                <div class="flex gap-2">
                    <button wire:click="factoryResetOnu" wire:confirm="WARNING: This will wipe all configuration on the ONU. Proceed ?? " class="btn btn-sm btn-outline-danger">Factory Reset TR-069</button>
                </div>
            </x-noc.card>
            @endif
        </div>

        {{-- Service Info --}}
        @if($onu->customerService)
        <x-noc.card class="flex-none w-80">
            <x-slot name="header">
                <span class="text-xs font-bold text-blue-500 uppercase tracking-widest">Layanan Pelanggan</span>
            </x-slot>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Pelanggan</dt><dd class="noc-text-secondary font-medium">{{ $onu->customerService->customer->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Kode</dt><dd class="noc-mono noc-muted">{{ $onu->customerService->customer->code ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Tipe</dt><dd class="noc-text-secondary">{{ strtoupper($onu->customerService->service_type ?? '-') }}</dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Status</dt><dd class="noc-text-secondary">{{ $onu->customerService->status ?? '-' }}</dd></div>
            </dl>
        </x-noc.card>
        @endif
    </div>
    @endif

    @if($activeTab === 'optical')
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <x-noc.card :noPadding="true" class="flex-1">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Time</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Rx Power</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Tx Power</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">SNR</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Temp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    @forelse($signals as $s)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  noc-muted">{{ $s->measured_at->format('Y-m-d H:i:s') }}</td>
                        <td class="p-3">
                            <x-noc.stat-badge :status="$s->status === 'online' ? 'ONLINE' : ($s->status === 'los' ? 'LOS' : 'OFFLINE')" />
                        </td>
                        <td class="p-3  font-mono {{ ($s->rx_power_dbm !== null && $s->rx_power_dbm < -27) ? 'text-red-500' : 'text-emerald-500' }}">{{ $s->rx_power_dbm ?? '-' }} dBm</td>
                        <td class="p-3  font-mono noc-muted">{{ $s->tx_power_dbm ?? '-' }} dBm</td>
                        <td class="p-3  font-mono noc-muted">{{ $s->snr_db ?? '-' }} dB</td>
                        <td class="p-3  font-mono noc-muted">{{ $s->temperature ?? '-' }} °C</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="6" class="p-3  text-center noc-muted">No signal history available.</td>
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
                            <div class="text-[10px] noc-muted truncate max-w-sm">{{ $alarm->description }}</div>
                        </td>
                        <td class="p-3  noc-muted">{{ $alarm->started_at->format('M d H:i') }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium noc-badge-info">OPEN</span>
                        </td>
                        <td class="p-3  text-right">
                            <a href="{{ route('noc.alarms.show', $alarm->id) }}" class="text-primary-600 hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="5" class="p-3  text-center noc-muted">No active alarms. ONU is healthy.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-noc.card>
    </div>
    @endif

    @if(in_array($activeTab, ['service', 'provisioning']))
    <div class="p-3 h-full flex flex-col items-center justify-center text-center">
        <i class="bi bi-tools text-4xl noc-muted mb-2 opacity-50"></i>
        <div class="noc-muted opacity-70">
            Modul {{ ucfirst($activeTab) }} sedang dikembangkan dan akan terintegrasi langsung.
        </div>
    </div>
    @endif

</div>

</div>






