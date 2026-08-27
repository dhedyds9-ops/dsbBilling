<?php
/** @var \App\Livewire\Jaringan\Monitoring\Index $this */
/** @var mixed $rows */
$summaryItems = $this->getSummaryItems();
$toolbarActions = $this->getToolbarActions();
$bulkActions = $this->getBulkActions();
$filterConfig = $this->getFilterConfig();
?>
<div class="flex flex-col h-full min-h-0 bg-slate-50 dark:bg-slate-900">

    @include('partials.enterprise.list-toolbar', [
        'title' => 'Jaringan > Monitoring',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari username, IP, MAC, router...',
        'showFiltersToggle' => true,
        'tabs' => $this->tabs,
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
    @elseif ($this->activeTab === 'realtime')
        <div class="flex-1 overflow-auto min-h-0" wire:poll.15s="refreshAll">
            <div class="p-3 grid grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-sm">Live Counters</h3>
                        <div class="flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Auto-refresh 15s</span>
                            <span class="text-slate-400 ml-1">{{ \Illuminate\Support\Carbon::parse($this->summary['updated_at'] ?? now())->format('H:i:s') }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach (['router_up'=>'Router UP','router_down'=>'Router DOWN','pppoe_online'=>'PPPoE','hotspot_online'=>'Hotspot'] as $k=>$label)
                            @php
                                $color = $k === 'router_down' ? 'red' : ($k === 'router_up' ? 'emerald' : ($k === 'pppoe_online' ? 'blue' : 'purple'));
                            @endphp
                            <div class="p-2 rounded-lg border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                                <div class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</div>
                                <div class="text-2xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400 tabular-nums mt-0.5">
                                    {{ $this->summary[$k] ?? 0 }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-950/40 dark:to-cyan-950/40 border border-blue-100 dark:border-blue-900">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Total Bandwidth</div>
                            <div class="text-xl font-bold text-cyan-700 dark:text-cyan-300 tabular-nums">{{ $this->summary['total_bandwidth_mbps'] ?? 0 }} <span class="text-xs font-normal">Mbps</span></div>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/40 border border-amber-100 dark:border-amber-900">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Alarms</div>
                            <div class="text-xl font-bold text-amber-700 dark:text-amber-300 tabular-nums">{{ $this->summary['alarms_active'] ?? 0 }}</div>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-slate-50 to-zinc-100 dark:from-slate-800 dark:to-zinc-900 border border-slate-200 dark:border-slate-700">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Ticket Open</div>
                            <div class="text-xl font-bold text-slate-700 dark:text-slate-200 tabular-nums">{{ $this->summary['ticket_open'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                    <h3 class="font-semibold text-sm mb-2">Recent Alarms</h3>
                    <div class="space-y-1 max-h-96 overflow-auto">
                        @forelse ($this->recentAlarms ?? [] as $a)
                            <div class="flex gap-2 p-2 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700/50 border border-slate-100 dark:border-slate-700/50">
                                <div class="flex-shrink-0">
                                    @php
                                        $sevColor = match($a['severity'] ?? 'info') {
                                            'critical' => 'bg-red-500', 'high' => 'bg-orange-500', 'warning' => 'bg-amber-500', 'info' => 'bg-blue-500', default => 'bg-slate-500'
                                        };
                                    @endphp
                                    <span class="inline-block w-2 h-2 rounded-full mt-1.5 {{ $sevColor }}"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-medium truncate">{{ $a['router'] }}</span>
                                        <span class="text-[10px] uppercase text-slate-400">{{ $a['type'] }}</span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $a['message'] }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ \Illuminate\Support\Carbon::parse($a['created_at'])->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 text-center py-8">Tidak ada alarm</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @elseif ($this->activeTab === 'bandwidth')
        <div class="flex-1 overflow-auto min-h-0">
            <div class="p-3 space-y-3">
                @php
                    $byRouter = collect($this->bandwidthData ?? [])->groupBy('router_name');
                @endphp
                @foreach ($byRouter as $routerName => $ifs)
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                        <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <h4 class="font-semibold text-sm">{{ $routerName }}</h4>
                            @php
                                $tot = $ifs->sum('in_mbps') + $ifs->sum('out_mbps');
                            @endphp
                            <span class="text-xs text-cyan-600 dark:text-cyan-400 font-medium">{{ round($tot) }} Mbps total</span>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="text-[10px] uppercase text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/40">
                                <tr>
                                    <th class="px-3 py-1.5 text-left">Interface</th>
                                    <th class="px-3 py-1.5 w-48">Download (sparkline)</th>
                                    <th class="px-3 py-1.5 text-right">In Mbps</th>
                                    <th class="px-3 py-1.5 w-48">Upload (sparkline)</th>
                                    <th class="px-3 py-1.5 text-right">Out Mbps</th>
                                    <th class="px-3 py-1.5 text-right">95th</th>
                                    <th class="px-3 py-1.5 w-28 text-right">Pct</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @foreach ($ifs as $i)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                        <td class="px-3 py-2 font-mono text-xs">{{ $i['interface'] }}</td>
                                        <td class="px-3 py-2">@include('livewire.partials.sparkline', ['data' => $i['spark_in'], 'color' => '#22d3ee'])</td>
                                        <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-cyan-600 dark:text-cyan-400">{{ $i['in_mbps'] }}</td>
                                        <td class="px-3 py-2">@include('livewire.partials.sparkline', ['data' => $i['spark_out'], 'color' => '#a78bfa'])</td>
                                        <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-purple-600 dark:text-purple-400">{{ $i['out_mbps'] }}</td>
                                        <td class="px-3 py-2 text-right font-mono text-xs tabular-nums">{{ $i['pct_95th_in'] }}/{{ $i['pct_95th_out'] }}</td>
                                        <td class="px-3 py-2"><x-progress-bar :val="min(100, $i['pct_util'])" :color="$i['pct_util'] > 80 ? 'red' : ($i['pct_util'] > 50 ? 'amber' : 'emerald')" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif ($this->activeTab === 'resources')
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="px-3 py-2 text-left">Router</th>
                        <th class="px-3 py-2 text-left">Host</th>
                        <th class="px-3 py-2 text-right">CPU%</th>
                        <th class="px-3 py-2 text-right">MEM%</th>
                        <th class="px-3 py-2 text-right">Disk%</th>
                        <th class="px-3 py-2 text-right">Temp</th>
                        <th class="px-3 py-2 text-left">Uptime</th>
                        <th class="px-3 py-2 text-left">FW Ver</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($this->resourcesData ?? [] as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-3 py-2 font-medium">{{ $r['name'] }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $r['host'] }}</td>
                            <td class="px-3 py-2 w-32"><x-progress-bar :val="$r['cpu_pct']" :color="$r['cpu_pct'] > 85 ? 'red' : ($r['cpu_pct'] > 60 ? 'amber' : 'emerald')" /></td>
                            <td class="px-3 py-2 w-32"><x-progress-bar :val="$r['mem_pct']" :color="$r['mem_pct'] > 85 ? 'red' : ($r['mem_pct'] > 60 ? 'amber' : 'blue')" /></td>
                            <td class="px-3 py-2 w-32"><x-progress-bar :val="$r['disk_pct']" color="slate" /></td>
                            <td class="px-3 py-2 text-right font-mono text-xs tabular-nums {{ $r['temp_c'] > 65 ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">{{ $r['temp_c'] }}°C</td>
                            <td class="px-3 py-2 text-xs text-slate-500">{{ $r['uptime'] }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $r['firmware_version'] }}</td>
                            <td class="px-3 py-2"><x-status-badge :status="$r['status']" /></td>
                            <td class="px-3 py-2 text-right">
                                <button wire:click="refreshRouter({{ $r['id'] }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-md border border-slate-200 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Refresh
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-6 py-16 text-center text-slate-500">Tidak ada router</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="w-10 px-3 py-2"><input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600"></th>
                        @if ($this->activeTab === 'pppoe')
                            <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('username')">Username</th>
                            <th class="px-3 py-2 text-left">Pelanggan</th>
                            <th class="px-3 py-2 text-left">Router</th>
                            <th class="px-3 py-2 text-left">IP</th>
                            <th class="px-3 py-2 text-right">Uptime</th>
                            <th class="px-3 py-2 text-right">RX Mbps</th>
                            <th class="px-3 py-2 text-right">TX Mbps</th>
                            <th class="px-3 py-2 text-left">Session Start</th>
                        @else
                            <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('username')">Username</th>
                            <th class="px-3 py-2 text-left">Router</th>
                            <th class="px-3 py-2 text-left">IP</th>
                            <th class="px-3 py-2 text-left font-mono text-xs">MAC</th>
                            <th class="px-3 py-2 text-right">Uptime</th>
                            <th class="px-3 py-2 text-right">RX</th>
                            <th class="px-3 py-2 text-right">TX</th>
                            <th class="px-3 py-2 text-right">Bytes In/Out</th>
                        @endif
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @php
                        $pageRows = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator ? $rows->items() : (is_array($rows) ? $rows : $rows->all());
                    @endphp
                    @forelse ($pageRows as $r)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-3 py-2"><input type="checkbox" wire:model.live="selected" value="{{ (string) $r->id }}" class="rounded border-slate-300 dark:border-slate-600"></td>
                            @if ($this->activeTab === 'pppoe')
                                <td class="px-3 py-2 font-mono text-xs text-blue-600 dark:text-blue-400">{{ $r->username }}</td>
                                <td class="px-3 py-2 text-xs">{{ $r->pppoeUser?->customer?->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-xs">{{ $r->router?->name ?? '-' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->ip_address }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums">{{ $r->uptime }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-cyan-600 dark:text-cyan-400">{{ round(($r->download_rate ?? 0)/1e6, 2) }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-purple-600 dark:text-purple-400">{{ round(($r->upload_rate ?? 0)/1e6, 2) }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500">{{ $r->session_started_at ? \Illuminate\Support\Carbon::parse($r->session_started_at)->diffForHumans() : '-' }}</td>
                            @else
                                <td class="px-3 py-2 font-mono text-xs text-purple-600 dark:text-purple-400">{{ $r->username }}</td>
                                <td class="px-3 py-2 text-xs">{{ $r->router?->name ?? '-' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->ip_address }}</td>
                                <td class="px-3 py-2 font-mono text-[10px] text-slate-500">{{ $r->mac_address }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums">{{ $r->uptime }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-cyan-600 dark:text-cyan-400">{{ $r->rx_bytes }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-purple-600 dark:text-purple-400">{{ $r->tx_bytes }}</td>
                                <td class="px-3 py-2 text-right font-mono text-[10px] text-slate-500">{{ $r->bytes_in ?? 0 }} / {{ $r->bytes_out ?? 0 }}</td>
                            @endif
                            <td class="px-3 py-2 text-right">
                                <button wire:click="kickSession('{{ $r->id }}')" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-md bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-300 border border-red-100 dark:border-red-900">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    Kick
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                <div class="font-medium">Tidak ada sesi {{ $this->tabs[$this->activeTab] ?? '' }} online</div>
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
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    {{ $rows->links('livewire::simple-tailwind') }}
                </div>
            </div>
        @endif
    @endif

    @include('partials.enterprise.confirm-modal')
</div>
