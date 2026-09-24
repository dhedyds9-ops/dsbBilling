{{--
 NOC: ONU Monitoring Index
--}}
<div class="h-full flex flex-col noc-bg">
    {{-- Summary Header --}}
    <div class="flex-none px-3 py-2 border-b noc-border flex items-center justify-between gap-3 flex-wrap noc-panel-bg">
        <div class="flex items-center gap-3">
            <h1 class="text-base font-bold noc-text">ONU Monitoring</h1>
            <span class="noc-count-badge">{{ $summary['total'] }} Total</span>
            <span class="noc-count-online">{{ $summary['online'] }} Online</span>
            <span class="noc-count-offline">{{ $summary['offline'] }} Offline</span>
            <span class="noc-count-los">{{ $summary['los'] }} LOS</span>
            <span class="noc-count-warning">{{ $summary['low_rx'] }} Low RX</span>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="oltFilter" class="noc-input px-2 py-1 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option value="">All OLT</option>
                @foreach($olts as $olt)
                <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="noc-input px-2 py-1 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option value="all">All Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="los">LOS / Critical RX</option>
                <option value="low_rx">Low RX (-30 to -27)</option>
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="SN / MAC / Pelanggan" class="noc-input px-2 py-1 text-xs rounded border focus:outline-none w-56 dark:bg-slate-900 dark:text-slate-100">
        </div>
    </div>

    {{-- Table --}}
    <div class="flex-1 overflow-auto noc-scroll">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
                    <th class="p-3 font-semibold" wire:click="sort('serial_number')">Serial {!! $sortField === 'serial_number' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold" wire:click="sort('name')">Name {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">OLT / PON</th>
                    <th class="p-3 font-semibold">Pelanggan</th>
                    <th class="p-3 font-semibold" wire:click="sort('rx_power_dbm')">RX Power {!! $sortField === 'rx_power_dbm' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">TX Power</th>
                    <th class="p-3 font-semibold">Temp</th>
                    <th class="p-3 font-semibold" wire:click="sort('status')">Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold" wire:click="sort('last_seen_at')">Last Seen {!! $sortField === 'last_seen_at' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($onus as $onu)
                @php
                    $st = $this->getOnuStatus($onu);
                    $badge = match($st) {
                        'ONLINE' => 'noc-badge-online',
                        'LOW_RX' => 'noc-badge-warning',
                        'LOS'    => 'noc-badge-los',
                        default  => 'noc-badge-offline',
                    };
                    $rxClass = $onu->rx_power_dbm < -30 ? 'text-red-500' : ($onu->rx_power_dbm < -27 ? 'text-yellow-500' : 'text-emerald-500');
                @endphp
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover">
                    <td class="p-3 noc-mono noc-text font-medium">{{ $onu->serial_number }}</td>
                    <td class="p-3 noc-text-secondary">{{ $onu->name ?? '-' }}</td>
                    <td class="p-3 noc-muted">
                        <span class="noc-muted">{{ $onu->olt->name ?? '-' }}</span>
                        <span class="noc-muted opacity-60">/</span>
                        <span class="noc-mono noc-muted">{{ $onu->ponPort->name ?? '-' }}</span>
                    </td>
                    <td class="p-3 noc-muted">{{ $onu->customerService->customer->name ?? '-' }}</td>
                    <td class="p-3 noc-mono font-medium {{ $rxClass }}">{{ $onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm-1) . ' dBm' : '-' }}</td>
                    <td class="p-3 noc-mono noc-muted">{{ $onu->tx_power_dbm !== null ? number_format($onu->tx_power_dbm-1) . ' dBm' : '-' }}</td>
                    <td class="p-3 noc-mono noc-muted">{{ $onu->temperature ?? '-' }}</td>
                    <td class="p-3">
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $st }}</span>
                    </td>
                    <td class="p-3 noc-muted">{{ $onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-' }}</td>
                    <td class="p-3">
                        <a href="{{ route('noc.onus.show', $onu->id) }}" class="text-blue-500 hover:text-blue-400">View</a>
                    </td>
                </tr>
                @empty
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="10" class="p-3 text-center noc-muted opacity-70">No ONUs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($onus->hasPages())
    <div class="flex-none px-3 py-2 border-t noc-border noc-panel-bg">
        {{ $onus->links(data: ['scrollTo' => false]) }}
    </div>
    @endif
</div>






