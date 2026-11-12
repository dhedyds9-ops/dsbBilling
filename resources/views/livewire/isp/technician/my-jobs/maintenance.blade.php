<div class="space-y-3.5 pb-6">
    @section('header_title', 'Jadwal Maintenance & Diagnostik ONT')
    @if(session('success'))
        <div class="p-3 bg-emerald-500 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 bg-rose-500 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-sm">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="p-4 sm:p-5 rounded-2xl shadow-md text-white"
         style="background: linear-gradient(135deg, #0284c7 0%, #4338ca 50%, #3730a3 100%) !important; color: #ffffff !important;">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl text-white">engineering</span>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-black leading-tight">Pemeliharaan & Diagnostik ONT</h1>
                    <p class="text-[11px] sm:text-xs text-sky-100">Pemeliharaan jaringan lapangan, tiket gangguan & Live ONT Diagnostics via GenieACS</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl flex items-center shadow-inner">
        <button wire:click="switchTab('active')" type="button"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                       {{ $activeTab === 'active' ? 'bg-white dark:bg-slate-900 text-sky-600 dark:text-sky-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">event_upcoming</span>
            <span>Tugas Aktif ({{ $activeCount ?? 0 }})</span>
        </button>
        <button wire:click="switchTab('history')" type="button"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                       {{ $activeTab === 'history' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Riwayat ({{ $historyCount ?? 0 }})</span>
        </button>
    </div>

    {{-- Search --}}
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:20px">search</span>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari jadwal maintenance, pelanggan, perangkat, atau lokasi..."
               class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-900 dark:text-slate-100 shadow-sm">
        @if($search)
            <button wire:click="$set('search', '')" type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        @endif
    </div>

    {{-- Cards --}}
    <div class="space-y-3">
        @forelse($jobs as $job)
            <div wire:key="maint-item-{{ $job->source }}-{{ $job->id }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $job->is_completed ? 'bg-emerald-500' : (in_array($job->priority, ['critical', 'high']) ? 'bg-rose-500' : 'bg-sky-500') }}"></div>

                <div class="flex justify-between items-start gap-2 mb-3 pb-2.5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400 text-xs font-semibold">
                        <span class="material-symbols-outlined text-sky-500 text-sm">calendar_month</span>
                        {{ $job->date ? \Carbon\Carbon::parse($job->date)->format('d M Y, H:i') : '-' }}
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="px-2 py-0.5 bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-lg text-[10px] font-bold uppercase">
                            {{ $job->type_label }}
                        </span>
                        <span class="px-2 py-0.5 {{ $job->is_completed ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800' }} border rounded-lg text-[10px] font-bold uppercase">
                            {{ $job->is_completed ? 'Selesai' : ($job->status === 'in_progress' ? 'Dikerjakan' : 'Dijadwalkan') }}
                        </span>
                    </div>
                </div>

                <div>
                    <h3 class="font-black text-base text-slate-900 dark:text-white leading-tight">{{ $job->target_name }}</h3>
                    <div class="flex flex-wrap items-center gap-2 mt-0.5">
                        <span class="text-[11px] font-mono text-slate-400">Kode: {{ $job->code }} · Prioritas: {{ strtoupper($job->priority) }}</span>
                        @if(!empty($job->onu_sn))
                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded font-mono text-[10px] font-bold">
                                ONT: {{ $job->onu_sn }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-start gap-1.5 mt-2 text-slate-500 dark:text-slate-400 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5 shrink-0 text-sm">location_on</span>
                    <span class="leading-snug">{{ $job->location }}</span>
                </div>

                <div class="mt-2.5 bg-sky-50/70 dark:bg-sky-950/30 p-2.5 rounded-xl border border-sky-200/70 dark:border-sky-800/50 text-[11px] space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-sky-900 dark:text-sky-200">
                        <span class="material-symbols-outlined text-sky-600 text-sm">build_circle</span>
                        <span>{{ $job->title }}</span>
                    </div>
                    @if($job->description)
                        <p class="text-slate-600 dark:text-slate-300 text-xs pl-5 leading-relaxed">{{ $job->description }}</p>
                    @endif
                </div>

                @if(!empty($job->resolution_notes))
                    <div class="mt-2 bg-emerald-50/80 dark:bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-200/70 dark:border-emerald-800/50 text-[11px] space-y-0.5">
                        <div class="flex items-center gap-1.5 font-bold text-emerald-800 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-emerald-600 text-sm">task_alt</span>
                            <span>Laporan Penyelesaian:</span>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-xs pl-5 leading-relaxed">{{ $job->resolution_notes }}</p>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="mt-3.5 flex flex-wrap items-center gap-2">
                    {{-- Tombol Live Diagnostik ONT (P1-1B) --}}
                    <button wire:click="openDiagnostics('{{ $job->source }}', {{ $job->id }})" type="button"
                            class="py-2 px-3 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-sm">sensors</span>
                        <span>Diagnostik ONT</span>
                    </button>

                    @if(!$job->is_completed)
                        @if(in_array($job->status, ['scheduled', 'open']))
                            <button wire:click="startItem('{{ $job->source }}', {{ $job->id }})" type="button"
                                    class="py-2 px-3.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1 shadow-sm active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-base">play_arrow</span>
                                Mulai Kerjakan
                            </button>
                        @endif
                        <button wire:click="openCompleteModal('{{ $job->source }}', {{ $job->id }}, false)" type="button"
                                class="flex-1 py-2 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            Selesaikan
                        </button>
                    @else
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold ml-auto">
                            Selesai: {{ $job->completed_at ? \Carbon\Carbon::parse($job->completed_at)->format('d M Y, H:i') : '-' }}
                        </div>
                        @if(!$job->is_locked)
                            <button wire:click="openCompleteModal('{{ $job->source }}', {{ $job->id }}, true)" type="button"
                                    class="py-1.5 px-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-sm">edit_note</span>
                                Edit Laporan
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="py-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                <div class="w-16 h-16 mx-auto bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-3xl text-slate-400">thumb_up</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">
                    {{ $activeTab === 'active' ? 'Tidak ada jadwal maintenance aktif' : 'Belum ada riwayat maintenance' }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                    Jadwal maintenance yang ditugaskan kepada Anda dari Admin Support akan tampil di sini.
                </p>
            </div>
        @endforelse
    </div>

    @if($jobs->hasPages())
        <div class="pt-2">
            {{ $jobs->links(data: ['scrollTo' => false]) }}
        </div>
    @endif
</div>

{{-- ========================================================================= --}}
{{-- MODAL LIVE ONT DIAGNOSTICS & FIELD ACTIONS (P1-1B)                          --}}
{{-- ========================================================================= --}}
@if($showDiagnosticModal)
<div class="fixed inset-0 z-[120] flex items-center justify-center p-2 sm:p-4 bg-slate-950/80 backdrop-blur-md overflow-y-auto"
     x-data
     @keydown.escape.window="$wire.closeDiagnostics()">
    <div class="bg-slate-50 dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full my-auto overflow-hidden text-slate-900 dark:text-slate-100 flex flex-col max-h-[92vh]">
        
        {{-- Header Modal --}}
        <div class="bg-gradient-to-r from-sky-600 via-indigo-600 to-indigo-800 text-white p-4 sm:p-5 shrink-0 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">sensors</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-black tracking-tight leading-tight">Live ONT Diagnostics</h2>
                        <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-bold uppercase tracking-wider">NBI REST</span>
                    </div>
                    <p class="text-[11px] text-sky-100">Pemantauan real-time status optik, Wi-Fi & kendali perangkat pelanggan</p>
                </div>
            </div>
            <button wire:click="closeDiagnostics" type="button" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        {{-- Alerts --}}
        <div class="px-4 pt-3 shrink-0 space-y-2">
            @if($diagnosticSuccess)
                <div class="p-3 bg-emerald-500 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    <span>{{ $diagnosticSuccess }}</span>
                </div>
            @endif

            @if($diagnosticError)
                <div class="p-3 bg-rose-500 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span>{{ $diagnosticError }}</span>
                </div>
            @endif

            @if($rebootStatusMessage)
                <div class="p-3 {{ $rebootVerified ? 'bg-emerald-600' : 'bg-sky-600' }} text-white rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">{{ $rebootVerified ? 'verified' : 'sync' }}</span>
                        <span>{{ $rebootStatusMessage }}</span>
                    </div>
                    @if(!$rebootVerified)
                        <button wire:click="verifyReboot" type="button" class="px-2.5 py-1 bg-white text-sky-700 font-bold text-[10px] rounded-lg shadow hover:bg-sky-50 transition-colors">
                            Cek Status Reboot
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Modal Body (Scrollable) --}}
        <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 text-xs">
            @if($diagnostics)
                {{-- 1. IDENTITAS PELANGGAN & ONT --}}
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                        <span class="font-bold text-[11px] text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-sky-500">person</span>
                            Identitas Pelanggan & ONT
                        </span>
                        <span class="font-mono text-[10px] text-slate-400">ID: #{{ $diagnostics['onu_id'] }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Nama Pelanggan</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $diagnosticCustomerName }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Serial Number (SN)</span>
                            <span class="font-mono font-bold text-sky-600 dark:text-sky-400">{{ $diagnostics['serial_number'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Alamat / Lokasi</span>
                            <span class="text-slate-700 dark:text-slate-300">{{ $diagnosticCustomerAddress }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">GenieACS Device ID</span>
                            <span class="font-mono text-[10px] text-slate-500 truncate block max-w-xs" title="{{ $diagnostics['device_id'] }}">
                                {{ $diagnostics['device_id'] ?: 'Belum Terdaftar' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 2. STATUS ONLINE / OFFLINE --}}
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-slate-700">
                        <span class="font-bold text-[11px] text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-indigo-500">wifi_tethering</span>
                            Status Koneksi Perangkat
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if($diagnostics['status_badge'] === 'green')
                                <span class="flex h-2.5 w-2.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold text-[11px]">ONLINE</span>
                            @elseif($diagnostics['status_badge'] === 'yellow')
                                <span class="inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold text-[11px]">STALE (TERLAMBAT)</span>
                            @elseif($diagnostics['status_badge'] === 'red')
                                <span class="inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-bold text-[11px]">OFFLINE</span>
                            @else
                                <span class="inline-flex rounded-full h-2.5 w-2.5 bg-slate-400"></span>
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold text-[11px]">TIDAK TERDAFTAR</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl">
                            <span class="text-slate-400 block text-[10px]">Last Inform</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 block truncate" title="{{ $diagnostics['last_inform'] }}">
                                {{ $diagnostics['last_inform'] }}
                            </span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl">
                            <span class="text-slate-400 block text-[10px]">Uptime Perangkat</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 block truncate">
                                {{ $diagnostics['uptime'] }}
                            </span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl">
                            <span class="text-slate-400 block text-[10px]">Last Booting</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 block truncate">
                                {{ $diagnostics['last_boot'] }}
                            </span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl">
                            <span class="text-slate-400 block text-[10px]">Status NBI</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 block truncate">
                                {{ $diagnostics['connection_status'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 3. OPTICAL DIAGNOSTICS (REDAMAN OPTIK) --}}
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-slate-700">
                        <span class="font-bold text-[11px] text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-amber-500">light_mode</span>
                            Optical Diagnostics (Sinyal GPON / EPON)
                        </span>
                        <span class="text-[10px] text-slate-400">Standar Redaman: -15 s/d -24 dBm</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        {{-- RX Optical Power --}}
                        <div class="p-3 rounded-2xl border transition-all
                            @if($diagnostics['optical']['rx_power_status'] === 'normal')
                                bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100
                            @elseif($diagnostics['optical']['rx_power_status'] === 'warning')
                                bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-100
                            @elseif($diagnostics['optical']['rx_power_status'] === 'critical')
                                bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-100
                            @else
                                bg-slate-50 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300
                            @endif">
                            <span class="text-[10px] font-bold uppercase block opacity-70">RX Optical Power</span>
                            <div class="text-base sm:text-lg font-black mt-1">
                                {{ $diagnostics['optical']['rx_power_display'] }}
                            </div>
                            <span class="text-[9px] font-bold uppercase mt-1 inline-block">
                                @if($diagnostics['optical']['rx_power_status'] === 'normal')
                                    ● Sinyal Sangat Baik
                                @elseif($diagnostics['optical']['rx_power_status'] === 'warning')
                                    ▲ Redaman Marginal
                                @elseif($diagnostics['optical']['rx_power_status'] === 'critical')
                                    ✖ Redaman Kritis
                                @else
                                    - Tidak Didukung
                                @endif
                            </span>
                        </div>

                        {{-- TX Optical Power --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase block text-slate-400">TX Optical Power</span>
                            <div class="text-base sm:text-lg font-black mt-1 text-slate-800 dark:text-slate-100">
                                {{ $diagnostics['optical']['tx_power_display'] }}
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1 inline-block">Daya Pancar Laser</span>
                        </div>

                        {{-- Optical Temperature --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase block text-slate-400">Suhu Optik SFP</span>
                            <div class="text-base sm:text-lg font-black mt-1 text-slate-800 dark:text-slate-100">
                                {{ $diagnostics['optical']['optical_temperature'] }}
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1 inline-block">Transceiver Temp</span>
                        </div>

                        {{-- LOS Status --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase block text-slate-400">Status LOS</span>
                            <div class="text-xs sm:text-sm font-black mt-1.5 truncate text-slate-800 dark:text-slate-100" title="{{ $diagnostics['optical']['los_status'] }}">
                                {{ $diagnostics['optical']['los_status'] }}
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1 inline-block">Loss of Signal Alarm</span>
                        </div>
                    </div>
                </div>

                {{-- 4. HARDWARE INFORMATION --}}
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <span class="font-bold text-[11px] text-slate-500 uppercase tracking-wider block pb-2 mb-2 border-b border-slate-100 dark:border-slate-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-slate-500">router</span>
                        Informasi Perangkat Keras (Hardware)
                    </span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Vendor / Pabrikan</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $diagnostics['hardware']['manufacturer'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Tipe / Product Class</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $diagnostics['hardware']['product_class'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Hardware Version</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $diagnostics['hardware']['hardware_version'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Firmware / Software</span>
                            <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300">{{ $diagnostics['hardware']['software_version'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Suhu Perangkat</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $diagnostics['hardware']['temperature'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- 5. WI-FI STATUS --}}
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                        <span class="font-bold text-[11px] text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-sky-500">wifi</span>
                            Status Konfigurasi Wi-Fi
                        </span>
                        <span class="px-2 py-0.5 rounded-full {{ $diagnostics['wifi']['radio_status'] === 'Aktif' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-200 text-slate-600' }} font-bold text-[10px]">
                            Radio: {{ $diagnostics['wifi']['radio_status'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px]">SSID 2.4 GHz</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $diagnostics['wifi']['ssid_24'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">SSID 5 GHz</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $diagnostics['wifi']['ssid_5'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Security Mode</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $diagnostics['wifi']['security_mode'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Wi-Fi Password</span>
                            <span class="font-mono text-slate-400 text-xs">•••••••• (Tersimpan Aman)</span>
                        </div>
                    </div>
                </div>

                {{-- 6. CONNECTED WI-FI CLIENTS --}}
                <div class="bg-white dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-700">
                        <span class="font-bold text-[11px] text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-violet-500">devices</span>
                            Daftar Klien Terhubung ({{ $diagnostics['wifi']['client_count'] }})
                        </span>
                        <span class="text-[10px] text-slate-400">Host Table & DHCP Lease</span>
                    </div>

                    @if(count($diagnostics['wifi']['clients']) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[11px]">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-100 dark:border-slate-700/60 font-semibold">
                                        <th class="pb-1.5 pr-2">Perangkat / Nama</th>
                                        <th class="pb-1.5 px-2">IP Address</th>
                                        <th class="pb-1.5 px-2">MAC Address</th>
                                        <th class="pb-1.5 px-2">Media</th>
                                        <th class="pb-1.5 pl-2 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($diagnostics['wifi']['clients'] as $client)
                                        <tr>
                                            <td class="py-2 pr-2 font-bold text-slate-800 dark:text-slate-200 max-w-[120px] truncate" title="{{ $client['host_name'] }}">
                                                {{ $client['host_name'] }}
                                            </td>
                                            <td class="py-2 px-2 font-mono text-slate-600 dark:text-slate-400">{{ $client['ip_address'] }}</td>
                                            <td class="py-2 px-2 font-mono text-[10px] text-slate-500">{{ $client['mac_address'] }}</td>
                                            <td class="py-2 px-2">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $client['interface_type'] === 'Wi-Fi' ? 'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                                    {{ $client['interface_type'] }}
                                                </span>
                                            </td>
                                            <td class="py-2 pl-2 text-right">
                                                @if($client['active'])
                                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold text-[10px]">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 text-[10px]">Idle</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <span class="material-symbols-outlined text-2xl mb-1 text-slate-300 dark:text-slate-600 block">wifi_off</span>
                            Tidak ada perangkat klien yang sedang terhubung ke Wi-Fi / LAN saat ini.
                        </div>
                    @endif
                </div>

                {{-- 7. ACTION PANEL (FIELD ACTIONS) --}}
                <div class="bg-gradient-to-r from-slate-100 to-sky-50 dark:from-slate-800 dark:to-slate-800/60 rounded-2xl p-4 border border-sky-100 dark:border-slate-700 shadow-sm space-y-2">
                    <span class="font-bold text-[11px] text-sky-800 dark:text-sky-300 uppercase tracking-wider block flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-sky-600">tune</span>
                        Action Panel Lapangan
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                        {{-- Tombol Refresh Parameter (Summon) --}}
                        <button wire:click="refreshDiagnostics" type="button" wire:loading.attr="disabled"
                                class="py-2.5 px-3 bg-white dark:bg-slate-900 hover:bg-sky-50 dark:hover:bg-slate-800 border border-sky-200 dark:border-slate-700 text-sky-700 dark:text-sky-300 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                            <span wire:loading.remove wire:target="refreshDiagnostics" class="material-symbols-outlined text-sm">refresh</span>
                            <span wire:loading wire:target="refreshDiagnostics" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            <span>Refresh Data (Summon)</span>
                        </button>

                        {{-- Tombol Reboot ONT --}}
                        <button wire:click="confirmReboot" type="button"
                                class="py-2.5 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                            <span class="material-symbols-outlined text-sm">restart_alt</span>
                            <span>Reboot ONT</span>
                        </button>

                        {{-- Tombol Ubah Wi-Fi --}}
                        <button wire:click="openWifiModal" type="button"
                                class="py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                            <span class="material-symbols-outlined text-sm">edit</span>
                            <span>Ubah Wi-Fi ONT</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl mb-2 text-slate-300 dark:text-slate-600 block animate-pulse">sensors</span>
                    <p class="font-bold text-sm text-slate-700 dark:text-slate-300">Menghubungkan ke GenieACS NBI...</p>
                    <p class="text-xs text-slate-400 mt-1">Mengambil parameter optik dan konfigurasi live dari perangkat pelanggan.</p>
                </div>
            @endif
        </div>

        {{-- Footer Modal --}}
        <div class="p-3 bg-slate-100 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700/60 shrink-0 flex items-center justify-between text-[11px] text-slate-500">
            <div>
                Data Terakhir: <span class="font-semibold">{{ $diagnostics['updated_at'] ?? '-' }}</span>
            </div>
            <button wire:click="closeDiagnostics" type="button" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 rounded-xl font-bold text-xs transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif

{{-- ========================================================================= --}}
{{-- MODAL KONFIRMASI REBOOT ONT (P1-1B)                                       --}}
{{-- ========================================================================= --}}
@if($showRebootModal)
<div class="fixed inset-0 z-[130] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
     x-data
     @keydown.escape.window="$wire.set('showRebootModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm w-full p-5 text-center relative overflow-hidden">
        <div class="w-14 h-14 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <span class="material-symbols-outlined" style="font-size:32px">restart_alt</span>
        </div>

        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight">
            Konfirmasi Reboot ONT?
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
            Perintah restart akan dikirim melalui GenieACS NBI REST API. Koneksi internet pelanggan akan terputus sementara selama 1-2 menit.
        </p>

        <div class="mt-4 flex items-center gap-2">
            <button wire:click="$set('showRebootModal', false)" type="button"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                Batal
            </button>
            <button wire:click="executeReboot" type="button" wire:loading.attr="disabled"
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all">
                <span wire:loading.remove wire:target="executeReboot" class="material-symbols-outlined text-sm">restart_alt</span>
                <span wire:loading wire:target="executeReboot" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span>Ya, Reboot Sekarang</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ========================================================================= --}}
{{-- MODAL UBAH WI-FI ONT (P1-1B)                                              --}}
{{-- ========================================================================= --}}
@if($showWifiModal)
<div class="fixed inset-0 z-[130] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
     x-data="{ showPass: false }"
     @keydown.escape.window="$wire.set('showWifiModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm w-full p-5 text-left relative overflow-hidden">
        <div class="flex items-center gap-2.5 mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">wifi_password</span>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">Ubah Wi-Fi ONT</h3>
                <p class="text-[10px] text-slate-400">Kirim konfigurasi SSID & Password baru via TR-069</p>
            </div>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase text-[10px] mb-1">SSID Wi-Fi (Nama Jaringan)</label>
                <input type="text" wire:model="wifi_ssid" placeholder="Contoh: MyHome-Fiber"
                       class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                @error('wifi_ssid') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase text-[10px]">Password Baru (WPA2-PSK)</label>
                    <button type="button" @click="showPass = !showPass" class="text-[10px] text-sky-600 font-semibold hover:underline">
                        <span x-text="showPass ? 'Sembunyikan' : 'Tampilkan'"></span>
                    </button>
                </div>
                <input :type="showPass ? 'text' : 'password'" wire:model="wifi_password" placeholder="Kosongkan jika tidak ingin mengubah sandi"
                       class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                <span class="text-slate-400 text-[10px] mt-0.5 block">Minimal 8 karakter. Sandi tersanitasi aman dari sistem log.</span>
                @error('wifi_password') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-5 flex items-center gap-2">
            <button wire:click="$set('showWifiModal', false)" type="button"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                Batal
            </button>
            <button wire:click="saveWifi" type="button" wire:loading.attr="disabled"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all">
                <span wire:loading.remove wire:target="saveWifi" class="material-symbols-outlined text-sm">save</span>
                <span wire:loading wire:target="saveWifi" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span>Simpan Wi-Fi</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ========================================================================= --}}
{{-- MODAL RESOLUTION / COMPLETION MAINTENANCE WORKFLOW                         --}}
{{-- ========================================================================= --}}
@if($showModal)
<div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-data
     @keydown.escape.window="$wire.set('showModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm w-full p-5 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-500 to-emerald-500"></div>

        <div class="w-14 h-14 bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
            <span class="material-symbols-outlined" style="font-size:30px">{{ $isEditing ? 'edit_note' : 'task_alt' }}</span>
        </div>

        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight">
            {{ $isEditing ? 'Edit Penyelesaian Maintenance' : 'Selesaikan Maintenance?' }}
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $selectedTitle }}</p>

        <div class="mt-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800 text-left text-xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Kode</span>
                <span class="font-mono font-bold text-sky-600 dark:text-sky-400">{{ $selectedCode }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Perangkat / Lokasi</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[180px]">{{ $selectedTarget }}</span>
            </div>
        </div>

        <div class="mt-3 text-left space-y-3">
            @if($isEditing)
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Status Tugas</label>
                    <select wire:model="edit_status"
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                        <option value="completed">Tetap Selesai (Perbarui Laporan)</option>
                        <option value="in_progress">Buka Kembali ke Tugas Aktif</option>
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Laporan / Catatan Penyelesaian</label>
                <textarea wire:model="resolution_notes" rows="3" placeholder="Contoh: Pembersihan SFP & ODP selesai, redaman optik normal -18 dBm..."
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-800 dark:text-slate-100 shadow-inner"></textarea>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button wire:click="$set('showModal', false)" type="button"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                Batal
            </button>
            <button wire:click="saveCompletion" type="button"
                    wire:loading.attr="disabled"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all">
                <span wire:loading.remove wire:target="saveCompletion" class="material-symbols-outlined text-sm">check</span>
                <span wire:loading wire:target="saveCompletion" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                <span>{{ $isEditing ? 'Simpan' : 'Ya, Selesaikan' }}</span>
            </button>
        </div>
    </div>
</div>
@endif
