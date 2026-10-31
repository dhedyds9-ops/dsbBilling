<div class="min-h-screen noc-bg noc-text noc-mono pb-12" wire:poll.60s x-data="topologyManager()">
    <div class="max-w-[1850px] mx-auto px-4 py-4 space-y-4">

        {{-- ============================================================
             1. HEADER & GLOBAL ACTIONS
             ============================================================ --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 noc-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    <h1 class="text-xl font-bold tracking-wider text-slate-100 uppercase">NOC · Topology & Impact Analysis</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-950 text-cyan-400 border border-cyan-800 tracking-wider">ENTERPRISE</span>
                </div>
                <p class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                    <span>Pemetaan relasi perangkat, kalkulasi dampak pelanggan, dan Root Cause Analysis real-time.</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-500 font-mono">Last refresh: {{ now()->format('H:i:s') }} WIB</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(Route::has('noc.overview'))
                    <a href="{{ route('noc.overview') }}" class="px-3 py-1.5 text-xs font-medium border border-slate-700 bg-slate-900/80 hover:bg-slate-800 text-slate-300 rounded transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        NOC Overview
                    </a>
                @endif
                <button wire:click="$refresh" class="px-3 py-1.5 text-xs font-medium border border-cyan-500/50 bg-cyan-950/40 hover:bg-cyan-900/60 text-cyan-400 rounded transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh Data
                </button>
            </div>
        </div>

        {{-- ============================================================
             2. SECTION A: NETWORK SUMMARY (4 CARDS)
             ============================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            {{-- Card 1: Total Devices --}}
            <div class="bg-[#111827] border border-slate-800 hover:border-cyan-500/50 rounded-lg p-3.5 relative overflow-hidden transition-all shadow-md group">
                <div class="flex items-center justify-between">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Total Network Devices</div>
                    <span class="p-1.5 rounded bg-slate-800/80 text-cyan-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-slate-100 mt-2 font-mono">{{ number_format($networkSummary['total_devices']) }}</div>
                <div class="mt-1 flex items-center text-[11px] text-slate-400 gap-1.5">
                    <span class="text-cyan-400 font-medium">Core, OLT & ONUs</span>
                    <span class="text-slate-600">·</span>
                    <span>Terdaftar di sistem</span>
                </div>
                <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-cyan-500/5 rounded-full blur-xl group-hover:bg-cyan-500/10 transition"></div>
            </div>

            {{-- Card 2: Online Devices --}}
            <div class="bg-[#111827] border border-slate-800 hover:border-emerald-500/50 rounded-lg p-3.5 relative overflow-hidden transition-all shadow-md group">
                <div class="flex items-center justify-between">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Online Devices</div>
                    <span class="p-1.5 rounded bg-slate-800/80 text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="flex items-baseline gap-2 mt-2">
                    <div class="text-2xl font-bold text-emerald-400 font-mono">{{ number_format($networkSummary['online_devices']) }}</div>
                    <span class="text-xs font-semibold px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-300 border border-emerald-800">
                        {{ $networkSummary['online_percentage'] }}%
                    </span>
                </div>
                <div class="mt-1 flex items-center text-[11px] text-slate-400 gap-1.5">
                    <span class="text-emerald-400">● Live heartbeat</span>
                    <span class="text-slate-600">·</span>
                    <span>Polled &lt; 15 mins</span>
                </div>
                <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition"></div>
            </div>

            {{-- Card 3: Active Alarms --}}
            <div class="bg-[#111827] border border-slate-800 hover:border-amber-500/50 rounded-lg p-3.5 relative overflow-hidden transition-all shadow-md group">
                <div class="flex items-center justify-between">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Active Alarms</div>
                    <span class="p-1.5 rounded bg-slate-800/80 text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <div class="flex items-baseline gap-2 mt-2">
                    <div class="text-2xl font-bold {{ $networkSummary['active_alarms'] > 0 ? 'text-amber-400' : 'text-slate-100' }} font-mono">
                        {{ number_format($networkSummary['active_alarms']) }}
                    </div>
                    @if($networkSummary['critical_alarms'] > 0)
                        <span class="text-xs font-semibold px-1.5 py-0.5 rounded bg-rose-950 text-rose-400 border border-rose-800 animate-pulse">
                            {{ $networkSummary['critical_alarms'] }} Critical
                        </span>
                    @endif
                </div>
                <div class="mt-1 flex items-center text-[11px] text-slate-400 gap-1.5">
                    <span class="text-amber-400">Unresolved events</span>
                    <span class="text-slate-600">·</span>
                    <span>Open status</span>
                </div>
                <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-amber-500/5 rounded-full blur-xl group-hover:bg-amber-500/10 transition"></div>
            </div>

            {{-- Card 4: Affected Customers --}}
            <div class="bg-[#111827] border border-slate-800 hover:border-rose-500/50 rounded-lg p-3.5 relative overflow-hidden transition-all shadow-md group">
                <div class="flex items-center justify-between">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Affected Customers</div>
                    <span class="p-1.5 rounded bg-slate-800/80 text-rose-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold {{ $networkSummary['affected_customers'] > 0 ? 'text-rose-400' : 'text-slate-100' }} mt-2 font-mono">
                    {{ number_format($networkSummary['affected_customers']) }}
                </div>
                <div class="mt-1 flex items-center text-[11px] text-slate-400 gap-1.5">
                    <span class="text-rose-400">Potensi / Terdampak</span>
                    <span class="text-slate-600">·</span>
                    <span>PPPoE & Hotspot</span>
                </div>
                <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-rose-500/5 rounded-full blur-xl group-hover:bg-rose-500/10 transition"></div>
            </div>
        </div>

        {{-- ============================================================
             3. SECTION B & C: IMPACT CATEGORY TABS & DEVICE SELECTION
             ============================================================ --}}
        <div class="bg-[#111827] border border-slate-800 rounded-lg shadow-md overflow-hidden">
            {{-- Tabs --}}
            <div class="flex items-center border-b border-slate-800 bg-slate-900/60 px-2 overflow-x-auto">
                <button wire:click="setTab('olt')"
                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'olt' ? 'border-cyan-400 text-cyan-400 bg-slate-800/40 shadow-[inset_0_-2px_0_rgba(0,229,255,1)]' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    <span>OLT Impact</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 border border-slate-700 text-slate-300">{{ $olts->count() }}</span>
                </button>
                <button wire:click="setTab('pon')"
                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'pon' ? 'border-cyan-400 text-cyan-400 bg-slate-800/40 shadow-[inset_0_-2px_0_rgba(0,229,255,1)]' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>PON Port Impact</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 border border-slate-700 text-slate-300">{{ $ponPorts->count() }}</span>
                </button>
                <button wire:click="setTab('router')"
                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wider border-b-2 transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'router' ? 'border-cyan-400 text-cyan-400 bg-slate-800/40 shadow-[inset_0_-2px_0_rgba(0,229,255,1)]' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span>Router Impact</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 border border-slate-700 text-slate-300">{{ $routers->count() }}</span>
                </button>
            </div>

            {{-- Device Select Controls --}}
            <div class="p-4 bg-slate-900/40">
                @if($activeTab === 'olt')
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-8">
                            <label class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1.5">
                                Pilih Optical Line Terminal (OLT)
                            </label>
                            <div class="relative">
                                <select wire:model.live="selectedOltId"
                                        class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded px-3 py-2 text-xs font-mono text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-400 transition shadow-inner">
                                    <option value="">— Pilih OLT Terdaftar —</option>
                                    @foreach($olts as $olt)
                                        <option value="{{ $olt->id }}">
                                            {{ $olt->name }} ({{ $olt->ip_address ?: 'No IP' }}) — {{ $olt->pon_ports_count }} PON, {{ $olt->onus_count }} ONUs [{{ strtoupper($olt->status) }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="md:col-span-4 flex items-center justify-between p-2.5 bg-slate-900 border border-slate-800 rounded">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Total OLT Aktif</span>
                                <span class="text-sm font-bold text-cyan-400 font-mono">{{ $olts->count() }} unit</span>
                            </div>
                            @if($selectedOltId)
                                @php $curOlt = $olts->firstWhere('id', $selectedOltId); @endphp
                                @if($curOlt)
                                    <div class="text-right">
                                        <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded font-semibold {{ $curOlt->status === 'active' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-rose-950 text-rose-400 border border-rose-800' }}">
                                            ● {{ strtoupper($curOlt->status) }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @elseif($activeTab === 'pon')
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-4">
                            <label class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1.5">
                                Filter Berdasarkan OLT
                            </label>
                            <select wire:model.live="selectedOltId"
                                    class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded px-3 py-2 text-xs font-mono text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-400 transition">
                                <option value="">Semua OLT</option>
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-8">
                            <label class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1.5">
                                Pilih PON Port
                            </label>
                            <select wire:model.live="selectedPonId"
                                    class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded px-3 py-2 text-xs font-mono text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-400 transition">
                                <option value="">— Pilih PON Port —</option>
                                @foreach($ponPorts as $pon)
                                    <option value="{{ $pon->id }}">
                                        {{ $pon->olt->name ?? 'OLT' }} → PON {{ $pon->port_number }} ({{ $pon->name }}) — {{ $pon->onus_count }} ONU terhubung
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @elseif($activeTab === 'router')
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-8">
                            <label class="block text-xs uppercase tracking-wider text-slate-400 font-semibold mb-1.5">
                                Pilih Core / Distribution Router
                            </label>
                            <select wire:model.live="selectedRouterId"
                                    class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded px-3 py-2 text-xs font-mono text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-400 transition">
                                <option value="">— Pilih Router —</option>
                                @foreach($routers as $r)
                                    <option value="{{ $r->id }}">
                                        {{ $r->name }} ({{ $r->ip_address ?: 'No IP' }}) — Model: {{ $r->model ?: 'MikroTik' }} [{{ strtoupper($r->status) }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-4 flex items-center justify-between p-2.5 bg-slate-900 border border-slate-800 rounded">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Total Router Terdaftar</span>
                                <span class="text-sm font-bold text-cyan-400 font-mono">{{ $routers->count() }} unit</span>
                            </div>
                            @if($selectedRouterId)
                                @php $curR = $routers->firstWhere('id', $selectedRouterId); @endphp
                                @if($curR)
                                    <div class="text-right">
                                        <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded font-semibold {{ in_array($curR->status, ['active','online']) ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-rose-950 text-rose-400 border border-rose-800' }}">
                                            ● {{ strtoupper($curR->status) }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             4. SECTION D: IMPACT SUMMARY CARDS
             ============================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Selected Device Card --}}
            <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold flex items-center justify-between">
                    <span>Selected {{ $impactResult['device_type'] }}</span>
                    <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-800 text-slate-300 font-mono">{{ $impactResult['model'] ?? 'Device' }}</span>
                </div>
                <div class="text-base font-bold text-cyan-300 mt-1.5 truncate font-mono" title="{{ $impactResult['device'] }}">
                    {{ $impactResult['device'] ?: 'No device selected' }}
                </div>
                <div class="text-xs text-slate-400 mt-1 font-mono flex items-center gap-2">
                    <span>IP: {{ $impactResult['ip_address'] ?? '—' }}</span>
                    <span class="text-slate-600">·</span>
                    <span class="uppercase {{ ($impactResult['status'] ?? '') === 'active' || ($impactResult['status'] ?? '') === 'online' ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $impactResult['status'] ?? 'Standby' }}
                    </span>
                </div>
            </div>

            @if($activeTab === 'olt')
                {{-- OLT: Affected PON Ports --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Affected PON Ports</div>
                    <div class="text-2xl font-bold text-blue-400 mt-1.5 font-mono">{{ $impactResult['pon_count'] }}</div>
                    <div class="text-xs text-slate-400 mt-1">Port GPON pada sasis ini</div>
                </div>
                {{-- OLT: Affected ONUs --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Affected ONUs</div>
                    <div class="text-2xl font-bold text-amber-400 mt-1.5 font-mono">{{ $impactResult['onu_count'] }}</div>
                    <div class="text-xs text-slate-400 mt-1">Modem pelanggan terhubung</div>
                </div>
                {{-- OLT: Affected Customers & Breakdown --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold flex items-center justify-between">
                        <span>Affected Customers</span>
                        <span class="text-xs font-bold text-rose-400 font-mono">{{ $impactResult['customer_count'] }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5 text-[10px]">
                        <span class="px-2 py-0.5 rounded bg-blue-950 text-blue-300 border border-blue-800 font-mono">
                            PPPoE: {{ $impactResult['breakdown']['pppoe'] }}
                        </span>
                        <span class="px-2 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-800 font-mono">
                            Hotspot: {{ $impactResult['breakdown']['hotspot'] }}
                        </span>
                        @if(($impactResult['breakdown']['other'] ?? 0) > 0)
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700 font-mono">
                                Other: {{ $impactResult['breakdown']['other'] }}
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1.5 font-mono">Total {{ $impactResult['service_count'] }} layanan aktif</div>
                </div>
            @elseif($activeTab === 'pon')
                {{-- PON: Affected ONUs --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Affected ONUs</div>
                    <div class="text-2xl font-bold text-amber-400 mt-1.5 font-mono">{{ $impactResult['onu_count'] }}</div>
                    <div class="text-xs text-slate-400 mt-1">Total ONU pada port ini</div>
                </div>
                {{-- PON: Affected Customers --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Affected Customers</div>
                    <div class="text-2xl font-bold text-rose-400 mt-1.5 font-mono">{{ $impactResult['customer_count'] }}</div>
                    <div class="text-xs text-slate-400 mt-1">Pelanggan mengalami dampak</div>
                </div>
                {{-- PON: Affected Services --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Affected Services</div>
                    <div class="text-2xl font-bold text-cyan-400 mt-1.5 font-mono">{{ $impactResult['service_count'] }}</div>
                    <div class="mt-1 flex gap-1.5 text-[10px]">
                        <span class="px-1.5 py-0.5 rounded bg-blue-950 text-blue-300 border border-blue-800">PPPoE {{ $impactResult['breakdown']['pppoe'] }}</span>
                        <span class="px-1.5 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-800">HS {{ $impactResult['breakdown']['hotspot'] }}</span>
                    </div>
                </div>
            @elseif($activeTab === 'router')
                {{-- Router: Affected Downstream Devices --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Downstream Devices</div>
                    <div class="text-2xl font-bold text-blue-400 mt-1.5 font-mono">{{ $impactResult['downstream_devices_count'] ?? 0 }}</div>
                    <div class="text-xs text-slate-400 mt-1">OLT & Switch terhubung</div>
                </div>
                {{-- Router: Affected Network Links --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Network Links</div>
                    <div class="text-2xl font-bold text-amber-400 mt-1.5 font-mono">{{ $impactResult['network_links_count'] ?? 1 }}</div>
                    <div class="text-xs text-slate-400 mt-1">Uplink & Downlink paths</div>
                </div>
                {{-- Router: Affected Customers & Breakdown --}}
                <div class="bg-[#111827] border border-slate-800 rounded-lg p-3.5">
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold flex items-center justify-between">
                        <span>Affected Customers</span>
                        <span class="text-xs font-bold text-rose-400 font-mono">{{ $impactResult['customer_count'] }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5 text-[10px]">
                        <span class="px-2 py-0.5 rounded bg-blue-950 text-blue-300 border border-blue-800 font-mono">
                            PPPoE: {{ $impactResult['breakdown']['pppoe'] }}
                        </span>
                        <span class="px-2 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-800 font-mono">
                            Hotspot: {{ $impactResult['breakdown']['hotspot'] }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1.5 font-mono">Total {{ $impactResult['service_count'] }} sessions</div>
                </div>
            @endif
        </div>

        {{-- ============================================================
             5. SECTION E: INTERACTIVE NETWORK TOPOLOGY CANVAS & RCA
             ============================================================ --}}
        <div id="topology-card-wrapper" class="bg-[#111827] border border-slate-800 rounded-lg overflow-hidden shadow-xl flex flex-col relative"
             x-data="{
                 canvasHeight: 'lg',
                 setHeight(val) {
                     this.canvasHeight = val;
                     this.$nextTick(() => {
                         setTimeout(() => {
                             if (window.topologyManagerInstance) {
                                 window.topologyManagerInstance.fitViewSmart();
                             }
                         }, 250);
                     });
                 }
             }">
            {{-- Topology Canvas Toolbar --}}
            <div class="px-4 py-2.5 bg-slate-900 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3 z-10">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold tracking-wider text-cyan-400 uppercase">Interactive Network Topology</span>
                    <span class="text-xs text-slate-600 font-mono">|</span>
                    <span class="text-xs text-slate-400 hidden sm:inline">Hierarchical DAG Flow (Core → Distribution → Access → Endpoint)</span>
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Height Presets --}}
                    <div class="flex items-center bg-slate-950/80 p-0.5 rounded border border-slate-800 text-[11px] font-mono">
                        <span class="text-slate-500 px-1.5 text-[10px] hidden md:inline">Height:</span>
                        <button type="button"
                                @click="setHeight('md')"
                                :class="canvasHeight === 'md' ? 'bg-cyan-950 text-cyan-400 border border-cyan-700 font-bold' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2 py-0.5 rounded transition">
                            580px
                        </button>
                        <button type="button"
                                @click="setHeight('lg')"
                                :class="canvasHeight === 'lg' ? 'bg-cyan-950 text-cyan-400 border border-cyan-700 font-bold' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2 py-0.5 rounded transition">
                            720px
                        </button>
                        <button type="button"
                                @click="setHeight('xl')"
                                :class="canvasHeight === 'xl' ? 'bg-cyan-950 text-cyan-400 border border-cyan-700 font-bold' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2 py-0.5 rounded transition">
                            900px
                        </button>
                    </div>

                    {{-- Zoom Controls --}}
                    <div class="flex items-center bg-slate-950/80 p-0.5 rounded border border-slate-800 gap-0.5">
                        <button type="button" @click="zoomOut()" class="p-1 rounded hover:bg-slate-800 text-slate-300 transition" title="Zoom Out (-)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/></svg>
                        </button>
                        <button type="button" @click="resetScale()" class="px-2 py-0.5 text-[10px] font-mono text-slate-300 hover:bg-slate-800 rounded transition" title="Reset Scale to 100%">
                            100%
                        </button>
                        <button type="button" @click="zoomIn()" class="p-1 rounded hover:bg-slate-800 text-slate-300 transition" title="Zoom In (+)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </button>
                    </div>

                    {{-- Fit View --}}
                    <button type="button" @click="fitViewSmart()" class="px-2.5 py-1 text-xs rounded bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center gap-1 shadow-sm" title="Fit & Center View">
                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        <span class="font-mono">Fit View</span>
                    </button>

                    {{-- Fullscreen --}}
                    <button type="button" @click="toggleFullscreen()" class="p-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition" title="Toggle Fullscreen">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8V5a2 2 0 012-2h3m10 0h3a2 2 0 012 2v3m0 10v3a2 2 0 01-2 2h-3M7 21H4a2 2 0 01-2-2v-3"/></svg>
                    </button>
                </div>
            </div>

            {{-- Legend bar --}}
            <div class="px-4 py-2 bg-slate-950/90 border-b border-slate-800/80 flex flex-wrap items-center justify-between text-xs gap-2">
                <div class="flex items-center gap-4 text-slate-300 font-mono text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-[#10b981] border border-emerald-400 shadow-[0_0_6px_rgba(16,185,129,0.5)]"></span>
                        <span>Online / Normal</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-[#f59e0b] border border-amber-400 shadow-[0_0_6px_rgba(245,158,11,0.5)]"></span>
                        <span>Warning / Degraded</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-[#ef4444] border border-rose-400 shadow-[0_0_6px_rgba(239,68,68,0.5)]"></span>
                        <span>Critical / Direct Fault / Impacted</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-[#64748b] border border-slate-500"></span>
                        <span>Unknown / Offline</span>
                    </div>
                </div>
                <div class="text-slate-400 text-[11px] font-mono flex items-center gap-2">
                    <span class="text-cyan-400">💡 Scroll mouse / drag untuk zoom &amp; geser</span>
                    <span class="text-slate-600">·</span>
                    <span>Klik node untuk side panel</span>
                </div>
            </div>

            {{-- Canvas Container with vis-network (Dynamic height: 580px / 720px / 900px) --}}
            <div class="relative w-full bg-[#070b14] overflow-hidden transition-all duration-300"
                 :class="{
                     'h-[580px]': canvasHeight === 'md',
                     'h-[720px]': canvasHeight === 'lg',
                     'h-[900px]': canvasHeight === 'xl'
                 }">
                <div id="topology-network-container"
                     wire:key="topo-canvas-{{ $activeTab }}-{{ $selectedOltId }}-{{ $selectedPonId }}-{{ $selectedRouterId }}"
                     x-init="loadTopology(@js($topologyData))"
                     class="w-full h-full cursor-grab active:cursor-grabbing"></div>

                {{-- Empty state overlay if no data --}}
                @if(empty($topologyData['nodes']))
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-[#070b14]/90 text-slate-400 pointer-events-none">
                        <svg class="w-14 h-14 text-slate-600 mb-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <p class="text-base font-semibold text-slate-200">Pilih perangkat di atas untuk memuat visualisasi topologi.</p>
                        <p class="text-xs text-slate-500 mt-1">Sistem akan merender jalur hirarkis secara otomatis.</p>
                    </div>
                @endif
            </div>

            {{-- Root Cause Analysis (RCA) Box --}}
            @if(!empty($topologyData['rca']))
                @php $rca = $topologyData['rca']; @endphp
                <div class="p-3.5 bg-slate-900 border-t border-slate-800 text-xs">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-950 text-rose-300 border border-rose-800 uppercase tracking-wider">
                                Root Cause Analysis
                            </span>
                            <span class="font-semibold text-slate-200">Diagnosa Otomatis Gangguan</span>
                        </div>
                        <div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ str_contains($rca['severity'], 'CRITICAL') ? 'bg-red-950 text-red-300 border border-red-700' : (str_contains($rca['severity'], 'HIGH') ? 'bg-amber-950 text-amber-300 border border-amber-700' : 'bg-blue-950 text-blue-300 border border-blue-700') }}">
                                SEVERITY: {{ $rca['severity'] }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-slate-950/70 p-3 rounded border border-slate-800/80 font-mono">
                        <div class="md:col-span-4">
                            <span class="text-[10px] uppercase text-slate-500 block">Titik Gangguan / Failed Device</span>
                            <span class="text-cyan-300 font-semibold block mt-0.5">{{ $rca['failed_device'] }}</span>
                            <span class="text-[10px] text-slate-400">Tipe: {{ $rca['device_type'] }}</span>
                        </div>
                        <div class="md:col-span-5">
                            <span class="text-[10px] uppercase text-slate-500 block">Dugaan Penyebab (Suspected Cause)</span>
                            <span class="text-amber-300 font-medium block mt-0.5">{{ $rca['suspected_cause'] }}</span>
                            <span class="text-[10px] text-slate-400 truncate block mt-0.5" title="{{ $rca['affected_path'] }}">
                                Jalur: {{ $rca['affected_path'] }}
                            </span>
                        </div>
                        <div class="md:col-span-3 text-right">
                            <span class="text-[10px] uppercase text-slate-500 block">Waktu Deteksi &amp; Update</span>
                            <span class="text-slate-300 text-[11px] block mt-0.5">{{ $rca['detection_time'] }}</span>
                            <span class="text-[10px] text-slate-500 block">Update: {{ $rca['last_status_update'] }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============================================================
             6. SECTION F: DEVICE DETAIL SIDE PANEL (SLIDE-OVER)
             ============================================================ --}}
        @if($showSidePanel && $selectedNodeDetail)
            <div class="fixed inset-y-0 right-0 w-full max-w-md bg-[#0f172a] border-l border-cyan-500/50 shadow-2xl z-50 flex flex-col transform transition-all ease-in-out duration-300 noc-mono"
                 x-transition:enter="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                {{-- Panel Header --}}
                <div class="p-4 bg-slate-900 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full {{ $selectedNodeDetail['status'] === 'online' ? 'bg-emerald-500' : ($selectedNodeDetail['status'] === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }} noc-pulse"></span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wider">Device Detail</h3>
                            <p class="text-[10px] text-slate-400">{{ $selectedNodeDetail['type'] }}</p>
                        </div>
                    </div>
                    <button wire:click="closeSidePanel" class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Panel Body --}}
                <div class="p-4 overflow-y-auto flex-1 space-y-3.5 text-xs">
                    {{-- Status Banner --}}
                    <div class="p-3 rounded border {{ $selectedNodeDetail['status'] === 'online' ? 'bg-emerald-950/40 border-emerald-800 text-emerald-300' : ($selectedNodeDetail['status'] === 'warning' ? 'bg-amber-950/40 border-amber-800 text-amber-300' : 'bg-rose-950/40 border-rose-800 text-rose-300') }} flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase block font-semibold">Status Operasional</span>
                            <span class="text-sm font-bold uppercase">{{ $selectedNodeDetail['status_label'] ?? $selectedNodeDetail['status'] }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $selectedNodeDetail['is_direct_fault'] ? 'bg-rose-900 text-rose-200' : 'bg-slate-800 text-slate-300' }}">
                            {{ $selectedNodeDetail['is_direct_fault'] ? 'Target Utama' : 'Downstream Cascade' }}
                        </span>
                    </div>

                    {{-- Technical Specifications --}}
                    <div class="bg-slate-900/80 border border-slate-800 rounded p-3 space-y-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-800 pb-1">
                            Identitas Perangkat
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/60">
                            <span class="text-slate-400">Nama Perangkat:</span>
                            <span class="font-semibold text-slate-200 text-right">{{ $selectedNodeDetail['name'] }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/60">
                            <span class="text-slate-400">Kode / SN:</span>
                            <span class="font-mono text-cyan-300 text-right">{{ $selectedNodeDetail['code'] }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/60">
                            <span class="text-slate-400">IP Address:</span>
                            <span class="font-mono text-slate-200 text-right">{{ $selectedNodeDetail['ip'] }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/60">
                            <span class="text-slate-400">Vendor / Manufaktur:</span>
                            <span class="text-slate-200 text-right">{{ $selectedNodeDetail['vendor'] }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400">Model / Hardware:</span>
                            <span class="text-slate-200 text-right">{{ $selectedNodeDetail['model'] }}</span>
                        </div>
                    </div>

                    {{-- Hierarchy & Topology Context --}}
                    <div class="bg-slate-900/80 border border-slate-800 rounded p-3 space-y-2">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 border-b border-slate-800 pb-1">
                            Konteks Hirarki Jaringan
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/60">
                            <span class="text-slate-400">Parent Device:</span>
                            <span class="text-slate-200 text-right">{{ $selectedNodeDetail['parent'] ?? 'Core' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/60">
                            <span class="text-slate-400">Child Devices Count:</span>
                            <span class="font-mono text-cyan-300 text-right">{{ $selectedNodeDetail['children_count'] ?? 0 }} node</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/60">
                            <span class="text-slate-400">Active Alarms:</span>
                            <span class="font-mono font-bold {{ ($selectedNodeDetail['alarms_count'] ?? 0) > 0 ? 'text-rose-400' : 'text-emerald-400' }} text-right">
                                {{ $selectedNodeDetail['alarms_count'] ?? 0 }} alarms
                            </span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400">Pelanggan Terdampak:</span>
                            <span class="font-mono font-bold text-rose-400 text-right">
                                {{ $selectedNodeDetail['affected_customers'] ?? 0 }} customer
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Panel Footer --}}
                <div class="p-3 bg-slate-900 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-[10px] text-slate-500">dsBilling NOC Inspector</span>
                    <button wire:click="closeSidePanel" class="px-3 py-1.5 rounded text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                        Tutup Panel
                    </button>
                </div>
            </div>
        @endif

        {{-- ============================================================
             7. SECTION G: AFFECTED CUSTOMERS TABLE WITH SEARCH & FILTER
             ============================================================ --}}
        <div class="bg-[#111827] border border-slate-800 rounded-lg overflow-hidden shadow-lg">
            {{-- Table Control Bar --}}
            <div class="px-4 py-3 border-b border-slate-800 bg-slate-900/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">Daftar Pelanggan Terdampak (Affected Customers)</h3>
                    @if($affectedCustomers->total() > 0)
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-rose-950 text-rose-300 border border-rose-800">
                            {{ number_format($affectedCustomers->total()) }} record
                        </span>
                    @endif
                </div>

                {{-- Search & Service Filter --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search Input --}}
                    <div class="relative min-w-[240px]">
                        <input type="text"
                               wire:model.live.debounce.300ms="customerSearch"
                               placeholder="Cari nama, kode, SN..."
                               class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded px-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none transition">
                        @if($customerSearch)
                            <button wire:click="$set('customerSearch', '')" class="absolute right-2 top-2 text-slate-500 hover:text-slate-300 text-xs">✕</button>
                        @endif
                    </div>

                    {{-- Service Type Filter --}}
                    <select wire:model.live="customerServiceFilter"
                            class="bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none transition">
                        <option value="all">Semua Layanan</option>
                        <option value="pppoe">Hanya PPPoE</option>
                        <option value="hotspot">Hanya Hotspot</option>
                    </select>
                </div>
            </div>

            {{-- Table Body --}}
            <div class="overflow-x-auto noc-scroll">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-900/90 border-b border-slate-800 uppercase tracking-wider text-slate-400 text-[11px]">
                        <tr>
                            <th class="p-3 w-12 text-center">#</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Customer Code</th>
                            <th class="p-3">Service Type</th>
                            <th class="p-3">ONU Serial Number</th>
                            <th class="p-3">Related Device</th>
                            <th class="p-3">Connection Status</th>
                            <th class="p-3 text-center">Impact Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/70">
                        @forelse($affectedCustomers as $idx => $cs)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3 text-center text-slate-500">
                                    {{ $affectedCustomers->firstItem() + $idx }}
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-100">{{ $cs->customer->name ?? '—' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $cs->username ?? '' }}</div>
                                </td>
                                <td class="p-3 font-mono text-cyan-400">
                                    {{ $cs->customer->code ?? '—' }}
                                </td>
                                <td class="p-3">
                                    @php
                                        $sType = $cs->serviceProfile->service_type ?? ($cs->service_type ?? 'service');
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-semibold {{ $sType === 'pppoe' ? 'bg-blue-950 text-blue-300 border border-blue-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                        {{ $sType }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if($cs->onu)
                                        <div class="font-mono text-slate-200">{{ $cs->onu->serial_number }}</div>
                                        <div class="text-[10px] flex items-center gap-1 mt-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full {{ in_array($cs->onu->status, ['online', 'active']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            <span class="uppercase {{ in_array($cs->onu->status, ['online', 'active']) ? 'text-emerald-400' : 'text-rose-400' }}">
                                                {{ $cs->onu->status }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($cs->onu && $cs->onu->olt)
                                        <div class="text-slate-300">{{ $cs->onu->olt->name }}</div>
                                        @if($cs->onu->ponPort)
                                            <div class="text-[10px] text-slate-500">PON {{ $cs->onu->ponPort->port_number }}</div>
                                        @endif
                                    @elseif($cs->networkProfile && $cs->networkProfile->router)
                                        <div class="text-slate-300">{{ $cs->networkProfile->router->name }}</div>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $cs->status === 'active' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                        {{ $cs->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-rose-950 text-rose-300 border border-rose-800 animate-pulse">
                                        TERDAMPAK
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-500">
                                    <div class="max-w-md mx-auto flex flex-col items-center">
                                        <svg class="w-10 h-10 text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <p class="text-sm font-semibold text-slate-300">
                                            {{ ($activeTab==='olt' && !$selectedOltId) || ($activeTab==='pon' && !$selectedPonId) || ($activeTab==='router' && !$selectedRouterId)
                                                ? 'Pilih perangkat di atas untuk menganalisis pelanggan terdampak.'
                                                : 'Tidak ada pelanggan aktif yang terdampak pada kriteria yang dipilih.' }}
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">Gunakan search atau ganti filter jika data tidak muncul.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Pagination --}}
            @if($affectedCustomers->hasPages())
                <div class="p-3 border-t border-slate-800 bg-slate-900/60">
                    {{ $affectedCustomers->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- ============================================================
     8. JAVASCRIPT: VIS-NETWORK INITIALIZATION & ALPINE LOGIC
     ============================================================ --}}
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
function topologyManager() {
    return {
        network: null,
        isFullscreen: false,
        currentData: null,
        init() {
            window.topologyManagerInstance = this;
        },
        loadTopology(topology) {
            this.currentData = topology;
            const container = document.getElementById('topology-network-container');
            if (!container || typeof vis === 'undefined') return;

            if (!topology || !topology.nodes || topology.nodes.length === 0) {
                if (this.network) {
                    this.network.destroy();
                    this.network = null;
                }
                return;
            }

            const nodes = new vis.DataSet(topology.nodes);
            const edges = new vis.DataSet(topology.edges);

            const options = {
                layout: {
                    hierarchical: {
                        enabled: true,
                        direction: 'LR', // Left to Right: Core -> OLT -> PON -> ONU
                        sortMethod: 'directed',
                        levelSeparation: 360,  // horizontal width spacing
                        nodeSpacing: 75,       // vertical height spacing so all 8-16 ports fit
                        treeSpacing: 100,
                        blockShifting: true,
                        edgeMinimization: true,
                        parentCentralization: true
                    }
                },
                physics: false,
                nodes: {
                    borderWidth: 2,
                    borderWidthSelected: 4,
                    shadow: {
                        enabled: true,
                        color: 'rgba(0,0,0,0.6)',
                        size: 6,
                        x: 2,
                        y: 2
                    },
                    margin: { top: 10, right: 16, bottom: 10, left: 16 },
                    font: {
                        size: 14,
                        face: 'JetBrains Mono, monospace',
                        color: '#ffffff'
                    },
                    shapeProperties: {
                        borderRadius: 8
                    }
                },
                edges: {
                    width: 2.5,
                    selectionWidth: 4,
                    hoverWidth: 3.5,
                    smooth: {
                        type: 'cubicBezier',
                        forceDirection: 'horizontal',
                        roundness: 0.35
                    },
                    color: {
                        color: '#00e5ff',
                        highlight: '#38bdf8',
                        hover: '#00e5ff'
                    },
                    arrows: {
                        to: {
                            enabled: true,
                            scaleFactor: 0.8
                        }
                    }
                },
                interaction: {
                    hover: true,
                    tooltipDelay: 150,
                    zoomView: true,
                    dragView: true,
                    selectable: true,
                    multiselect: false
                }
            };

            if (this.network) {
                this.network.destroy();
            }

            this.network = new vis.Network(container, { nodes, edges }, options);

            // Click node event -> open side panel
            this.network.on('click', (params) => {
                if (params.nodes && params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    if (window.Livewire) {
                        const lwEl = container.closest('[wire\\:id]');
                        if (lwEl && window.Livewire.find(lwEl.getAttribute('wire:id'))) {
                            window.Livewire.find(lwEl.getAttribute('wire:id')).selectNode(nodeId);
                        }
                    }
                }
            });

            // Initial fit with padding so every node is 100% visible
            setTimeout(() => {
                this.fitViewSmart();
            }, 80);
        },
        zoomIn() {
            if (!this.network) return;
            const currentScale = this.network.getScale();
            this.network.moveTo({ scale: currentScale * 1.25, animation: { duration: 250 } });
        },
        zoomOut() {
            if (!this.network) return;
            const currentScale = this.network.getScale();
            this.network.moveTo({ scale: currentScale * 0.8, animation: { duration: 250 } });
        },
        resetScale() {
            if (!this.network) return;
            this.network.moveTo({ scale: 1.0, animation: { duration: 250 } });
        },
        fitViewSmart() {
            if (!this.network) return;
            this.network.fit({
                padding: 40,
                animation: { duration: 350, easingFunction: 'easeInOutQuad' }
            });
        },
        toggleFullscreen() {
            const el = document.getElementById('topology-card-wrapper');
            if (!el) return;

            if (!document.fullscreenElement) {
                el.requestFullscreen().then(() => {
                    this.isFullscreen = true;
                    setTimeout(() => this.fitViewSmart(), 300);
                }).catch(() => {});
            } else {
                document.exitFullscreen().then(() => {
                    this.isFullscreen = false;
                    setTimeout(() => this.fitViewSmart(), 300);
                }).catch(() => {});
            }
        }
    };
}
</script>
