{{--
 NOC: Active Sessions Index
--}}
<div class="h-full flex flex-col noc-bg">
    <div class="flex-none px-3 py-2 border-b noc-border flex flex-col sm:flex-row items-center justify-between gap-3 noc-panel-bg">
        <div class="flex items-center gap-3">
            <h1 class="text-base font-bold noc-text">Active Sessions</h1>
            
            <div class="flex bg-slate-800 rounded p-0.5 ml-2">
                <button wire:click="setTab('pppoe')" class="px-3 py-1 text-xs font-semibold rounded {{ $activeTab === 'pppoe' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200' }}">PPPoE</button>
                <button wire:click="setTab('hotspot')" class="px-3 py-1 text-xs font-semibold rounded {{ $activeTab === 'hotspot' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200' }}">Hotspot</button>
            </div>

            <span class="noc-count-info ml-2">{{ number_format($summary['total']) }} Online</span>
            <span class="text-xs noc-muted">TX: <span class="noc-mono text-blue-500">{{ $summary['tx'] ? number_format($summary['tx'] / 1024 / 1024, 2) . ' Mbps' : '-' }}</span></span>
            <span class="text-xs noc-muted">RX: <span class="noc-mono text-emerald-500">{{ $summary['rx'] ? number_format($summary['rx'] / 1024 / 1024, 2) . ' Mbps' : '-' }}</span></span>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="routerFilter" class="px-2 py-1 text-xs rounded border focus:outline-none noc-input dark:bg-slate-900 dark:text-slate-100">
                <option value="">All Routers</option>
                @foreach($routers as $r)
                <option value="{{ $r->id }}">{{ $r->name }}</option>
                @endforeach
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Username / IP / Pelanggan…" class="px-2 py-1 text-xs rounded border focus:outline-none w-56 noc-input dark:bg-slate-900 dark:text-slate-100">
        </div>
    </div>

    <div class="flex-1 overflow-auto noc-scroll">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
                    <th class="p-3 font-semibold" wire:click="sort('username')">Username {!! $sortField === 'username' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">Pelanggan</th>
                    <th class="p-3 font-semibold">Router</th>
                    <th class="p-3 font-semibold" wire:click="sort('address')">IP {!! $sortField === 'address' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                    <th class="p-3 font-semibold">Server / MAC</th>
                    <th class="p-3 font-semibold">Rate Up</th>
                    <th class="p-3 font-semibold">Rate Down</th>
                    <th class="p-3 font-semibold">Uptime</th>
                    <th class="p-3 font-semibold" wire:click="sort('session_started_at')">Started {!! $sortField === 'session_started_at' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : '' !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($sessions as $s)
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover">
                    <td class="p-3  font-medium noc-text">{{ $s->username }}</td>
                    <td class="p-3  noc-muted">{{ $s->customerService->customer->name ?? '-' }}</td>
                    <td class="p-3  noc-muted">{{ $s->router->name ?? '-' }}</td>
                    <td class="p-3  noc-mono noc-muted">{{ $s->address }}</td>
                    <td class="p-3  noc-mono noc-muted">{{ $s->server ?? '-' }} / {{ $s->caller_id ?? '-' }}</td>
                    <td class="p-3  noc-mono noc-muted">{{ $s->rate_up ? number_format($s->rate_up / 1000000, 1) . ' Mbps' : '-' }}</td>
                    <td class="p-3  noc-mono noc-muted">{{ $s->rate_down ? number_format($s->rate_down / 1000000, 1) . ' Mbps' : '-' }}</td>
                    <td class="p-3  noc-mono noc-muted">{{ $s->uptime ?? '-' }}</td>
                    <td class="p-3  noc-muted">{{ $s->session_started_at?->diffForHumans() ?? '-' }}</td>
                </tr>
                @empty
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="9" class="p-3  text-center noc-muted opacity-70">No active sessions</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
    <div class="flex-none px-3 py-2 border-t noc-panel-bg noc-border">
        {{ $sessions->links(data: ['scrollTo' => false]) }}
    </div>
    @endif
</div>






