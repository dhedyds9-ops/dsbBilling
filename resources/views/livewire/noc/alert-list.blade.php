<div class="min-h-screen noc-bg noc-text noc-mono" wire:poll.60s>
    <div class="max-w-[1800px] mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold noc-text tracking-tight">NOC &middot; Alerts</h1>
                <p class="text-xs noc-muted mt-0.5"><span class="inline-block w-2 h-2 rounded-full noc-pulse bg-green-500 mr-1.5"></span>LIVE · auto refresh 60s</p>
            </div>
            <div class="flex items-center gap-2">
                @if(Route::has('noc.overview'))
                    <a href="{{ route('noc.overview') }}" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">← Overview</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
            <div class="noc-summary-card">
                <div class="noc-summary-label">Total</div>
                <div class="noc-summary-value noc-text">{{ $stats['total'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Active</div>
                <div class="noc-summary-value text-yellow-500">{{ $stats['active'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Critical</div>
                <div class="noc-summary-value text-red-500">{{ $stats['critical'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Warning</div>
                <div class="noc-summary-value text-yellow-500">{{ $stats['warning'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Resolved</div>
                <div class="noc-summary-value text-emerald-500">{{ $stats['resolved'] }}</div>
            </div>
        </div>

        <div class="noc-panel-bg border noc-border rounded p-3 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                <div class="md:col-span-4">
                    <label class="noc-section-label">Search</label>
                    <input wire:model.live.debounce.250ms="search" type="text" placeholder="Title- description- source…"
                           class="w-full noc-input rounded px-2.5 py-1.5 text-sm dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="noc-section-label">Level</label>
                    <select wire:model.live="filters.severity" class="w-full noc-input rounded px-2 py-1.5 text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="all">All</option>
                        <option value="critical">Critical</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="noc-section-label">Status</label>
                    <select wire:model.live="filters.status" class="w-full noc-input rounded px-2 py-1.5 text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="all">All</option>
                        <option value="open">Open</option>
                        <option value="acknowledged">Acknowledged</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="noc-section-label">Source</label>
                    <select wire:model.live="filters.source" class="w-full noc-input rounded px-2 py-1.5 text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="all">All</option>
                        @foreach($sourceTypes as $st)
                            <option value="{{ $st }}">{{ class_basename($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-1.5">
                    <button wire:click="resetPage" class="flex-1 px-2.5 py-1.5 text-xs border rounded noc-btn-outline transition">Reset</button>
                </div>
            </div>
        </div>

        <x-admin.table-card>
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                        <tr class="text-slate-500 dark:text-slate-400">
                            <th class="p-3 font-semibold" wire:click="sort('level')">
                                Level @if($sortField==='level') <span class="noc-muted">{{ $sortDirection==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="p-3 font-semibold" wire:click="sort('status')">
                                Status @if($sortField==='status') <span class="noc-muted">{{ $sortDirection==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="p-3 font-semibold" wire:click="sort('title')">
                                Title @if($sortField==='title') <span class="noc-muted">{{ $sortDirection==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="p-3 font-semibold" wire:click="sort('source_name')">
                                Source @if($sortField==='source_name') <span class="noc-muted">{{ $sortDirection==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="p-3 font-semibold" wire:click="sort('started_at')">
                                Started @if($sortField==='started_at') <span class="noc-muted">{{ $sortDirection==='asc'?'↑':'↓' }}</span> @endif
                            </th>
                            <th class="p-3 font-semibold">Ack By</th>
                            <th class="p-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($alerts as $alert)
                            @php
                                $levelClass = match($alert->level) {
                                    'critical' => 'noc-badge-critical',
                                    'warning'  => 'noc-badge-warning',
                                    'info'     => 'noc-badge-info',
                                    default    => 'noc-badge-unknown',
                                };
                                $statusClass = match($alert->status) {
                                    'open'         => 'noc-badge-critical',
                                    'acknowledged' => 'noc-badge-warning',
                                    'resolved'     => 'noc-badge-ok',
                                    default        => 'noc-badge-unknown',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors border-b noc-border noc-row-hover transition">
                                <td class="p-3"><span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $levelClass }}">{{ $alert->level }}</span></td>
                                <td class="p-3"><span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $statusClass }}">{{ $alert->status }}</span></td>
                                <td class="p-3">
                                    @if(Route::has('noc.alarms.show'))
                                        <a href="{{ route('noc.alarms.show', $alert->id) }}" class="noc-text hover:text-blue-500 transition">{{ $alert->title }}</a>
                                    @else
                                        <span class="noc-text">{{ $alert->title }}</span>
                                    @endif
                                    @if($alert->description)
                                        <div class="text-[10px] noc-muted mt-0.5 max-w-md truncate">{{ $alert->description }}</div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="noc-text-secondary">{{ $alert->source_name ?: class_basename($alert->source_type) }}</div>
                                    <div class="text-[10px] noc-muted opacity-60 mt-0.5">{{ $alert->source_type ? class_basename($alert->source_type).' #'.$alert->source_id : '—' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="noc-text-secondary">{{ $alert->started_at->format('M d H:i') }}</div>
                                    @if($alert->resolved_at)
                                        <div class="text-[10px] text-emerald-500 mt-0.5">Res: {{ $alert->resolved_at->format('M d H:i') }}</div>
                                    @elseif($alert->started_at)
                                        <div class="text-[10px] noc-muted mt-0.5">{{ $alert->started_at->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($alert->acknowledgedBy)
                                        <div class="noc-text-secondary">{{ $alert->acknowledgedBy->name }}</div>
                                        <div class="text-[10px] noc-muted mt-0.5">{{ $alert->acknowledged_at->format('M d H:i') }}</div>
                                    @else
                                        <span class="noc-muted opacity-60">—</span>
                                    @endif
                                </td>
                                <td class="p-3  text-right">
                                    <div class="inline-flex gap-1">
                                        @if($alert->status !== 'resolved')
                                            <button wire:click="acknowledgeAlert({{ $alert->id }})" class="px-2 py-1 text-[10px] border border-blue-700 rounded text-blue-500 hover:bg-blue-950 transition" onclick="return confirm('Acknowledge alarm #{{ $alert->id }}-')">ACK</button>
                                            <button wire:click="resolveAlert({{ $alert->id }})" class="px-2 py-1 text-[10px] border border-green-700 rounded text-emerald-500 hover:bg-green-950 transition" onclick="return confirm('Resolve alarm #{{ $alert->id }}-')">RESOLVE</button>
                                        @endif
                                        @if(Route::has('noc.alarms.show'))
                                            <a href="{{ route('noc.alarms.show', $alert->id) }}" class="px-2 py-1 text-[10px] border rounded noc-btn-outline transition">VIEW</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td colspan="7" class="p-3  text-center noc-muted text-xs">No alarms found matching current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($alerts->hasPages())
                <div class="px-3 py-2 border-t noc-border text-xs noc-muted">
                    {{ $alerts->onEachSide(1)->links() }}
                </div>
            @endif
        
    </x-admin.table-card>
</div>










