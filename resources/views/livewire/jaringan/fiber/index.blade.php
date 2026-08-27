<?php
/** @var \App\Livewire\Jaringan\Fiber\Index $this */
/** @var mixed $rows */
$summaryItems = $this->getSummaryItems();
$toolbarActions = $this->getToolbarActions();
$bulkActions = $this->getBulkActions();
$filterConfig = $this->getFilterConfig();
$tabCounts = [
    'olt' => $this->summary['total_olt'] ?? null,
    'onu' => $this->summary['total_onu'] ?? null,
    'odp' => $this->summary['odp_aktif'] ?? null,
    'odc' => $this->summary['odc_aktif'] ?? null,
];
$tabsWithCounts = [];
foreach ($this->tabs as $k => $label) {
    $c = $tabCounts[$k] ?? null;
    $tabsWithCounts[$k] = $c !== null ? ['label' => $label, 'count' => $c] : $label;
}
?>
<div class="flex flex-col h-full min-h-0 bg-slate-50 dark:bg-slate-900">

    @include('partials.enterprise.list-toolbar', [
        'title' => 'Jaringan > Fiber & ONU',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari nama, SN, host, alamat...',
        'showFiltersToggle' => true,
        'tabs' => $tabsWithCounts,
    ])

    @include('partials.enterprise.summary-cards', ['items' => $summaryItems])

    @if ($this->showFilters)
        @include('partials.enterprise.filters', ['filters' => $filterConfig])
    @endif

    @include('partials.enterprise.bulk-bar', ['bulkActions' => $bulkActions])

    @if ($this->errorMessage)
        <div class="px-3 py-2 bg-red-50 border-b border-red-100 dark:bg-red-900/30 dark:border-red-800 text-red-700 dark:text-red-200 text-sm">
            {{ $this->errorMessage }}
        </div>
    @endif

    @if ($this->loading)
        <div class="flex-1 flex items-center justify-center py-16">
            <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Memuat data...
            </div>
        </div>
    @else
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="w-10 px-3 py-2">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600">
                        </th>
                        @if ($this->activeTab === 'olt')
                            <th class="px-3 py-2 text-left cursor-pointer hover:text-slate-900" wire:click="sortBy('id')">ID <x-sort-ind :field="'id'" :current="$this->sortField" :dir="$this->sortDirection" /></th>
                            <th class="px-3 py-2 text-left cursor-pointer hover:text-slate-900" wire:click="sortBy('name')">Nama <x-sort-ind :field="'name'" :current="$this->sortField" :dir="$this->sortDirection" /></th>
                            <th class="px-3 py-2 text-left">Host</th>
                            <th class="px-3 py-2 text-left">POP</th>
                            <th class="px-3 py-2 text-right">Port PON</th>
                            <th class="px-3 py-2 text-right">Total ONU</th>
                            <th class="px-3 py-2 text-right">ONU Online</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">CPU%</th>
                            <th class="px-3 py-2 text-right">MEM%</th>
                        @elseif ($this->activeTab === 'onu')
                            <th class="px-3 py-2 text-left">SN</th>
                            <th class="px-3 py-2 text-left">Nomor Seri</th>
                            <th class="px-3 py-2 text-left">Pelanggan</th>
                            <th class="px-3 py-2 text-left">OLT</th>
                            <th class="px-3 py-2 text-right">PON Port</th>
                            <th class="px-3 py-2 text-left">ODP</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">RX(dBm)</th>
                            <th class="px-3 py-2 text-right">TX(dBm)</th>
                            <th class="px-3 py-2 text-left">Last Reg</th>
                        @elseif ($this->activeTab === 'odp')
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Lokasi</th>
                            <th class="px-3 py-2 text-left">POP</th>
                            <th class="px-3 py-2 text-left">OLT</th>
                            <th class="px-3 py-2 text-left">Splitter</th>
                            <th class="px-3 py-2 text-right">Port Tot/Used</th>
                        @elseif ($this->activeTab === 'odc')
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Lokasi</th>
                            <th class="px-3 py-2 text-left">POP</th>
                            <th class="px-3 py-2 text-left">Rak</th>
                            <th class="px-3 py-2 text-right">Port Tot/Used</th>
                        @elseif ($this->activeTab === 'pop')
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Alamat</th>
                            <th class="px-3 py-2 text-left">Koordinat</th>
                            <th class="px-3 py-2 text-right">OLT Total</th>
                        @elseif ($this->activeTab === 'fiber')
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Awal → Akhir</th>
                            <th class="px-3 py-2 text-left">Tipe</th>
                            <th class="px-3 py-2 text-right">Panjang</th>
                            <th class="px-3 py-2 text-right">Loss(dB)</th>
                        @elseif ($this->activeTab === 'los')
                            <th class="px-3 py-2 text-left">ONU SN</th>
                            <th class="px-3 py-2 text-left">Pelanggan</th>
                            <th class="px-3 py-2 text-left">OLT</th>
                            <th class="px-3 py-2 text-left">Sejak LOS</th>
                            <th class="px-3 py-2 text-right">Durasi</th>
                            <th class="px-3 py-2 text-left">Severity</th>
                        @endif
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @php
                        $pageRows = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator ? $rows->items() : (is_array($rows) ? $rows : $rows->all());
                    @endphp
                    @forelse ($pageRows as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <td class="px-3 py-2">
                                <input type="checkbox" wire:model.live="selected" value="{{ (string) $r->id }}" class="rounded border-slate-300 dark:border-slate-600">
                            </td>
                            @if ($this->activeTab === 'olt')
                                <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $r->id }}</td>
                                <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-100">{{ $r->name }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->host }}</td>
                                <td class="px-3 py-2">{{ $r->pop->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $r->ponPorts->count() ?? 0 }}</td>
                                <td class="px-3 py-2 text-right">{{ $r->onus_count ?? 0 }}</td>
                                <td class="px-3 py-2 text-right">{{ $r->onus_online ?? 0 }}</td>
                                <td class="px-3 py-2"><x-status-badge :status="$r->status" /></td>
                                <td class="px-3 py-2 text-right"><x-progress-bar :val="$r->cpu_pct ?? 0" color="blue" /></td>
                                <td class="px-3 py-2 text-right"><x-progress-bar :val="$r->mem_pct ?? 0" color="amber" /></td>
                            @elseif ($this->activeTab === 'onu')
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->code ?? $r->id }}</td>
                                <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $r->serial_number }}</td>
                                <td class="px-3 py-2">{{ $r->customer->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $r->olt->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $r->pon_port }}</td>
                                <td class="px-3 py-2">{{ $r->odp->name ?? '-' }}</td>
                                <td class="px-3 py-2"><x-status-badge :status="$r->status" /></td>
                                <td class="px-3 py-2 text-right font-mono text-xs">{{ $r->rx_power ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs">{{ $r->tx_power ?? '-' }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500">{{ $r->last_registered_at ? \Illuminate\Support\Carbon::parse($r->last_registered_at)->diffForHumans() : '-' }}</td>
                            @elseif ($this->activeTab === 'odp')
                                <td class="px-3 py-2 font-medium">{{ $r->name }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500">{{ $r->location ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $r->pop->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $r->olt->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $r->splitter->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs">{{ $r->port_total ?? 0 }}/{{ $r->port_used ?? 0 }}</td>
                            @elseif ($this->activeTab === 'odc')
                                <td class="px-3 py-2 font-medium">{{ $r->name }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500">{{ $r->location ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $r->pop->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $r->rack->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs">{{ $r->port_total ?? 0 }}/{{ $r->port_used ?? 0 }}</td>
                            @elseif ($this->activeTab === 'pop')
                                <td class="px-3 py-2 font-medium">{{ $r->name }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500 max-w-xs truncate">{{ $r->address }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ ($r->latitude ?? '-') . ', ' . ($r->longitude ?? '-') }}</td>
                                <td class="px-3 py-2 text-right">{{ $r->olts->count() ?? 0 }}</td>
                            @elseif ($this->activeTab === 'fiber')
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->code }}</td>
                                <td class="px-3 py-2 text-xs">{{ $r->startOdc->name ?? '-' }} → {{ $r->endOdc->name ?? '-' }}</td>
                                <td class="px-3 py-2"><x-chip :label="$r->type" color="slate" /></td>
                                <td class="px-3 py-2 text-right">{{ $r->length }} m</td>
                                <td class="px-3 py-2 text-right font-mono text-xs">{{ $r->loss_db ?? 0 }} dB</td>
                            @elseif ($this->activeTab === 'los')
                                <td class="px-3 py-2 font-mono text-xs text-red-600 dark:text-red-400">{{ $r->serial_number }}</td>
                                <td class="px-3 py-2">{{ $r->customer->name ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $r->olt->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-xs">{{ $r->last_seen_at ? \Illuminate\Support\Carbon::parse($r->last_seen_at)->diffForHumans() : '-' }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs">{{ $r->los_duration_hours ?? 0 }}j</td>
                                <td class="px-3 py-2"><x-severity-badge :severity="$r->los_severity ?? 'high'" /></td>
                            @endif
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-0.5">
                                    <button wire:click="rowEdit({{ $r->id }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded dark:hover:bg-blue-900/30" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="rowDetail({{ $r->id }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded dark:hover:bg-blue-900/30" title="Detail">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="rowSync({{ $r->id }})" class="p-1 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded dark:hover:bg-emerald-900/30" title="Sync">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                    <button wire:click="rowShowMap({{ $r->id }})" class="p-1 text-slate-500 hover:text-cyan-600 hover:bg-cyan-50 rounded dark:hover:bg-cyan-900/30" title="Map">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    @if ($this->activeTab === 'onu')
                                        <button wire:click="rowTestLos({{ $r->id }})" class="p-1 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded dark:hover:bg-red-900/30" title="Test LOS">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                        </button>
                                    @endif
                                    @if (($r->status ?? '') === 'active')
                                        <button wire:click="rowDisable({{ $r->id }})" class="p-1 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded dark:hover:bg-amber-900/30" title="Disable">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        </button>
                                    @else
                                        <button wire:click="rowEnable({{ $r->id }})" class="p-1 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded dark:hover:bg-emerald-900/30" title="Enable">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <div class="font-medium">Data {{ $this->tabs[$this->activeTab] ?? '' }} kosong</div>
                                <div class="text-xs mt-1">Ubah filter atau buat data baru</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 flex items-center justify-between text-sm">
                <div class="text-slate-500 dark:text-slate-400 text-xs">
                    Menampilkan {{ $rows->firstItem() }}-{{ $rows->lastItem() }} dari {{ $rows->total() }}
                </div>
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="text-xs rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-1 px-2">
                        <option value="10">10 / hal</option>
                        <option value="25">25 / hal</option>
                        <option value="50">50 / hal</option>
                        <option value="100">100 / hal</option>
                    </select>
                    {{ $rows->links('livewire::simple-tailwind') }}
                </div>
            </div>
        @endif
    @endif

    @include('partials.enterprise.confirm-modal')

    @if ($this->showLosTest && $this->losTestResult)
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold">Hasil Test LOS ONU</h3>
                    <button @click="show = false; $wire.closeLosTest()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">SN:</span><span class="font-mono">{{ $this->losTestResult['serial_number'] }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Status:</span><x-status-badge :status="$this->losTestResult['status']" /></div>
                    <div class="flex justify-between"><span class="text-slate-500">RX Power:</span><span class="font-mono">{{ $this->losTestResult['rx_power_dbm'] ?? '-' }} dBm</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">TX Power:</span><span class="font-mono">{{ $this->losTestResult['tx_power_dbm'] ?? '-' }} dBm</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Diuji:</span><span class="text-xs">{{ $this->losTestResult['tested_at'] }}</span></div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end">
                    <button @click="show = false; $wire.closeLosTest()" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    @if ($this->showMapPopup && $this->mapData)
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold">Lokasi: {{ $this->mapData['name'] }}</h3>
                    <button @click="show = false; $wire.closeMapPopup()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4">
                    <div class="aspect-video bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-400 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_40%,#e0f2fe,transparent_50%),radial-gradient(circle_at_70%_60%,#fef3c7,transparent_50%)] dark:bg-[radial-gradient(circle_at_30%_40%,#1e293b,transparent_50%),radial-gradient(circle_at_70%_60%,#334155,transparent_50%)]"></div>
                        <div class="relative z-10 text-center">
                            <svg class="w-10 h-10 mx-auto text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
                            <div class="font-mono text-xs mt-2">{{ $this->mapData['lat'] }}, {{ $this->mapData['lng'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-2">
                    <button class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700">Open GIS</button>
                    <button @click="show = false; $wire.closeMapPopup()" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    @once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fiberIndex', () => ({
                init() {
                    Livewire.hook('commit', ({ component, succeed }) => {
                        if (component.name !== 'jaringan.fiber.index') return;
                        succeed(() => {});
                    });
                }
            }));
        });
    </script>
    @endpush
    @endonce
</div>
