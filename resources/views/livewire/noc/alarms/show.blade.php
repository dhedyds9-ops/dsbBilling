<div class="min-h-screen noc-text noc-mono noc-bg" wire:poll.30s>
    <div class="max-w-[1400px] mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold noc-text tracking-tight">NOC · Alarm #{{ $alarm->id }}</h1>
                <p class="text-xs noc-muted mt-0.5"><span class="inline-block w-2 h-2 rounded-full noc-pulse bg-emerald-500 mr-1.5"></span>LIVE · auto refresh 30s</p>
            </div>
            <div class="flex items-center gap-2">
                @if(Route::has('noc.alarms.index'))
                    <a href="{{ route('noc.alarms.index') }}" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">← Alarms</a>
                @endif
                @if(Route::has('noc.overview'))
                    <a href="{{ route('noc.overview') }}" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">Overview</a>
                @endif
            </div>
        </div>

        @php
            $levelClass = match($alarm->level) {
                'critical' => 'noc-badge-critical',
                'warning'  => 'noc-badge-warning',
                'info'     => 'noc-badge-info',
                default    => 'bg-gray-700 noc-text',
            };
            $statusClass = match($alarm->status) {
                'open'         => 'noc-badge-critical',
                'acknowledged' => 'noc-badge-warning',
                'resolved'     => 'noc-badge-ok',
                default        => 'bg-gray-700 noc-text',
            };
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <div class="border noc-border rounded p-4 noc-panel-bg">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2.5 py-1 rounded text-[11px] font-semibold uppercase tracking-wider {{ $levelClass }}">{{ $alarm->level }}</span>
                            <span class="inline-block px-2.5 py-1 rounded text-[11px] font-semibold uppercase tracking-wider {{ $statusClass }}">{{ $alarm->status }}</span>
                        </div>
                        <div class="text-right text-[11px] noc-muted">
                            <div>UUID: <span class="noc-muted font-mono">{{ $alarm->uuid }}</span></div>
                        </div>
                    </div>

                    <h2 class="text-base font-semibold noc-text mb-2">{{ $alarm->title }}</h2>
                    @if($alarm->description)
                        <p class="text-sm noc-muted mb-4 whitespace-pre-wrap">{{ $alarm->description }}</p>
                    @endif

                    <div class="grid grid-cols-2 gap-3 text-xs pt-3 border-t noc-border">
                        <div>
                            <div class="noc-section-label">Source</div>
                            <div class="noc-text">{{ $alarm->source_name ?: '—' }}</div>
                            <div class="text-[10px] noc-muted mt-0.5">{{ $alarm->source_type ? class_basename($alarm->source_type).' #'.$alarm->source_id : 'Unspecified' }}</div>
                        </div>
                        <div>
                            <div class="noc-section-label">Started</div>
                            <div class="noc-text">{{ $alarm->started_at->format('M d- Y H:i:s') }}</div>
                            <div class="text-[10px] noc-muted mt-0.5">{{ $alarm->started_at->diffForHumans() }}</div>
                        </div>
                        <div>
                            <div class="noc-section-label">Acknowledged</div>
                            @if($alarm->acknowledgedBy)
                                <div class="noc-text">{{ $alarm->acknowledgedBy->name }}</div>
                                <div class="text-[10px] noc-muted mt-0.5">{{ $alarm->acknowledged_at->format('M d H:i') }}</div>
                            @else
                                <div class="noc-muted opacity-70">—</div>
                            @endif
                        </div>
                        <div>
                            <div class="noc-section-label">Resolved</div>
                            @if($alarm->resolved_at)
                                <div class="text-emerald-500">{{ $alarm->resolved_at->format('M d- Y H:i:s') }}</div>
                                <div class="text-[10px] noc-muted mt-0.5">{{ $alarm->resolved_at->diffForHumans() }}</div>
                            @else
                                <div class="noc-muted opacity-70">—</div>
                            @endif
                        </div>
                    </div>

                    @if($alarm->acknowledged_note)
                        <div class="mt-3 pt-3 border-t noc-border">
                            <div class="noc-section-label">Acknowledge Note</div>
                            <div class="text-sm noc-text-secondary border noc-border rounded px-3 py-2 noc-bg">{{ $alarm->acknowledged_note }}</div>
                        </div>
                    @endif

                    @if($alarm->metadata && !empty($alarm->metadata))
                        <div class="mt-3 pt-3 border-t noc-border">
                            <div class="noc-section-label mb-1.5">Metadata</div>
                            <pre class="text-[11px] noc-muted border noc-border rounded px-3 py-2 overflow-x-auto noc-scroll noc-bg">{{ json_encode($alarm->metadata- JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($alarm->status !== 'resolved')
                        <div class="border noc-border rounded p-4 noc-panel-bg">
                            <div class="noc-section-label mb-2 font-semibold text-blue-500">Acknowledge Alarm</div>
                            <textarea wire:model="ackNote" rows="2" placeholder="Optional note…"
                                      class="w-full border rounded px-2.5 py-1.5 text-xs noc-text focus:outline-none focus:border-primary-600 mb-2 noc-input dark:bg-slate-900 dark:text-slate-100"></textarea>
                            <button wire:click="acknowledge" class="w-full py-1.5 text-xs font-semibold bg-blue-900 hover:bg-blue-800 border border-blue-700 rounded text-blue-200 transition">Acknowledge</button>
                        </div>

                        <div class="border noc-border rounded p-4 noc-panel-bg">
                            <div class="noc-section-label mb-2 font-semibold text-emerald-500">Resolve Alarm</div>
                            <textarea wire:model="resolveNote" rows="2" placeholder="Resolution note…"
                                      class="w-full border rounded px-2.5 py-1.5 text-xs noc-text focus:outline-none focus:border-green-600 mb-2 noc-input dark:bg-slate-900 dark:text-slate-100"></textarea>
                            <button wire:click="resolve" class="w-full py-1.5 text-xs font-semibold bg-green-900 hover:bg-green-800 border border-green-700 rounded text-green-200 transition">Mark Resolved</button>
                        </div>
                    @else
                        <div class="md:col-span-2 border noc-border rounded p-4 text-center noc-panel-bg">
                            <span class="inline-block px-3 py-1.5 rounded text-xs font-semibold uppercase tracking-wider noc-badge-ok">Alarm Resolved</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="border noc-border rounded p-4 noc-panel-bg">
                    <div class="noc-section-label mb-2">Timeline</div>
                    <div class="space-y-3 text-xs">
                        <div class="flex gap-2">
                            <div class="flex flex-col items-center">
                                <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1"></div>
                                <div class="w-px flex-1 my-1 noc-progress"></div>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="noc-text-secondary">Alarm raised</div>
                                <div class="text-[10px] noc-muted mt-0.5">{{ $alarm->started_at->format('M d- Y H:i:s') }}</div>
                            </div>
                        </div>
                        @if($alarm->acknowledged_at)
                            <div class="flex gap-2">
                                <div class="flex flex-col items-center">
                                    <div class="w-2 h-2 rounded-full bg-yellow-500 mt-1"></div>
                                    <div class="w-px flex-1 my-1 noc-progress"></div>
                                </div>
                                <div class="flex-1 pb-2">
                                    <div class="noc-text-secondary">Acknowledged by <span class="text-blue-500">{{ $alarm->acknowledgedBy->name }}</span></div>
                                    <div class="text-[10px] noc-muted mt-0.5">{{ $alarm->acknowledged_at->format('M d- Y H:i:s') }}</div>
                                </div>
                            </div>
                        @endif
                        @if($alarm->resolved_at)
                            <div class="flex gap-2">
                                <div class="flex flex-col items-center">
                                    <div class="w-2 h-2 rounded-full bg-emerald-600 mt-1"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="noc-text-secondary">Resolved</div>
                                    <div class="text-[10px] noc-muted mt-0.5">{{ $alarm->resolved_at->format('M d- Y H:i:s') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($alarm->source)
                    <div class="border noc-border rounded p-4 noc-panel-bg">
                        <div class="noc-section-label mb-2">Source Object</div>
                        <div class="text-xs space-y-1.5">
                            <div class="flex justify-between"><span class="noc-muted">Type</span><span class="noc-text-secondary">{{ class_basename($alarm->source_type) }}</span></div>
                            <div class="flex justify-between"><span class="noc-muted">ID</span><span class="noc-text-secondary font-mono">#{{ $alarm->source_id }}</span></div>
                            @if(method_exists($alarm->source ?? 'name') || isset($alarm->source->name))
                                <div class="flex justify-between"><span class="noc-muted">Name</span><span class="noc-text-secondary">{{ $alarm->source->name }}</span></div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>






