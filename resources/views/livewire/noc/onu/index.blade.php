{{--
 NOC: ONU Monitoring Index
--}}
<div class="h-full flex flex-col noc-bg w-full" style="background-color: #0a0e1a !important; min-height: 100vh;">
    {{-- Top Bar: Title & Summary Badges --}}
    <div class="flex-none px-4 py-3 border-b noc-border noc-panel-bg">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 mr-1">
                    <span class="material-symbols-outlined notranslate noc-text text-xl" translate="no">router</span>
                    <h1 class="text-base font-bold noc-text">ONU Monitoring</h1>
                </div>

                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 w-full sm:w-auto shrink-0">
                    <button type="button" wire:click="$set('statusFilter', 'all')"
                        class="whitespace-nowrap inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white border-transparent' : 'noc-count-badge border-transparent' }}">
                        <span>{{ number_format($summary['total']) }}</span>
                        <span class="text-[11px] opacity-80">Total</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'online')"
                        class="whitespace-nowrap inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'online' ? 'bg-emerald-600 text-white border-transparent' : 'noc-count-online border-transparent' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'online' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                        <span>{{ number_format($summary['online']) }}</span>
                        <span class="text-[11px] opacity-80">Online</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'offline')"
                        class="whitespace-nowrap inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'offline' ? 'bg-slate-600 text-white border-transparent' : 'noc-count-offline border-transparent' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'offline' ? 'bg-white' : 'bg-slate-400' }}"></span>
                        <span>{{ number_format($summary['offline']) }}</span>
                        <span class="text-[11px] opacity-80">Offline</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'los')"
                        class="whitespace-nowrap inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'los' ? 'bg-rose-600 text-white border-transparent' : 'noc-count-los border-transparent' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'los' ? 'bg-white' : 'bg-rose-500' }}"></span>
                        <span>{{ number_format($summary['los']) }}</span>
                        <span class="text-[11px] opacity-80">LOS</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'low_rx')"
                        class="whitespace-nowrap inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'low_rx' ? 'bg-amber-600 text-white border-transparent' : 'noc-count-warning border-transparent' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'low_rx' ? 'bg-white' : 'bg-amber-500' }}"></span>
                        <span>{{ number_format($summary['low_rx']) }}</span>
                        <span class="text-[11px] opacity-80">Low RX</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="flex-none px-4 py-2.5 border-b noc-border bg-black/20">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2.5">
            {{-- Search Bar --}}
            <div class="relative w-full lg:w-72">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                    <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">search</span>
                </div>
                <input type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Cari SN, MAC, atau Pelanggan..."
                    class="noc-input w-full pl-8 pr-7 py-1.5 text-xs rounded-lg border focus:outline-none"
                />
                @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-200">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">close</span>
                </button>
                @endif
            </div>

            {{-- Dropdown Filters --}}
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full lg:w-auto">
                <select wire:model.live="oltFilter" class="noc-input h-8 px-2.5 pr-7 text-xs rounded-lg border focus:outline-none cursor-pointer w-full sm:w-auto">
                    <option value="">Semua OLT</option>
                    @foreach($olts as $olt)
                    <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                    @endforeach
                </select>

                @if(!empty($oltFilter) && $oltFilter !== 'all')
                <select wire:model.live="ponFilter" class="noc-input h-8 px-2.5 pr-7 text-xs rounded-lg border focus:outline-none cursor-pointer w-full sm:w-auto">
                    <option value="">Semua PON</option>
                    @foreach($ponPorts as $pon)
                    <option value="{{ $pon->id }}">{{ $pon->name }}</option>
                    @endforeach
                </select>
                @endif

                <select wire:model.live="statusFilter" class="noc-input h-8 px-2.5 pr-7 text-xs rounded-lg border focus:outline-none cursor-pointer w-full sm:w-auto">
                    <option value="all">Semua Status</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="los">LOS / Critical RX</option>
                    <option value="low_rx">Low RX (-30 s/d -27)</option>
                </select>

                <select wire:model.live="perPage" class="noc-input h-8 px-2.5 pr-7 text-xs rounded-lg border focus:outline-none cursor-pointer w-full sm:w-auto">
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                    <option value="0">Semua</option>
                </select>

                @if($search !== '' || $statusFilter !== 'all' || $oltFilter !== '' || $ponFilter !== '')
                <button type="button" wire:click="resetFilters" class="noc-input h-8 px-2.5 text-xs font-medium rounded-lg border hover:brightness-125 transition-all flex items-center justify-center gap-1 col-span-2 sm:col-span-1" title="Reset Semua Filter">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">restart_alt</span>
                    Reset
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="flex-1 overflow-auto noc-scroll">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
                    <th class="p-3 font-semibold text-center w-28">Aksi</th>
                    <th class="p-3 font-semibold" wire:click="sort('serial_number')">Serial {!! $sortField === 'serial_number' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold" wire:click="sort('mac_address')">MAC {!! $sortField === 'mac_address' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">IP Host</th>
                    <th class="p-3 font-semibold">OLT / PON</th>
                    <th class="p-3 font-semibold">Pelanggan</th>
                    <th class="p-3 font-semibold" wire:click="sort('rx_power_dbm')">RX Power {!! $sortField === 'rx_power_dbm' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">TX Power</th>
                    <th class="p-3 font-semibold">Temp</th>
                    <th class="p-3 font-semibold" wire:click="sort('status')">Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold" wire:click="sort('last_seen_at')">Last Seen {!! $sortField === 'last_seen_at' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
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
                    <td class="p-3 text-center">
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ route('noc.onus.show', $onu->id) }}" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-colors">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <a href="{{ route('isp.onus.edit', $onu->id) }}" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition-colors">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>
                    </td>
                    <td class="p-3 noc-mono noc-text font-medium">{{ $onu->serial_number }}</td>
                    <td class="p-3 noc-mono noc-text-secondary">{{ $onu->mac_address ?? $onu->acsDevice?->mac_address ?? '-' }}</td>
                    <td class="p-3 noc-mono noc-text-secondary">{{ $onu->acsDevice?->ip_address ?? '-' }}</td>
                    <td class="p-3 noc-muted">
                        <span class="noc-muted">{{ $onu->olt?->name ?? '-' }}</span>
                        <span class="noc-muted opacity-60">/</span>
                        <span class="noc-mono noc-muted">{{ $onu->ponPort?->name ?? '-' }}</span>
                    </td>
                    <td class="p-3 noc-muted">{{ $onu->customerService?->customer?->name ?? '-' }}</td>
                    <td class="p-3 noc-mono font-medium {{ $rxClass }}">{{ $onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm-1) . ' dBm' : '-' }}</td>
                    <td class="p-3 noc-mono noc-muted">{{ $onu->tx_power_dbm !== null ? number_format($onu->tx_power_dbm-1) . ' dBm' : '-' }}</td>
                    <td class="p-3 noc-mono noc-muted">{{ $onu->temperature ?? '-' }}</td>
                    <td class="p-3">
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $st }}</span>
                    </td>
                    <td class="p-3 noc-muted">{{ $onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-' }}</td>
                </tr>
                @empty
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="11" class="p-3 text-center noc-muted opacity-70">No ONUs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($onus->hasPages())
    <div class="flex-none px-3 py-2 border-t noc-border noc-panel-bg overflow-x-auto">
        {{ $onus->links(data: ['scrollTo' => false]) }}
    </div>
    @endif
</div>






