<div class="min-h-screen noc-bg noc-text noc-mono" wire:poll.30s>
    <div class="max-w-[1800px] mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold noc-text tracking-tight">NOC · Provisioning Monitor</h1>
                <p class="text-xs noc-muted mt-0.5"><span class="inline-block w-2 h-2 rounded-full noc-pulse bg-green-500 mr-1.5"></span>LIVE · auto refresh 30s · retry via PipelineOrchestrator</p>
            </div>
            <div class="flex items-center gap-2">
                @if(Route::has('noc.overview'))
                    <a href="{{ route('noc.overview') }}" class="px-3 py-1.5 text-xs border rounded noc-btn-outline">← Overview</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
            <div class="noc-summary-card">
                <div class="noc-summary-label">Queued</div>
                <div class="noc-summary-value text-blue-500">{{ $summary['queued'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Running</div>
                <div class="noc-summary-value text-yellow-500">{{ $summary['running'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Completed</div>
                <div class="noc-summary-value text-emerald-500">{{ $summary['completed'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Failed</div>
                <div class="noc-summary-value text-red-500">{{ $summary['failed'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Total</div>
                <div class="noc-summary-value noc-text">{{ $summary['total'] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-3 space-y-4">
                <div class="noc-panel-bg border noc-border rounded p-3">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                        <div class="md:col-span-7">
                            <label class="noc-section-label">Search</label>
                            <input wire:model.live.debounce.250ms="search" type="text" placeholder="UUID- customer name- code…"
                                   class="w-full noc-input border rounded px-2.5 py-1.5 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div class="md:col-span-3">
                            <label class="noc-section-label">Status</label>
                            <select wire:model.live="statusFilter" class="w-full noc-input border rounded px-2 py-1.5 text-sm dark:bg-slate-900 dark:text-slate-100">
                                <option value="all">All</option>
                                <option value="pending">Pending</option>
                                <option value="running">Running</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex gap-1.5">
                            <button wire:click="resetPage" class="flex-1 px-2.5 py-1.5 text-xs border rounded noc-btn-outline">Reset</button>
                        </div>
                    </div>
                </div>

                <x-admin.table-card>
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                                <tr class="text-slate-500 dark:text-slate-400">
                                    <th class="p-3 font-semibold">UUID</th>
                                    <th class="p-3 font-semibold">Customer</th>
                                    <th class="p-3 font-semibold">Status</th>
                                    <th class="p-3 font-semibold">Steps</th>
                                    <th class="p-3 font-semibold">Created</th>
                                    <th class="p-3 font-semibold">By</th>
                                    <th class="p-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($pipelines as $p)
                                    @php
                                        $statusClass = match($p->status) {
                                            'pending'   => 'noc-badge-info',
                                            'running'   => 'noc-badge-warning',
                                            'completed' => 'noc-badge-ok',
                                            'failed'    => 'noc-badge-critical',
                                            default     => 'noc-badge-unknown',
                                        };
                                        $customer = $p->serviceInstance?->customerService?->customer;
                                        $stepTotal = $p->steps->count();
                                        $stepDone  = $p->steps->where('status', 'completed')->count();
                                        $stepFail  = $p->steps->where('status', 'failed')->count();
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition">
                                        <td class="p-3">
                                            @if(Route::has('noc.provisioning.show'))
                                                <a href="{{ route('noc.provisioning.show', $p->id) }}" class="text-blue-500 hover:text-blue-400 font-mono text-[11px]">{{ substr($p->uuid, 0, 12) }}…</a>
                                            @else
                                                <span class="font-mono text-[11px] noc-muted">{{ substr($p->uuid, 0, 12) }}…</span>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            @if($customer)
                                                <div class="noc-text">{{ $customer->name }}</div>
                                                <div class="text-[10px] noc-muted mt-0.5">{{ $customer->code }}</div>
                                            @else
                                                <span class="noc-muted opacity-70">—</span>
                                            @endif
                                        </td>
                                        <td class="p-3"><span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $statusClass }}">{{ $p->status }}</span></td>
                                        <td class="p-3">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-20 h-1.5 noc-progress rounded overflow-hidden">
                                                    @if($stepTotal > 0)
                                                        <div class="h-full {{ $stepFail ? 'bg-red-500' : 'bg-emerald-500' }}" style="width: {{ ($stepDone / $stepTotal) * 100 }}%"></div>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] noc-muted">{{ $stepDone }}/{{ $stepTotal }}{{ $stepFail ? " · {$stepFail}✗" : '' }}</span>
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <div class="noc-text-secondary">{{ $p->created_at->format('M d H:i') }}</div>
                                            <div class="text-[10px] noc-muted mt-0.5">{{ $p->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="p-3  noc-muted">{{ $p->createdBy->name ?: '—' }}</td>
                                        <td class="p-3  text-right">
                                            <div class="inline-flex gap-1">
                                                @if($p->status === 'failed')
                                                    <button wire:click="retryPipeline({{ $p->id }})" onclick="return confirm('Retry pipeline #{{ $p->id }} via PipelineOrchestrator ?? ')"
                                                            class="px-2 py-1 text-[10px] border border-yellow-700 rounded text-yellow-500 hover:bg-yellow-950 transition">RETRY</button>
                                                @endif
                                                @if(Route::has('noc.provisioning.show'))
                                                    <a href="{{ route('noc.provisioning.show', $p->id) }}" class="px-2 py-1 text-[10px] border rounded noc-btn-outline">VIEW</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td colspan="7" class="p-3  text-center noc-muted text-xs">No provisioning pipelines matching current filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($pipelines->hasPages())
                        <div class="px-3 py-2 border-t noc-border noc-muted text-xs">
                            {{ $pipelines->onEachSide(1)->links() }}
                        </div>
                    @endif
                
    </x-admin.table-card>

            <div class="space-y-4">
                <div class="noc-panel-bg border noc-border rounded p-4">
                    <div class="text-[11px] uppercase tracking-wider text-red-500 mb-2 font-semibold flex items-center justify-between">
                        <span>Recent Failures</span>
                        <span class="text-[10px] font-normal noc-muted">Last {{ $recentFailures->count() }}</span>
                    </div>
                    <div class="space-y-2 text-xs max-h-96 overflow-y-auto noc-scroll pr-1">
                        @forelse($recentFailures as $f)
                            @php
                                $failedStep = $f->steps->first();
                                $customer   = $f->serviceInstance->customerService->customer;
                            @endphp
                            <div class="noc-subpanel-bg border noc-border rounded p-2.5">
                                <div class="flex items-center justify-between mb-1">
                                    @if(Route::has('noc.provisioning.show'))
                                        <a href="{{ route('noc.provisioning.show', $f->id) }}" class="text-red-500 font-mono text-[11px] hover:text-red-400">#{{ substr($f->uuid, 0, 8) }}</a>
                                    @else
                                        <span class="text-red-500 font-mono text-[11px]">#{{ substr($f->uuid, 0, 8) }}</span>
                                    @endif
                                    <span class="text-[10px] noc-muted">{{ $f->failed_at->diffForHumans() }}</span>
                                </div>
                                @if($customer)
                                    <div class="text-[11px] noc-text-secondary">{{ $customer->name }} <span class="noc-muted">({{ $customer->code }})</span></div>
                                @endif
                                @if($failedStep)
                                    <div class="text-[10px] mt-1">
                                        <span class="text-yellow-500">Step:</span> <span class="noc-muted">{{ $failedStep->step_name }}</span>
                                    </div>
                                    @if($failedStep->error_message)
                                        <div class="text-[10px] text-red-500 mt-0.5 truncate" title="{{ $failedStep->error_message }}">{{ $failedStep->error_message }}</div>
                                    @endif
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-[11px] noc-muted opacity-70 py-4">No recent failures. All clear ✔</div>
                        @endforelse
                    </div>
                </div>

                <div class="noc-panel-bg border noc-border rounded p-4">
                    <div class="text-[11px] uppercase tracking-wider noc-muted mb-2">SSOT</div>
                    <ul class="text-[10px] noc-muted space-y-1 list-disc list-inside">
                        <li>Retry hanya melalui <span class="text-blue-500">PipelineOrchestrator</span></li>
                        <li>Tidak ada bypass manual step apapun</li>
                        <li>Payload step credentials tidak terekspos</li>
                        <li>Cache NOC TTL 15 detik</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>






