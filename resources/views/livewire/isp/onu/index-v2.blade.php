{{--
 NOC: ONU Monitoring Index
--}}
<div class="h-full flex flex-col bg-slate-50 dark:bg-slate-900">
    {{-- Top Bar: Title, Summary Badges, and Action --}}
    <div class="flex-none px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700/80">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Title & Summary Badges --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 mr-1">
                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400 text-xl" translate="no">router</span>
                    <h1 class="text-base font-bold text-slate-800 dark:text-slate-100">ONU Monitoring</h1>
                </div>

                {{-- Status Pills (clickable quick filters) --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" wire:click="$set('statusFilter', 'all')"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'all' ? 'bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 border-transparent shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600' }}">
                        <span>{{ number_format($summary['total']) }}</span>
                        <span class="text-[11px] opacity-80">Total</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'online')"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'online' ? 'bg-emerald-600 text-white border-transparent shadow-xs' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'online' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                        <span>{{ number_format($summary['online']) }}</span>
                        <span class="text-[11px] opacity-80">Online</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'offline')"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'offline' ? 'bg-slate-600 text-white border-transparent shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'offline' ? 'bg-white' : 'bg-slate-400' }}"></span>
                        <span>{{ number_format($summary['offline']) }}</span>
                        <span class="text-[11px] opacity-80">Offline</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'los')"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'los' ? 'bg-rose-600 text-white border-transparent shadow-xs' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 dark:text-rose-400 border-rose-200 dark:border-rose-800/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'los' ? 'bg-white' : 'bg-rose-500' }}"></span>
                        <span>{{ number_format($summary['los']) }}</span>
                        <span class="text-[11px] opacity-80">LOS</span>
                    </button>

                    <button type="button" wire:click="$set('statusFilter', 'low_rx')"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-all border {{ $statusFilter === 'low_rx' ? 'bg-amber-600 text-white border-transparent shadow-xs' : 'bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:hover:bg-amber-900/50 dark:text-amber-400 border-amber-200 dark:border-amber-800/60' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'low_rx' ? 'bg-white' : 'bg-amber-500' }}"></span>
                        <span>{{ number_format($summary['low_rx']) }}</span>
                        <span class="text-[11px] opacity-80">Low RX</span>
                    </button>
                </div>
            </div>

            {{-- Top Actions --}}
            <div class="flex items-center gap-2 self-start md:self-auto">
                <button wire:click="cleanupDuplicates" wire:confirm="Sistem akan otomatis membersihkan baris ONU duplikat dan mempertahankan data ONU yang valid. Lanjutkan?" type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                    <span class="material-symbols-outlined notranslate" style="font-size:16px" translate="no">cleaning_services</span>
                    Bersihkan Duplikat
                </button>
                <button wire:click="deleteAllOfflineOnu" wire:confirm="Yakin ingin menghapus SEMUA ONU Offline / Orphan ({{ $summary['offline'] }} ONU) dari database lokal? (Ini tidak menghapus dari OLT fisik)" type="button" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                    <span class="material-symbols-outlined notranslate" style="font-size:16px" translate="no">delete_sweep</span>
                    Hapus Offline
                </button>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar: Search & Select Filters --}}
    <div class="flex-none px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700/80">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2.5">
            {{-- Search Bar --}}
            <div class="relative w-full lg:w-72">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">search</span>
                </div>
                <input type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Cari SN, MAC, atau Pelanggan..."
                    class="w-full pl-8 pr-7 py-1.5 text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors placeholder:text-slate-400"
                />
                @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">close</span>
                </button>
                @endif
            </div>

            {{-- Dropdown Filters Group --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- OLT Filter --}}
                <select wire:model.live="oltFilter" class="h-8 px-2.5 pr-7 text-xs bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors cursor-pointer">
                    <option value="">Semua OLT</option>
                    @foreach($olts as $olt)
                    <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                    @endforeach
                </select>

                {{-- PON Filter (if OLT is selected) --}}
                @if(!empty($oltFilter) && $oltFilter !== 'all')
                <select wire:model.live="ponFilter" class="h-8 px-2.5 pr-7 text-xs bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors cursor-pointer">
                    <option value="">Semua PON</option>
                    @foreach($ponPorts as $pon)
                    <option value="{{ $pon->id }}">{{ $pon->name }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="h-8 px-2.5 pr-7 text-xs bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="los">LOS / Critical RX</option>
                    <option value="low_rx">Low RX (-30 s/d -27)</option>
                </select>

                {{-- Rows Per Page --}}
                <select wire:model.live="perPage" class="h-8 px-2.5 pr-7 text-xs bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors cursor-pointer">
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                    <option value="0">Semua</option>
                </select>

                {{-- Reset Filter Button --}}
                @if($search !== '' || $statusFilter !== 'all' || $oltFilter !== '' || $ponFilter !== '')
                <button type="button" wire:click="resetFilters" class="h-8 px-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-1" title="Reset Semua Filter">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">restart_alt</span>
                    Reset
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="flex-1 overflow-auto ">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
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
                    <th class="p-3 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($onus as $onu)
                @php
                    $st = $this->getOnuStatus($onu);
                    $badge = match($st) {
                        'ONLINE' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'LOW_RX' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                        'LOS'    => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                        default  => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400',
                    };
                    $rxClass = $onu->rx_power_dbm < -30 ? 'text-red-500' : ($onu->rx_power_dbm < -27 ? 'text-yellow-500' : 'text-emerald-500');
                @endphp
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors ">
                    <td class="p-3 font-mono text-slate-800 dark:text-slate-200 font-medium">{{ $onu->serial_number }}</td>
                    <td class="p-3 font-mono text-slate-500 dark:text-slate-400">{{ $onu->mac_address ?? $onu->acsDevice?->mac_address ?? '-' }}</td>
                    <td class="p-3 font-mono text-slate-500 dark:text-slate-400">{{ $onu->acsDevice?->ip_address ?? '-' }}</td>
                    <td class="p-3 text-slate-500 dark:text-slate-400">
                        <span class="text-slate-500 dark:text-slate-400">{{ $onu->olt->name ?? '-' }}</span>
                        <span class="text-slate-500 dark:text-slate-400 opacity-60">/</span>
                        <span class="font-mono text-slate-500 dark:text-slate-400">{{ $onu->ponPort->name ?? '-' }}</span>
                    </td>
                    <td class="p-3 text-slate-500 dark:text-slate-400">
                        @if($editingOnuId === $onu->id)
                            <div class="flex items-center gap-1">
                                <select wire:model="selectedCustomerId" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 pr-6 text-xs rounded border focus:outline-none w-40">
                                    <option value="">- Kosongkan -</option>
                                    @foreach($this->allCustomers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                                    @endforeach
                                </select>
                                <button wire:click="assignCustomer({{ $onu->id }})" class="p-1 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded" title="Simpan">
                                    <span class="material-symbols-outlined notranslate" style="font-size:14px">check</span>
                                </button>
                                <button wire:click="cancelEditCustomer" class="p-1 bg-red-100 hover:bg-red-200 text-red-600 rounded" title="Batal">
                                    <span class="material-symbols-outlined notranslate" style="font-size:14px">close</span>
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-2 group cursor-pointer" wire:click="editCustomer({{ $onu->id }}, '{{ $onu->customerService->customer_id ?? '' }}')">
                                <span>{{ $onu->customerService->customer->name ?? '-' }}</span>
                                <span class="material-symbols-outlined notranslate opacity-0 group-hover:opacity-100 text-slate-400 hover:text-blue-500 transition-opacity" style="font-size:14px">edit</span>
                            </div>
                        @endif
                    </td>
                    <td class="p-3 font-mono font-medium {{ $rxClass }}">{{ $onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm-1) . ' dBm' : '-' }}</td>
                    <td class="p-3 font-mono text-slate-500 dark:text-slate-400">{{ $onu->tx_power_dbm !== null ? number_format($onu->tx_power_dbm-1) . ' dBm' : '-' }}</td>
                    <td class="p-3 font-mono text-slate-500 dark:text-slate-400">{{ $onu->temperature ?? '-' }}</td>
                    <td class="p-3">
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $st }}</span>
                    </td>
                    <td class="p-3 text-slate-500 dark:text-slate-400">{{ $onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-' }}</td>
                    <td class="p-3 flex items-center gap-3">
                        <a href="{{ route('isp.onus.show', $onu->id) }}" class="text-blue-500 hover:text-blue-400 text-sm font-medium">View</a>
                        <button type="button" wire:click="deleteOnu({{ $onu->id }})" wire:confirm="Yakin ingin menghapus ONU {{ $onu->serial_number ?: ('#' . $onu->id) }} dari database lokal?" class="text-red-500 hover:text-red-400 text-sm font-medium">
                            Hapus
                        </button>
                    </td>
                </tr>
                @empty
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="10" class="p-3 text-center text-slate-500 dark:text-slate-400 opacity-70">No ONUs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($onus->hasPages())
    <div class="flex-none px-3 py-2 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        {{ $onus->links(data: ['scrollTo' => false]) }}
    </div>
    @endif
</div>







