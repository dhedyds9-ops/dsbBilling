{{--
 NOC: Router Monitoring Index
--}}
<div class="h-full flex flex-col noc-bg">
    <div class="flex-none px-3 py-2 border-b noc-border flex items-center justify-between gap-3 flex-wrap noc-panel-bg">
        <div class="flex items-center gap-3">
            <h1 class="text-base font-bold noc-text">Router Monitoring</h1>
            <span class="noc-count-badge">{{ $summary['total'] }} Total</span>
            <span class="noc-count-online">{{ $summary['online'] }} Online</span>
            <span class="noc-count-offline">{{ $summary['offline'] }} Offline</span>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="statusFilter" class="px-2 py-1 text-xs rounded border focus:outline-none noc-input dark:bg-slate-900 dark:text-slate-100">
                <option value="all">All Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Name / IP / Model…" class="px-2 py-1 text-xs rounded border focus:outline-none w-56 noc-input dark:bg-slate-900 dark:text-slate-100">
        </div>
    </div>

    <div class="flex-1 overflow-auto noc-scroll">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
                    <th class="p-3 font-semibold" wire:click="sort('name')">Name {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold" wire:click="sort('ip_address')">IP Address {!! $sortField === 'ip_address' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">Vendor / Model</th>
                    <th class="p-3 font-semibold">POP</th>
                    <th class="p-3 font-semibold">CPU</th>
                    <th class="p-3 font-semibold">Memory</th>
                    <th class="p-3 font-semibold">Uptime</th>
                    <th class="p-3 font-semibold" wire:click="sort('status')">Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">Last Seen</th>
                    <th class="p-3 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($routers as $r)
                @php
                    $log = $r->latestMonitoringLog;
                    $stale = $log && $log->created_at->diffInMinutes(now()) > 5;
                    if (!$log) { $status = 'UNKNOWN'; }
                    elseif (!$log->is_online) { $status = 'OFFLINE'; }
                    elseif ($stale) { $status = 'STALE'; }
                    elseif ($log->cpu_load > 80) { $status = 'WARNING'; }
                    else { $status = 'ONLINE'; }
                    $badge = match($status) {
                        'ONLINE' => 'noc-badge-online',
                        'WARNING' => 'noc-badge-warning',
                        'OFFLINE' => 'noc-badge-offline',
                        default => 'noc-badge-unknown',
                    };
                @endphp
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover">
                    <td class="p-3  font-medium noc-text">{{ $r->name }}</td>
                    <td class="p-3  noc-mono noc-muted">{{ $r->ip_address }}</td>
                    <td class="p-3  noc-muted">{{ $r->vendor->name ?? '-' }} / {{ $r->model ?? '-' }}</td>
                    <td class="p-3  noc-muted">{{ $r->pop->name ?? '-' }}</td>
                    <td class="p-3  noc-mono {{ $log && $log->cpu_load > 80 ? 'text-red-500' : 'text-emerald-500' }}">{{ $log->cpu_load ? $log->cpu_load . '%' : '-' }}</td>
                    <td class="p-3  noc-mono noc-muted">
                        @if($log && $log->total_memory)
                            {{ round((1 - $log->free_memory / $log->total_memory) * 100) }}%
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-3  noc-mono noc-muted">{{ $log->uptime ?? '-' }}</td>
                    <td class="p-3">
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $status }}</span>
                    </td>
                    <td class="p-3  noc-muted">{{ $log ? $log->created_at->diffForHumans() : ($r->last_seen_at ? $r->last_seen_at->diffForHumans() : '-') }}</td>
                    <td class="p-3">
                        <a href="{{ route('noc.routers.show', $r->id) }}" class="text-blue-500 hover:text-blue-400">View</a>
                    </td>
                </tr>
                @empty
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="10" class="p-3  text-center noc-muted opacity-70">No routers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($routers->hasPages())
    <div class="flex-none px-3 py-2 border-t noc-panel-bg noc-border">
        {{ $routers->links(data: ['scrollTo' => false]) }}
    </div>
    @endif
</div>






