@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">archive</span>
    <span class="text-lg">Detail Joint Closure: {{ $closure->name }}</span>
@endsection

<div class="space-y-5 pb-16">
    {{-- ============================================================
         FLASH ALERTS
         ============================================================ --}}
    @if(session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-500">error</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
    @endif

    {{-- ============================================================
         1. HEADER JOINT CLOSURE DETAIL
         ============================================================ --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.joint-closures.index') }}" class="p-2.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-md shadow-indigo-500/20">
                <span class="material-symbols-outlined notranslate" style="font-size:26px">archive</span>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $closure->name }}</h1>
                    <span class="px-2.5 py-0.5 font-mono text-xs font-semibold rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        {{ $closure->code }}
                    </span>
                    @php
                        $statusBadge = match($closure->status) {
                            'active' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                            default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700'
                        };
                        $typeBadge = match(strtolower($closure->type ?? 'dome')) {
                            'dome' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                            'inline' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-400 border-cyan-200 dark:border-cyan-800',
                            default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800'
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 font-semibold text-xs rounded-full border {{ $statusBadge }} flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $closure->status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-current' }}"></span>
                        {{ ucfirst($closure->status ?? 'active') }}
                    </span>
                    <span class="px-2 py-0.5 font-bold text-xs rounded-md border {{ $typeBadge }}">
                        {{ $closure->type ?? 'Dome' }}
                    </span>
                    <span class="px-2 py-0.5 font-bold text-xs rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        {{ $closure->port_count ?? 24 }} Core
                    </span>
                    @if($closure->branch)
                        <span class="px-2.5 py-0.5 text-xs rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            Cabang: {{ $closure->branch->name }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                    <span>{{ $closure->address ?: 'Alamat belum diisi' }}</span>
                    @if($closure->parentClosure)
                        <span class="text-slate-400">·</span>
                        <a href="{{ route('isp.joint-closures.show', $closure->parentClosure->id) }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline inline-flex items-center gap-1">
                            <span class="material-symbols-outlined notranslate" style="font-size:14px">subdirectory_arrow_right</span>
                            Induk Closure: {{ $closure->parentClosure->name }} ({{ $closure->parentClosure->code }})
                        </a>
                    @elseif($closure->odc)
                        <span class="text-slate-400">·</span>
                        <a href="{{ route('isp.odcs.show', $closure->odc->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline inline-flex items-center gap-1">
                            <span class="material-symbols-outlined notranslate" style="font-size:14px">hub</span>
                            Uplink ODC: {{ $closure->odc->name }} ({{ $closure->odc->code }})
                        </a>
                    @elseif($closure->olt)
                        <span class="text-slate-400">·</span>
                        <a href="{{ route('isp.olts.show', $closure->olt->id) }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline inline-flex items-center gap-1">
                            <span class="material-symbols-outlined notranslate" style="font-size:14px">dns</span>
                            OLT Core: {{ $closure->olt->name }} {{ $closure->pon_port ? '· PON ' . $closure->pon_port : '' }}
                        </a>
                    @endif
                </p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('gis.map', ['lat' => $closure->latitude, 'lng' => $closure->longitude]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-500/20 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-sm">map</span>
                Peta GIS
            </a>
            <a href="{{ route('isp.joint-closures.edit', $closure->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-all shadow-2xs">
                <span class="material-symbols-outlined text-sm">edit</span>
                Edit Closure
            </a>
            <a href="{{ route('isp.joint-closures.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700/60 transition-all">
                <span class="material-symbols-outlined text-sm">list</span>
                Daftar Closure
            </a>
        </div>
    </div>

    {{-- ============================================================
         2. RINGKASAN KPI METRIC CARDS
         ============================================================ --}}
    @php
        $totalOnus = 0;
        foreach ($connectedOdps as $odpItem) {
            $totalOnus += ($odpItem->onus ? $odpItem->onus->count() : 0);
        }
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Kapasitas Core</span>
            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1 block">{{ $closure->port_count ?? 24 }}</span>
            <span class="text-[11px] text-indigo-500 font-semibold">Kapasitas Sambungan</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Model Enclosure</span>
            <span class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1 block">{{ $closure->type ?? 'Dome' }}</span>
            <span class="text-[11px] text-purple-500">Tipe Box Pelindung</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">ODP Terhubung</span>
            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $connectedOdps->count() }}</span>
            <span class="text-[11px] text-emerald-500 font-semibold">Titik Distribusi</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">ODC Terhubung</span>
            <span class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ $connectedOdcs->count() }}</span>
            <span class="text-[11px] text-amber-500 font-semibold">Kabinet Feeder</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Sub-Closure FO</span>
            <span class="text-xl font-black text-cyan-600 dark:text-cyan-400 mt-1 block">{{ $childClosures->count() }}</span>
            <span class="text-[11px] text-cyan-500">Cascade Splice</span>
        </div>
        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <span class="text-xs text-slate-400 block font-medium">Pelanggan Dilayani</span>
            <span class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 block">{{ $totalOnus }}</span>
            <span class="text-[11px] text-blue-500 font-semibold">Unit ONT / Pelanggan</span>
        </div>
    </div>

    {{-- ============================================================
         3. TAB NAVIGATION
         ============================================================ --}}
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 overflow-x-auto pb-px">
        <button type="button" wire:click="setTab('overview')"
                class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 whitespace-nowrap transition-all {{ $activeTab === 'overview' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">info</span>
            <span>Ringkasan & Spesifikasi</span>
        </button>
        <button type="button" wire:click="setTab('odps')"
                class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 whitespace-nowrap transition-all {{ $activeTab === 'odps' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">home_work</span>
            <span>ODP Terhubung</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'odps' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                {{ $connectedOdps->count() }}
            </span>
        </button>
        <button type="button" wire:click="setTab('odcs')"
                class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 whitespace-nowrap transition-all {{ $activeTab === 'odcs' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">hub</span>
            <span>ODC Terhubung</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'odcs' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                {{ $connectedOdcs->count() }}
            </span>
        </button>
        <button type="button" wire:click="setTab('history')"
                class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 whitespace-nowrap transition-all {{ $activeTab === 'history' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Riwayat & Foto Tiang</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'history' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                {{ is_array($closure->photos) ? count($closure->photos) : 0 }}
            </span>
        </button>
    </div>

    {{-- ============================================================
         TAB 1: OVERVIEW
         ============================================================ --}}
    @if($activeTab === 'overview')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Card Kiri: Spesifikasi Teknis --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-500">settings</span>
                    Spesifikasi Teknis Joint Closure
                </h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-400 font-medium">Nama Perangkat</dt>
                        <dd class="text-slate-800 dark:text-slate-200 font-bold text-sm mt-0.5">{{ $closure->name }}</dd>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-400 font-medium">Kode Referensi</dt>
                        <dd class="font-mono text-indigo-600 dark:text-indigo-400 font-bold text-sm mt-0.5">{{ $closure->code }}</dd>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-400 font-medium">Kapasitas Core Optik</dt>
                        <dd class="text-slate-800 dark:text-slate-200 font-black text-sm mt-0.5">{{ $closure->port_count ?? 24 }} Core</dd>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-400 font-medium">Model Enclosure</dt>
                        <dd class="text-purple-600 dark:text-purple-400 font-bold text-sm mt-0.5">{{ $closure->type ?? 'Dome' }}</dd>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-400 font-medium">Vendor / Pabrikan</dt>
                        <dd class="text-slate-800 dark:text-slate-200 font-semibold mt-0.5">{{ $closure->vendor ? $closure->vendor->name : '-' }}</dd>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-400 font-medium">Koordinat GPS</dt>
                        <dd class="font-mono text-slate-800 dark:text-slate-200 mt-0.5">
                            @if($closure->latitude && $closure->longitude)
                                {{ $closure->latitude }}, {{ $closure->longitude }}
                            @else
                                <span class="text-slate-400">Belum diset</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2 p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-800">
                        <dt class="text-slate-400 font-medium">Alamat / Patokan Tiang</dt>
                        <dd class="text-slate-800 dark:text-slate-200 font-semibold mt-0.5">{{ $closure->address ?: 'Tidak ada keterangan alamat/tiang.' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Relasi Jalur Backbone (Uplink / Cascade) --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-500">timeline</span>
                    Jalur Uplink & Sumber Distribusi
                </h3>

                <div class="p-4 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <span class="material-symbols-outlined">hub</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Sumber Uplink Fiber</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                @if($closure->parentClosure)
                                    Induk Joint Closure: {{ $closure->parentClosure->name }} ({{ $closure->parentClosure->code }})
                                @elseif($closure->odc)
                                    Kabinet ODC: {{ $closure->odc->name }} ({{ $closure->odc->code }})
                                @elseif($closure->olt)
                                    OLT Core: {{ $closure->olt->name }} {{ $closure->pon_port ? '· PON ' . $closure->pon_port : '' }}
                                @else
                                    <span class="text-slate-400">Titik Awal (Belum terhubung uplink)</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @if($closure->parentClosure)
                        <a href="{{ route('isp.joint-closures.show', $closure->parentClosure->id) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Induk &rarr;</a>
                    @elseif($closure->odc)
                        <a href="{{ route('isp.odcs.show', $closure->odc->id) }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">Lihat ODC &rarr;</a>
                    @elseif($closure->olt)
                        <a href="{{ route('isp.olts.show', $closure->olt->id) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Lihat OLT &rarr;</a>
                    @endif
                </div>

                @if($childClosures->count() > 0)
                <div class="mt-4">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-2">Sub-Closure Terhubung (Cascade):</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($childClosures as $child)
                            <a href="{{ route('isp.joint-closures.show', $child->id) }}" class="p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-indigo-400 transition-colors flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-purple-500 text-sm">archive</span>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">{{ $child->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $child->code }} · {{ $child->port_count ?? 24 }} Core</span>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 text-xs">arrow_forward</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Card Kanan: Mini Peta & Foto Utama --}}
        <div class="space-y-5">
            {{-- GIS Lokasi Preview --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-500">pin_drop</span>
                    Lokasi Lapangan
                </h3>
                @if($closure->latitude && $closure->longitude)
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl mb-3">
                        <div class="flex items-center justify-between text-xs font-mono font-bold text-emerald-800 dark:text-emerald-300">
                            <span>Lat: {{ $closure->latitude }}</span>
                            <span>Lng: {{ $closure->longitude }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="https://www.google.com/maps?q={{ $closure->latitude }},{{ $closure->longitude }}" target="_blank"
                           class="flex-1 py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl text-center transition-colors">
                            Google Maps &rarr;
                        </a>
                        <a href="{{ route('gis.map', ['lat' => $closure->latitude, 'lng' => $closure->longitude]) }}"
                           class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl text-center shadow-xs transition-colors">
                            Buka di Peta GIS
                        </a>
                    </div>
                @else
                    <div class="p-6 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                        <span class="material-symbols-outlined text-3xl text-slate-400 mb-1">location_off</span>
                        <p class="text-xs text-slate-500 font-medium">Koordinat belum diisi.</p>
                        <a href="{{ route('isp.joint-closures.edit', $closure->id) }}" class="inline-block mt-2 text-xs font-bold text-indigo-600 hover:underline">Edit Koordinat &rarr;</a>
                    </div>
                @endif
            </div>

            {{-- Dokumentasi Tiang --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-500">photo_camera</span>
                        Foto Tiang / Lapangan
                    </h3>
                    <button type="button" wire:click="setTab('history')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Kelola</button>
                </div>

                @php $photos = is_array($closure->photos) ? $closure->photos : []; @endphp
                @if(count($photos) > 0)
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(array_slice($photos, 0, 4) as $photo)
                            <a href="{{ asset($photo) }}" target="_blank" class="block aspect-video rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:opacity-90">
                                <img src="{{ asset($photo) }}" class="w-full h-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                        <span class="material-symbols-outlined text-3xl text-slate-400 mb-1">add_a_photo</span>
                        <p class="text-xs text-slate-500 font-medium">Belum ada foto dokumentasi tiang.</p>
                        <button type="button" wire:click="setTab('history')" class="inline-block mt-2 text-xs font-bold text-indigo-600 hover:underline">Unggah Foto Sekarang &rarr;</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================
         TAB 2: ODP TERHUBUNG
         ============================================================ --}}
    @if($activeTab === 'odps')
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider">ODP Terhubung Melalui Splice Ini</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar Optical Distribution Point yang mendapatkan core dari joint closure ini</p>
            </div>
            <a href="{{ route('isp.odps.create') }}?closure_id={{ $closure->id }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs">
                <span class="material-symbols-outlined text-sm">add</span>
                Tambah ODP di Titik Ini
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nama ODP</th>
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Splitter / Port</th>
                        <th class="py-3 px-4">Terpakai</th>
                        <th class="py-3 px-4">Pelanggan ONT</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($connectedOdps as $odp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-750/50 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                                <a href="{{ route('isp.odps.show', $odp->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $odp->name }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-400">{{ $odp->code }}</td>
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-semibold">
                                {{ $odp->port_count ?? 8 }} Port
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $used = $odp->ports ? $odp->ports->where('status', 'used')->count() : ($odp->onus ? $odp->onus->count() : 0);
                                    $pct = ($odp->port_count > 0) ? round(($used / $odp->port_count) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-indigo-600 h-full rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span class="font-mono text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ $used }}/{{ $odp->port_count }} ({{ $pct }}%)</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md font-bold text-[11px] bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    {{ $odp->onus ? $odp->onus->count() : 0 }} Unit
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $odp->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $odp->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('isp.odps.show', $odp->id) }}" class="p-1.5 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 inline-block font-semibold">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">home_work</span>
                                <p class="text-xs">Belum ada ODP yang terhubung ke joint closure ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ============================================================
         TAB 3: ODC TERHUBUNG
         ============================================================ --}}
    @if($activeTab === 'odcs')
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider">ODC Terhubung Melalui Splice Ini</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar Optical Distribution Cabinet yang terhubung ke joint closure ini</p>
            </div>
            <a href="{{ route('isp.odcs.create') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs">
                <span class="material-symbols-outlined text-sm">add</span>
                Tambah ODC
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nama ODC</th>
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Total Kapasitas</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($connectedOdcs as $odc)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-750/50 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                                <a href="{{ route('isp.odcs.show', $odc->id) }}" class="text-amber-600 dark:text-amber-400 hover:underline">
                                    {{ $odc->name }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-400">{{ $odc->code }}</td>
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-semibold">
                                {{ $odc->total_ports ?? 24 }} Port Distribusi
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $odc->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $odc->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('isp.odcs.show', $odc->id) }}" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 inline-block font-semibold">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">hub</span>
                                <p class="text-xs">Belum ada ODC yang terhubung ke joint closure ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ============================================================
         TAB 4: RIWAYAT & FOTO TIANG
         ============================================================ --}}
    @if($activeTab === 'history')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Galeri Foto Tiang --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-500">collections</span>
                Galeri Foto Dokumentasi Lapangan
            </h3>

            @php $photos = is_array($closure->photos) ? $closure->photos : []; @endphp
            @if(count($photos) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($photos as $idx => $photo)
                        @php
                            $photoPath = is_array($photo) ? ($photo['url'] ?? $photo['path'] ?? (is_string(reset($photo)) ? reset($photo) : '')) : (is_string($photo) ? $photo : '');
                        @endphp
                        @if(!empty($photoPath))
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 aspect-video bg-slate-100 dark:bg-slate-900">
                            <img src="{{ asset($photoPath) }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <a href="{{ asset($photoPath) }}" target="_blank" class="p-1.5 bg-white text-slate-800 rounded-lg hover:bg-slate-100">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </a>
                                <button type="button" wire:click="deletePhoto({{ $idx }})" wire:confirm="Hapus foto ini?" class="p-1.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">image_not_supported</span>
                    <p class="text-xs text-slate-500 font-medium">Belum ada foto dokumentasi lapangan.</p>
                </div>
            @endif

            {{-- Form Upload Foto Baru --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Unggah Foto Baru</h4>
                <form wire:submit.prevent="uploadPhoto" class="flex flex-col sm:flex-row items-center gap-3">
                    <input type="file" wire:model="newPhoto" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300">
                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs shrink-0 flex items-center justify-center gap-1.5">
                        <span wire:loading.remove>Unggah</span>
                        <span wire:loading>Mengunggah...</span>
                    </button>
                </form>
                @error('newPhoto') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Log Audit --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-500">history</span>
                Informasi Audit Sistem
            </h3>
            <div class="space-y-3 text-xs">
                <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl">
                    <span class="text-slate-400 block font-medium">Dibuat Pada</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $closure->created_at ? $closure->created_at->format('d M Y, H:i') : '-' }}</span>
                    @if($closure->createdBy)
                        <span class="text-slate-400 block text-[11px]">Oleh: {{ $closure->createdBy->name }}</span>
                    @endif
                </div>
                <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl">
                    <span class="text-slate-400 block font-medium">Terakhir Diperbarui</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $closure->updated_at ? $closure->updated_at->format('d M Y, H:i') : '-' }}</span>
                    @if($closure->updatedBy)
                        <span class="text-slate-400 block text-[11px]">Oleh: {{ $closure->updatedBy->name }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
