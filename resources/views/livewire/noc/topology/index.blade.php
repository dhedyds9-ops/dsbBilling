<div class="min-h-screen noc-bg noc-text noc-mono" wire:poll.60s>
    <div class="max-w-[1800px] mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold noc-text tracking-tight">NOC · Topology & Impact Analysis</h1>
                <p class="text-xs noc-muted mt-0.5"><span class="inline-block w-2 h-2 rounded-full noc-pulse bg-green-500 mr-1.5"></span>LIVE · Hitung dampak perangkat terhadap pelanggan</p>
            </div>
            <div class="flex items-center gap-2">
                @if(Route::has('noc.overview'))
                    <a href="{{ route('noc.overview') }}" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">← Overview</a>
                @endif
            </div>
        </div>

        <div class="noc-panel-bg border noc-border rounded mb-4 overflow-hidden">
            <div class="flex border-b noc-border">
                <button wire:click="setTab('olt')" class="px-4 py-2.5 text-xs font-medium border-b-2 transition {{ $activeTab==='olt' ? 'noc-tab-active' : 'noc-tab-inactive' }}">
                    OLT Impact
                </button>
                <button wire:click="setTab('pon')" class="px-4 py-2.5 text-xs font-medium border-b-2 transition {{ $activeTab==='pon' ? 'noc-tab-active' : 'noc-tab-inactive' }}">
                    PON Port Impact
                </button>
                <button wire:click="setTab('router')" class="px-4 py-2.5 text-xs font-medium border-b-2 transition {{ $activeTab==='router' ? 'noc-tab-active' : 'noc-tab-inactive' }}">
                    Router Impact
                </button>
            </div>

            <div class="p-3 grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                @if($activeTab === 'olt')
                    <div class="md:col-span-8">
                        <label class="noc-section-label">Pilih OLT</label>
                        <select wire:model.live="selectedOltId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select OLT —</option>
                            @foreach($olts as $olt)
                                <option value="{{ $olt->id }}">{{ $olt->name }} @if($olt->code)<span class="noc-muted">({{ $olt->code }})</span>@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <div class="noc-section-label">Total OLT</div>
                        <div class="text-sm noc-text-secondary">{{ $olts->count() }} device aktif</div>
                    </div>
                @elseif($activeTab === 'pon')
                    <div class="md:col-span-5">
                        <label class="noc-section-label">Filter OLT (opsional)</label>
                        <select wire:model.live="selectedOltId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">All OLTs</option>
                            @foreach($olts as $olt)
                                <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-7">
                        <label class="noc-section-label">Pilih PON Port</label>
                        <select wire:model.live="selectedPonId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select PON Port —</option>
                            @foreach($ponPorts as $pon)
                                <option value="{{ $pon->id }}">
                                    @if(isset($pon->olt->name)) {{ $pon->olt->name }} — @endif
                                    PON {{ $pon->port_number }} ({{ $pon->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif($activeTab === 'router')
                    <div class="md:col-span-8">
                        <label class="noc-section-label">Pilih Router</label>
                        <select wire:model.live="selectedRouterId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select Router —</option>
                            @foreach($routers as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} @if($r->code)<span class="noc-muted">({{ $r->code }})</span>@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <div class="noc-section-label">Total Router</div>
                        <div class="text-sm noc-text-secondary">{{ $routers->count() }} device aktif</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
            <div class="noc-summary-card">
                <div class="noc-summary-label">{{ $impactResult['device_type'] }}</div>
                <div class="text-sm font-semibold noc-text mt-1">{{ $impactResult['device'] }}</div>
            </div>
            @if(in_array($impactResult['device_type'], ['OLT','PON Port']))
                <div class="noc-summary-card">
                    <div class="noc-summary-label">PON Port Terdampak</div>
                    <div class="noc-summary-value text-blue-500">{{ $impactResult['pon_count'] }}</div>
                </div>
            @endif
            <div class="noc-summary-card">
                <div class="noc-summary-label">ONU Terdampak</div>
                <div class="noc-summary-value text-yellow-500">{{ $impactResult['onu_count'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Pelanggan Terdampak</div>
                <div class="noc-summary-value text-red-500">{{ $impactResult['customer_count'] }}</div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Layanan Aktif</div>
                <div class="noc-summary-value text-yellow-500">{{ $impactResult['service_count'] }}</div>
                <div class="mt-1 flex gap-1.5 text-[10px]">
                    @if($impactResult['breakdown']['pppoe']>0)<span class="noc-badge-info px-1.5 py-0.5 rounded">PPPoE {{ $impactResult['breakdown']['pppoe'] }}</span>@endif
                    @if($impactResult['breakdown']['hotspot']>0)<span class="noc-badge-warning px-1.5 py-0.5 rounded">HS {{ $impactResult['breakdown']['hotspot'] }}</span>@endif
                    @if($impactResult['breakdown']['other']>0)<span class="noc-badge-unknown px-1.5 py-0.5 rounded">Other {{ $impactResult['breakdown']['other'] }}</span>@endif
                </div>
            </div>
        </div>

        <div class="noc-panel-bg border noc-border rounded overflow-hidden">
            <div class="px-3 py-2 border-b noc-border flex items-center justify-between">
                <div class="text-[11px] uppercase tracking-wider noc-muted font-semibold">Affected Customers</div>
                @if($affectedCustomers->total() > 0)
                    <div class="text-[11px] noc-muted">{{ $affectedCustomers->total() }} record · Page {{ $affectedCustomers->currentPage() }}/{{ $affectedCustomers->lastPage() }}</div>
                @endif
            </div>
            <div class="overflow-x-auto noc-scroll">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                        <tr class="text-slate-500 dark:text-slate-400">
                            <th class="p-3 font-semibold w-16">#</th>
                            <th class="p-3 font-semibold">Customer</th>
                            <th class="p-3 font-semibold">Code</th>
                            <th class="p-3 font-semibold">Service</th>
                            <th class="p-3 font-semibold">ONU SN</th>
                            <th class="p-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($affectedCustomers as $idx => $cs)
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors border-b noc-divider noc-row-hover transition">
                                <td class="p-3  noc-muted">{{ $affectedCustomers->firstItem() + $idx }}</td>
                                <td class="p-3">
                                    @isset($cs->customer->name)
                                        <div class="noc-text">{{ $cs->customer->name }}</div>
                                    @endisset
                                </td>
                                <td class="p-3  noc-muted font-mono">{{ $cs->customer->code }}</td>
                                <td class="p-3">
                                    <span class="uppercase {{ $cs->service_type==='pppoe'?'noc-badge-info':'noc-badge-warning' }} px-1.5 py-0.5 rounded text-[10px]">{{ $cs->service_type }}</span>
                                </td>
                                <td class="p-3">
                                    @if($cs->onu)
                                        <div class="font-mono text-[11px] text-blue-500">{{ $cs->onu->serial_number }}</div>
                                        <div class="text-[10px] mt-0.5 {{ $cs->onu->status==='online'?'text-emerald-500':'text-red-500' }}">{{ strtoupper($cs->onu->status) }}</div>
                                    @else
                                        <span class="noc-muted opacity-70">—</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $cs->status==='active'?'noc-badge-ok':'noc-badge-unknown' }}">{{ $cs->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td colspan="6" class="p-3  text-center noc-muted text-xs">
                                    {{ ($activeTab==='olt' && !$selectedOltId) || ($activeTab==='pon' && !$selectedPonId) || ($activeTab==='router' && !$selectedRouterId)
                                        ? 'Pilih perangkat di atas untuk melihat daftar pelanggan terdampak.'
                                        : 'Tidak ada pelanggan aktif yang terdampak pada perangkat ini.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($affectedCustomers->hasPages())
                <div class="px-3 py-2 border-t noc-border text-xs noc-muted">
                    {{ $affectedCustomers->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</div>






