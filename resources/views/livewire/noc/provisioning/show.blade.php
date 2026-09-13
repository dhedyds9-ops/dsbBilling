<div class="min-h-screen noc-bg noc-text noc-mono" wire:poll.15s>
    <div class="max-w-[1400px] mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold noc-text tracking-tight">NOC · Pipeline #{{ $pipeline->id }}</h1>
                <p class="text-xs noc-muted mt-0.5"><span class="inline-block w-2 h-2 rounded-full noc-pulse bg-emerald-500 mr-1.5"></span>LIVE · UUID <span class="font-mono">{{ $pipeline->uuid }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                @if(Route::has('noc.provisioning.index'))
                    <a href="{{ route('noc.provisioning.index') }}" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">← Pipelines</a>
                @endif
                @if(Route::has('noc.overview'))
                    <a href="{{ route('noc.overview') }}" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">Overview</a>
                @endif
            </div>
        </div>

        @php
            $statusClass = match($pipeline->status) {
                'pending'   => 'noc-badge-info',
                'running'   => 'noc-badge-warning',
                'completed' => 'noc-badge-ok',
                'failed'    => 'noc-badge-critical',
                default     => 'bg-gray-700 noc-text',
            };
            $customer = $pipeline->serviceInstance->customerService->customer;
            $onu      = $pipeline->serviceInstance->customerService->onu;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <div class="noc-panel-bg border noc-border rounded p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-block px-2.5 py-1 rounded text-[11px] font-semibold uppercase tracking-wider {{ $statusClass }}">{{ $pipeline->status }}</span>
                            <span class="text-xs noc-muted">Pipeline</span>
                        </div>
                        @if($pipeline->status === 'failed')
                            <button wire:click="retry" onclick="return confirm('Retry failed steps via PipelineOrchestrator ?? ')"
                                    class="px-3 py-1.5 text-xs font-semibold bg-yellow-900 hover:bg-yellow-800 border border-yellow-700 rounded text-yellow-200 transition">↻ Retry Pipeline</button>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs mb-4 pb-4 border-b noc-border">
                        <div>
                            <div class="noc-section-label">Created</div>
                            <div class="noc-text">{{ $pipeline->created_at->format('M d- Y H:i:s') }}</div>
                            <div class="text-[10px] noc-muted mt-0.5">{{ $pipeline->created_at->diffForHumans() }}</div>
                        </div>
                        <div>
                            <div class="noc-section-label">Updated</div>
                            <div class="noc-text">{{ $pipeline->updated_at->format('M d- Y H:i:s') }}</div>
                            <div class="text-[10px] noc-muted mt-0.5">{{ $pipeline->updated_at->diffForHumans() }}</div>
                        </div>
                        <div>
                            <div class="noc-section-label">Triggered By</div>
                            <div class="noc-text">{{ $pipeline->createdBy->name ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="noc-section-label">Error</div>
                            @if($pipeline->error_message)
                                <div class="text-red-500 text-[11px]">{{ $pipeline->error_message }}</div>
                            @else
                                <div class="noc-muted">—</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="noc-section-label mb-3">Step Timeline</div>
                        <div class="space-y-1">
                            @foreach($pipeline->steps->sortBy('order') as $step)
                                @php
                                    $stepStatusClass = match($step->status) {
                                        'pending'   => 'border-gray-600 noc-muted',
                                        'running'   => 'border-yellow-500 text-yellow-500',
                                        'completed' => 'border-emerald-600 text-emerald-500',
                                        'failed'    => 'border-red-600 text-red-500',
                                        default     => 'border-gray-600 noc-muted',
                                    };
                                    $stepBadgeClass = match($step->status) {
                                        'pending'   => 'bg-gray-700 noc-text-secondary',
                                        'running'   => 'noc-badge-warning',
                                        'completed' => 'noc-badge-ok',
                                        'failed'    => 'noc-badge-critical',
                                        default     => 'bg-gray-700 noc-text',
                                    };
                                @endphp
                                <div class="flex items-start gap-3 p-2.5 rounded noc-bg border noc-border">
                                    <div class="flex flex-col items-center">
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold {{ $stepStatusClass }}">
                                            @if($step->status === 'completed') ✓
                                            @elseif($step->status === 'failed') ✗
                                            @elseif($step->status === 'running') <span class="w-2 h-2 noc-pulse rounded-full bg-current"></span>
                                            @else {{ $step->order }}
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-px flex-1 noc-progress my-1 min-h-[20px]"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <div class="font-semibold noc-text text-xs">{{ $step->step_name }}</div>
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $stepBadgeClass }}">{{ $step->status }}</span>
                                        </div>
                                        <div class="text-[10px] noc-muted mb-1">
                                            <span class="uppercase tracking-wider">{{ $step->step_type }}</span>
                                            @if($step->started_at || $step->completed_at || $step->failed_at)
                                                <span class="mx-1.5">·</span>
                                                @if($step->started_at && !$step->completed_at && !$step->failed_at)
                                                    started {{ $step->started_at->diffForHumans() }}
                                                @elseif($step->completed_at)
                                                    {{ $step->completed_at->diffForHumans() }}
                                                @elseif($step->failed_at)
                                                    failed {{ $step->failed_at->diffForHumans() }}
                                                @endif
                                            @endif
                                        </div>
                                        @if($step->error_message)
                                            <div class="text-[11px] text-red-500 bg-red-950/30 border border-red-950 rounded px-2 py-1 mt-1">{{ $step->error_message }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @if($customer)
                    <div class="noc-panel-bg border noc-border rounded p-4">
                        <div class="noc-section-label mb-2">Customer</div>
                        <div class="text-sm noc-text font-medium">{{ $customer->name }}</div>
                        <div class="text-[11px] noc-muted mt-0.5 font-mono">{{ $customer->code }}</div>
                        @if($customerService = $pipeline->serviceInstance->customerService)
                            <div class="mt-3 pt-3 border-t noc-border space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="noc-muted">Service Type</span>
                                    <span class="noc-text-secondary uppercase">{{ $customerService->service_type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="noc-muted">Status</span>
                                    <span class="noc-text-secondary">{{ $customerService->status }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if($onu)
                    <div class="noc-panel-bg border noc-border rounded p-4">
                        <div class="noc-section-label mb-2">ONU Device</div>
                        <div class="text-xs space-y-1.5">
                            <div class="flex justify-between"><span class="noc-muted">SN</span><span class="noc-text-secondary font-mono text-[11px]">{{ $onu->serial_number }}</span></div>
                            @if($onu->name)
                                <div class="flex justify-between"><span class="noc-muted">Name</span><span class="noc-text-secondary">{{ $onu->name }}</span></div>
                            @endif
                            @if($onu->olt->name)
                                <div class="flex justify-between"><span class="noc-muted">OLT</span><span class="noc-text-secondary">{{ $onu->olt->name }}</span></div>
                            @endif
                            <div class="flex justify-between"><span class="noc-muted">Status</span><span class="noc-text-secondary">{{ $onu->status }}</span></div>
                        </div>
                    </div>
                @endif

                @if($pipeline->metadata && !empty($pipeline->metadata))
                    <div class="noc-panel-bg border noc-border rounded p-4">
                        <div class="noc-section-label mb-2">Metadata (safe only)</div>
                        <pre class="text-[11px] noc-muted noc-bg border noc-border rounded px-3 py-2 overflow-x-auto noc-scroll max-h-40">{{ json_encode($pipeline->metadata- JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>






