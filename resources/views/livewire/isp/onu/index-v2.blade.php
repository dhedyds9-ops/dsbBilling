{{--
 NOC: ONU Monitoring Index
--}}
<div class="h-full flex flex-col bg-slate-50 dark:bg-slate-900">
    {{-- Summary Header --}}
    <div class="flex-none px-3 py-2 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3 flex-wrap bg-white dark:bg-slate-800">
        <div class="flex items-center gap-3">
            <h1 class="text-base font-bold text-slate-800 dark:text-slate-200">ONU Monitoring</h1>
            <span class="px-2 py-1 rounded bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 text-xs font-semibold">{{ $summary['total'] }} Total</span>
            <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-semibold">{{ $summary['online'] }} Online</span>
            <span class="px-2 py-1 rounded bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-400 text-xs font-semibold">{{ $summary['offline'] }} Offline</span>
            <span class="px-2 py-1 rounded bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-semibold">{{ $summary['los'] }} LOS</span>
            <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 text-xs font-semibold">{{ $summary['low_rx'] }} Low RX</span>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="oltFilter" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 pr-6 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="">All OLT</option>
                @foreach($olts as $olt)
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="{{ $olt->id }}">{{ $olt->name }}</option>
                @endforeach
            </select>
            
            @if(!empty($oltFilter) && $oltFilter !== 'all')
            <select wire:model.live="ponFilter" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 pr-6 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="">All PON</option>
                @foreach($ponPorts as $pon)
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="{{ $pon->id }}">{{ $pon->name }}</option>
                @endforeach
            </select>
            @endif
<select wire:model.live="statusFilter" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 pr-6 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="all">All Status</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="online">Online</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="offline">Offline</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="los">LOS / Critical RX</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="low_rx">Low RX (-30 to -27)</option>
            </select>
            <select wire:model.live="perPage" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 pr-6 text-xs rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="25">25 Baris</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="50">50 Baris</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="100">100 Baris</option>
                <option class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="0">All</option>
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="SN / MAC / Pelanggan" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 px-2 py-1 text-xs rounded border focus:outline-none w-56 dark:bg-slate-900 dark:text-slate-100">
            
            <button wire:click="deleteAllOfflineOnu" wire:confirm="Yakin ingin menghapus SEMUA ONU Offline ({{ $summary['offline'] }} ONU) dari database lokal? (Ini tidak menghapus dari OLT fisik)" type="button" class="bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 px-3 py-1 rounded text-xs font-semibold flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:14px">delete</span>
                Hapus Offline
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="flex-1 overflow-auto ">
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
                        'ONLINE' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'LOW_RX' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                        'LOS'    => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                        default  => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400',
                    };
                    $rxClass = $onu->rx_power_dbm < -30 ? 'text-red-500' : ($onu->rx_power_dbm < -27 ? 'text-yellow-500' : 'text-emerald-500');
                @endphp
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors ">
                    <td class="p-3 font-mono text-slate-800 dark:text-slate-200 font-medium">{{ $onu->serial_number }}</td>
                    <td class="p-3 text-slate-600 dark:text-slate-300">{{ $onu->name ?? '-' }}</td>
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
                        @if($st === 'OFFLINE')
                        <button type="button" wire:click="deleteOnu({{ $onu->id }})" wire:confirm="Yakin ingin menghapus ONU ini dari database? (Ini tidak akan menghapus ONU dari OLT)" class="text-red-500 hover:text-red-400 text-sm font-medium">
                            Hapus
                        </button>
                        @endif
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







