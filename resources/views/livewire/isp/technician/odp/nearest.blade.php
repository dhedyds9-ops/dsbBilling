<div class="space-y-4 pb-6">
    @section('header_title', 'Peta Jaringan')

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-4 sm:p-5 rounded-2xl shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-xl sm:text-2xl notranslate" translate="no">share_location</span>
                    <h2 class="text-base sm:text-lg font-black tracking-tight">Peta Jaringan & Pendataan Titik</h2>
                </div>
                <p class="text-xs text-indigo-100 max-w-xl">
                    Satu peta topologi terpadu (OLT, ODC, Closure FO, ODP). Cari titik jaringan atau tambahkan titik baru langsung dari lapangan lengkap dengan foto dokumentasi.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-sm notranslate" translate="no">add_location_alt</span>
                    <span>+ Titik Baru</span>
                </button>
                <a href="{{ route('technician.odp.search') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 bg-white/15 hover:bg-white/25 text-white rounded-xl text-xs font-bold transition-colors">
                    <span class="material-symbols-outlined text-sm notranslate" translate="no">list_alt</span>
                    <span>Tabel ODP</span>
                </a>
            </div>
        </div>
    </div>

    @if($createSuccessMessage)
        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 rounded-xl text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>{{ $createSuccessMessage }}</span>
            </div>
            <button type="button" wire:click="$set('createSuccessMessage', null)" class="text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    {{-- Search & Filter Toolbar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/80 p-3.5 sm:p-4 space-y-3">
        {{-- Search Input --}}
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 notranslate" translate="no" style="font-size:20px">search</span>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari kode ODP, nama, atau alamat (misal: ODP-01)..."
                   class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-100 placeholder:text-slate-400">
            @if(!empty($search))
                <button type="button" wire:click="$set('search', '')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                    <span class="material-symbols-outlined text-sm notranslate" translate="no">close</span>
                </button>
            @endif
        </div>

        {{-- Filter Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
            {{-- Radius Filter --}}
            <div class="sm:col-span-4">
                <select wire:model.live="radius"
                        class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="1">Radius 1 KM</option>
                    <option value="2">Radius 2 KM</option>
                    <option value="5">Radius 5 KM (Standar)</option>
                    <option value="10">Radius 10 KM</option>
                    <option value="25">Radius 25 KM</option>
                    <option value="0">Semua Radius (Tanpa Batas)</option>
                </select>
            </div>

            {{-- Status Port Filter --}}
            <div class="sm:col-span-4">
                <select wire:model.live="statusFilter"
                        class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="all">Semua Status Port</option>
                    <option value="available">Port Tersedia (Ready)</option>
                    <option value="full">Port Penuh (Full)</option>
                </select>
            </div>

            {{-- GPS Button --}}
            <div class="sm:col-span-4">
                <button type="button"
                        onclick="requestLocation()"
                        class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-sm notranslate" translate="no">my_location</span>
                    <span>Lacak Lokasi Saya</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content: Map & List --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Peta Leaflet --}}
        <div class="lg:col-span-7 xl:col-span-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative">
                <div id="map" wire:ignore style="height: 520px; width: 100%; z-index: 10;"></div>

                {{-- Loading Overlay --}}
                <div wire:loading wire:target="loadNearestOdps, search, radius, statusFilter, setLocation"
                     class="absolute inset-0 bg-slate-900/25 backdrop-blur-xs z-20 flex items-center justify-center">
                    <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-3 shadow-xl flex items-center gap-2.5 border border-slate-100 dark:border-slate-700">
                        <span class="material-symbols-outlined notranslate animate-spin text-indigo-600 text-xl" translate="no">autorenew</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Memuat titik ODP...</span>
                    </div>
                </div>

                {{-- Map Legend / Helper --}}
                <div class="absolute bottom-3 left-3 z-20 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xs p-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm text-[10px] space-y-1">
                    <div class="flex items-center gap-1.5 font-bold">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="text-slate-700 dark:text-slate-200">Port Tersedia</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-bold">
                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                        <span class="text-slate-700 dark:text-slate-200">Port Penuh (0 Kosong)</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-bold">
                        <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block"></span>
                        <span class="text-slate-700 dark:text-slate-200">Posisi Teknisi (Acuan)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Hasil Pencarian ODP --}}
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col h-[520px] bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-3.5 sm:p-4 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-slate-100">Hasil Pencarian</h3>
                    <p class="text-[11px] text-slate-400">
                        {{ !empty($search) ? 'Menampilkan ODP cocok "' . $search . '"' : ($latitude ? 'Berdasarkan radius terdekat' : 'Semua ODP aktif terdaftar') }}
                    </p>
                </div>
                <span class="bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-extrabold px-2.5 py-1 rounded-full border border-indigo-200 dark:border-indigo-800">
                    {{ count($odps) }} ODP
                </span>
            </div>

            <div class="flex-1 overflow-y-auto p-3.5 space-y-3">
                @forelse($odps as $odp)
                    @php
                        $isAvailable = $odp['available_port_count'] > 0;
                        $isSelected = ($selectedOdpId === $odp['id']);
                    @endphp
                    <div wire:key="odp-card-{{ $odp['id'] }}"
                         wire:click="selectOdp({{ $odp['id'] }})"
                         class="p-3 rounded-xl border transition-all cursor-pointer text-xs space-y-2 {{ $isSelected ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 shadow-xs' : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 hover:border-indigo-300 hover:bg-slate-50/50 dark:hover:bg-slate-750' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-black text-slate-900 dark:text-slate-100 text-xs sm:text-sm">{{ $odp['code'] }}</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isAvailable ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                                        {{ $isAvailable ? 'Ready' : 'Full' }}
                                    </span>
                                </div>
                                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">{{ $odp['name'] }}</p>
                            </div>

                            @if($odp['distance'] !== null)
                                <span class="inline-flex items-center gap-1 text-[11px] font-extrabold px-2 py-0.5 bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 rounded-lg shrink-0">
                                    <span class="material-symbols-outlined text-[13px] notranslate" translate="no">route</span>
                                    <span>{{ $odp['distance'] }} km</span>
                                </span>
                            @endif
                        </div>

                        {{-- Port Stats Pill --}}
                        <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                            <div class="bg-slate-50 dark:bg-slate-900/60 p-2 rounded-lg text-center border border-slate-100 dark:border-slate-700/50">
                                <span class="text-slate-400 block text-[10px]">Total Port</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $odp['port_count'] }} Port</span>
                            </div>
                            <div class="p-2 rounded-lg text-center border {{ $isAvailable ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50/70 border-rose-200 dark:bg-rose-950/40 dark:border-rose-800/60 text-rose-700 dark:text-rose-300' }}">
                                <span class="block text-[10px] opacity-80">Port Kosong</span>
                                <span class="font-extrabold">{{ $odp['available_port_count'] }} Port</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-700/60">
                            <span class="truncate max-w-[200px]" title="{{ $odp['address'] }}">
                                <span class="material-symbols-outlined text-[13px] align-middle notranslate" translate="no">pin_drop</span>
                                {{ $odp['address'] }}
                            </span>
                            @if($odp['latitude'] && $odp['longitude'])
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $odp['latitude'] }},{{ $odp['longitude'] }}"
                                   target="_blank"
                                   onclick="event.stopPropagation();"
                                   class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-[10px] shrink-0 flex items-center gap-0.5">
                                    <span>Rute GPS</span>
                                    <span class="material-symbols-outlined text-xs notranslate" translate="no">open_in_new</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400">
                        <span class="material-symbols-outlined text-4xl opacity-40 notranslate" translate="no">location_off</span>
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-2">Tidak ada ODP ditemukan</p>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                            Coba ubah kata kunci pencarian, perbesar radius, atau klik tombol "Lacak Lokasi Saya".
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- Modal Tambah Titik Jaringan Baru (ODP / Closure FO) --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg overflow-hidden animate-in fade-in duration-200">
            <div class="px-5 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">add_location_alt</span>
                    <h3 class="font-bold text-sm sm:text-base">Tambah Titik Lapangan Baru</h3>
                </div>
                <button type="button" wire:click="closeCreateModal" class="text-white/80 hover:text-white text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveNewNode" class="p-5 space-y-4">
                {{-- Pilihan Jenis Node --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Jenis Titik Jaringan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" wire:click="$set('newNodeType', 'odp')"
                                class="py-2.5 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 {{ $newNodeType === 'odp' ? 'bg-indigo-50 border-indigo-500 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <span>📦</span>
                            <span>ODP (Distribusi)</span>
                        </button>
                        <button type="button" wire:click="$set('newNodeType', 'closure')"
                                class="py-2.5 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 {{ $newNodeType === 'closure' ? 'bg-teal-50 border-teal-500 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <span>🧰</span>
                            <span>Closure FO (Joint)</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kode Titik</label>
                        <input type="text" wire:model="newNodeCode" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-mono uppercase" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Titik</label>
                        <input type="text" wire:model="newNodeName" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                {{ $newNodeType === 'closure' ? 'Kapasitas Core FO' : 'Kapasitas Port' }}
                            </label>
                        </div>
                        <input type="number" wire:model="newNodePortCount" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold" min="1" max="512">
                        @if($newNodeType === 'closure')
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach([4, 6, 8, 12, 24, 48, 96, 144] as $core)
                                <button type="button" wire:click="$set('newNodePortCount', {{ $core }})"
                                        class="px-1.5 py-0.5 text-[10px] rounded border font-semibold transition-colors {{ (int)$newNodePortCount === $core ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200' }}">
                                    {{ $core }}C
                                </button>
                            @endforeach
                        </div>
                        @else
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach([4, 8, 16] as $p)
                                <button type="button" wire:click="$set('newNodePortCount', {{ $p }})"
                                        class="px-1.5 py-0.5 text-[10px] rounded border font-semibold transition-colors {{ (int)$newNodePortCount === $p ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200' }}">
                                    {{ $p }}P
                                </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @if($newNodeType === 'closure')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Model Closure</label>
                        <select wire:model="newNodeClosureType" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                            <option value="Dome">Dome (Kubah Tiang)</option>
                            <option value="Inline">Inline (Horizontal)</option>
                            <option value="Wall Mount">Wall Mount (Dinding)</option>
                        </select>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Latitude</label>
                        <input type="text" wire:model="newNodeLat" class="w-full px-2.5 py-1.5 text-xs bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-lg font-mono text-slate-600 dark:text-slate-300" readonly>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Longitude</label>
                        <input type="text" wire:model="newNodeLng" class="w-full px-2.5 py-1.5 text-xs bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-lg font-mono text-slate-600 dark:text-slate-300" readonly>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat / Patokan Tiang</label>
                    <input type="text" wire:model="newNodeAddress" placeholder="Misal: Depan Pos Ronda RT 02 / Tiang No. 14" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                </div>

                {{-- Upload Foto Tiang / Box --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Foto Tiang / Box Lokasi</label>
                    <input type="file" wire:model="newNodePhoto" accept="image/*" capture="environment"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan kamera ponsel untuk mengambil foto tiang/box langsung di lokasi.</p>
                    @if ($newNodePhoto)
                        <div class="mt-2">
                            <img src="{{ $newNodePhoto->temporaryUrl() }}" class="h-24 w-auto rounded-lg object-cover border border-slate-200">
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
                    <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm flex items-center gap-1.5">
                        <span wire:loading.remove>Simpan Titik</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Tambah / Update / Crop Foto Titik yang Sudah Ada --}}
    @if($showUploadPhotoModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg overflow-hidden animate-in fade-in duration-200">
            <div class="px-5 py-4 bg-gradient-to-r from-sky-600 to-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">crop</span>
                    <h3 class="font-bold text-sm sm:text-base">Foto Tiang & Penyesuaian (Crop)</h3>
                </div>
                <button type="button" wire:click="closeUploadPhotoModal" class="text-white/80 hover:text-white text-lg">&times;</button>
            </div>

            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-500">Titik Target:</span>
                        <div class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $uploadTargetName }} ({{ strtoupper($uploadTargetType) }})</div>
                    </div>
                </div>

                {{-- Foto Saat Ini & Tombol Hapus --}}
                @if($existingTargetPhoto)
                <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ $existingTargetPhoto }}" class="w-16 h-12 object-cover rounded-lg border border-slate-300" onerror="this.onerror=null; this.src='/node-photo/{{ $uploadTargetType }}/{{ $uploadTargetId }}';">
                        <div>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-200">Foto Saat Ini</div>
                            <div class="text-[10px] text-slate-400">Klik hapus jika ingin mengosongkan</div>
                        </div>
                    </div>
                    <button type="button" wire:click="deleteCurrentPhoto" wire:confirm="Hapus foto tiang ini dari sistem?" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-bold transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">delete</span> Hapus Foto
                    </button>
                </div>
                @endif

                {{-- Pilih Foto Baru & Interactive Crop Area --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Pilih / Ambil Foto Baru</span>
                        <span class="text-[10px] text-sky-600 font-normal">Tersedia fitur geser, zoom & pangkas</span>
                    </label>
                    <input type="file" id="techPhotoFileInput" accept="image/*" capture="environment"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan kamera ponsel untuk mengambil foto tiang atau pilih dari galeri.</p>
                </div>

                {{-- Area Cropper.js --}}
                <div id="techCropperContainer" class="hidden space-y-2">
                    <div class="max-h-64 overflow-hidden rounded-xl bg-slate-900 border border-slate-300 dark:border-slate-700 flex items-center justify-center">
                        <img id="techCropperImage" class="max-w-full block">
                    </div>
                    <div class="flex items-center justify-between gap-1 p-2 bg-slate-100 dark:bg-slate-700/50 rounded-xl text-xs">
                        <span class="text-[10px] text-slate-500 font-medium">Atur Tampilan:</span>
                        <div class="flex items-center gap-1">
                            <button type="button" id="btnTechRotate" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold" title="Putar 90 derajat">
                                🔄 Putar
                            </button>
                            <button type="button" id="btnTechZoomIn" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold" title="Perbesar">
                                🔍+
                            </button>
                            <button type="button" id="btnTechZoomOut" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold" title="Perkecil">
                                🔍-
                            </button>
                            <button type="button" id="btnTechReset" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold" title="Reset">
                                🔁 Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
                    <button type="button" wire:click="closeUploadPhotoModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Batal</button>
                    <button type="button" id="btnTechSubmitPhoto" class="px-5 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <span id="btnTechSubmitText">Simpan Foto Tiang</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-ant-path@1.3.0/dist/leaflet-ant-path.min.js"></script>
    <style>
        .map-tiles-dark { filter: invert(100%) hue-rotate(180deg) brightness(85%) contrast(90%) !important; }
        .modern-map-popup .leaflet-popup-content-wrapper {
            background: #0f172a !important;
            padding: 0 !important;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5), 0 8px 10px -6px rgb(0 0 0 / 0.5) !important;
            border: 1px solid #1e293b !important;
            border-radius: 12px !important;
        }
        .modern-map-popup .leaflet-popup-content { margin: 0 !important; width: 280px !important; }
        .modern-map-popup .leaflet-popup-tip { background: #0f172a !important; }
        .modern-map-popup .leaflet-popup-close-button { color: #94a3b8 !important; padding: 8px 8px 0 0 !important; font-size: 16px !important; }
        .modern-map-popup .leaflet-popup-close-button:hover { color: #ffffff !important; }

        .network-card {
            width: 100%; padding: 0; color: #cbd5e1; font-family: "Inter", sans-serif; font-size: 12px; line-height: 1.4;
            background: #0f172a; border-radius: 12px; overflow: hidden;
        }
        .network-header {
            display: flex; align-items: center; justify-content: space-between; padding: 10px 12px;
            border-bottom: 1px solid #1e293b; background: rgba(30, 41, 59, 0.5);
        }
        .network-title { font-size: 13px; font-weight: 700; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px; }
        .network-subtitle { font-size: 10px; color: #94a3b8; font-family: monospace; }
        .network-badge { font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 999px; text-transform: uppercase; }
        .badge-olt { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: #fff; border: 1px solid #60a5fa; }
        .badge-odc { background: linear-gradient(135deg, #78350f, #f59e0b); color: #fff; border: 1px solid #fbbf24; }
        .badge-odp { background: linear-gradient(135deg, #064e3b, #10b981); color: #fff; border: 1px solid #34d399; }
        .badge-closure { background: #334155; color: #cbd5e1; border: 1px solid #64748b; }
        .network-content { padding: 10px 12px; display: flex; flex-direction: column; gap: 8px; }
        .node-panel {
            background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(71, 85, 105, 0.4); border-radius: 8px;
            padding: 7px 9px; display: flex; flex-direction: column; gap: 4px;
        }
        .node-kv { display: flex; justify-content: space-between; align-items: center; gap: 6px; font-size: 11px; }
        .node-kv-label { color: #94a3b8; font-weight: 500; }
        .node-kv-value { color: #f1f5f9; font-weight: 600; text-align: right; }
        .node-progress-bg { width: 100%; height: 5px; background: #1e293b; border-radius: 999px; overflow: hidden; margin-top: 2px; }
        .node-progress-bar { height: 100%; border-radius: 999px; }
        .node-actions { display: flex; flex-direction: column; gap: 5px; margin-top: 4px; }
        .node-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 4px;
            padding: 5px 8px; font-size: 10px; font-weight: 700; border-radius: 6px; text-decoration: none !important; cursor: pointer; border: none;
        }

        .custom-icon { display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.5); cursor: pointer; }
        .custom-icon.icon-olt { background: radial-gradient(circle, #3b82f6 0%, #1e3a8a 100%); border: 2px solid #93c5fd; border-radius: 50%; box-shadow: 0 0 12px #3b82f6; }
        .custom-icon.icon-odc { background: linear-gradient(135deg, #f59e0b 0%, #92400e 100%); border: 2px solid #fde68a; border-radius: 8px; transform: rotate(45deg); box-shadow: 0 0 12px #f59e0b; }
        .custom-icon.icon-odc i { transform: rotate(-45deg); }
        .custom-icon.icon-odp { background: linear-gradient(to bottom right, #10b981, #065f46); border: 2px solid #a7f3d0; border-radius: 4px; box-shadow: 0 0 10px #10b981; }
        .custom-icon.icon-closure { background: #334155; border: 2px solid #94a3b8; border-radius: 10px 10px 3px 3px; }
    </style>

    <script>
        let map = null;
        let userMarker = null;
        let odpMarkers = {};
        let odpLayerGroup = null;
        let backboneLayerGroup = null;
        let techCropper = null;

        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'techPhotoFileInput') {
                const file = e.target.files[0];
                if (!file) return;

                const container = document.getElementById('techCropperContainer');
                const image = document.getElementById('techCropperImage');
                if (container) container.classList.remove('hidden');

                const reader = new FileReader();
                reader.onload = function(evt) {
                    if (techCropper) {
                        techCropper.destroy();
                        techCropper = null;
                    }
                    image.src = evt.target.result;
                    if (typeof Cropper !== 'undefined') {
                        techCropper = new Cropper(image, {
                            aspectRatio: 16 / 10,
                            viewMode: 1,
                            autoCropArea: 0.9,
                            responsive: true,
                            restore: false,
                        });
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target) return;
            if (e.target.id === 'btnTechRotate' || e.target.closest('#btnTechRotate')) {
                if (techCropper) techCropper.rotate(90);
            }
            if (e.target.id === 'btnTechZoomIn' || e.target.closest('#btnTechZoomIn')) {
                if (techCropper) techCropper.zoom(0.1);
            }
            if (e.target.id === 'btnTechZoomOut' || e.target.closest('#btnTechZoomOut')) {
                if (techCropper) techCropper.zoom(-0.1);
            }
            if (e.target.id === 'btnTechReset' || e.target.closest('#btnTechReset')) {
                if (techCropper) techCropper.reset();
            }
            if (e.target.id === 'btnTechSubmitPhoto' || e.target.closest('#btnTechSubmitPhoto')) {
                const btn = document.getElementById('btnTechSubmitPhoto');
                const btnText = document.getElementById('btnTechSubmitText');
                if (btn) btn.disabled = true;
                if (btnText) btnText.innerText = 'Menyimpan...';

                if (techCropper) {
                    const canvas = techCropper.getCroppedCanvas({
                        maxWidth: 1200,
                        maxHeight: 800,
                        fillColor: '#fff',
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });
                    const base64Data = canvas.toDataURL('image/jpeg', 0.82);
                    @this.set('croppedPhotoData', base64Data).then(() => {
                        @this.saveAdditionalPhoto();
                    });
                } else {
                    const fileInput = document.getElementById('techPhotoFileInput');
                    if (fileInput && fileInput.files && fileInput.files[0]) {
                        @this.upload('additionalPhoto', fileInput.files[0], () => {
                            @this.saveAdditionalPhoto();
                        });
                    } else {
                        alert('Silakan pilih foto terlebih dahulu.');
                        if (btn) btn.disabled = false;
                        if (btnText) btnText.innerText = 'Simpan Foto Tiang';
                    }
                }
            }
        });

        document.addEventListener('livewire:initialized', () => {
            initMap();

            // Listen for ODP updates from backend
            Livewire.on('odps-updated', (event) => {
                const payload = event[0] || event || {};
                const odps = payload.odps || [];
                const center = payload.center || null;
                const hasGps = payload.hasGps || false;
                renderOdpsOnMap(odps, center, hasGps);
            });

            // Listen for single ODP selection/focus
            Livewire.on('focus-odp', (event) => {
                const payload = event[0] || event || {};
                if (payload.lat && payload.lng) {
                    focusOdp(payload.lat, payload.lng, payload.id);
                }
            });
        });

        function initMap() {
            if (map !== null) {
                map.remove();
            }

            const initialLat = {{ $latitude ?? -6.200000 }};
            const initialLng = {{ $longitude ?? 106.816666 }};

            map = L.map('map').setView([initialLat, initialLng], 14);

            // 2 Lapisan Layar Jernih Seragam
            const googleHybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                attribution: '&copy; Google Hybrid Satellite HD',
                maxZoom: 22,
                subdomains: ['mt0','mt1','mt2','mt3']
            });

            const googleRoad = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '&copy; Google Road Clean HD',
                maxZoom: 22,
                subdomains: ['mt0','mt1','mt2','mt3']
            });

            const darkLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '&copy; dsBilling Night Mode (Google)',
                maxZoom: 22,
                subdomains: ['mt0','mt1','mt2','mt3'],
                className: 'map-tiles-dark'
            });

            // Default to Google Hybrid
            googleHybrid.addTo(map);

            L.control.layers({
                '🛰️ Google Hybrid Satelit HD': googleHybrid,
                '🗺️ Google Road Clean HD': googleRoad,
                '🌙 Dark Mode': darkLayer
            }).addTo(map);

            backboneLayerGroup = L.layerGroup().addTo(map);
            odpLayerGroup = L.layerGroup().addTo(map);

            // Render Backbone Nodes (OLT, ODC, Closures)
            renderBackboneNodes();

            // Render current initial ODPs
            const initialOdps = @json($odps);
            renderOdpsOnMap(initialOdps, { lat: initialLat, lng: initialLng }, false);

            // Map click listener: Set acuan & tawarkan tambah titik baru
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                updateUserMarker(lat, lng);
                @this.setLocation(lat, lng);
            });

            setTimeout(() => { if (map) map.invalidateSize(); }, 250);
            setTimeout(() => { if (map) map.invalidateSize(); }, 800);
            window.addEventListener('resize', () => { if (map) map.invalidateSize(); });
        }

        function renderBackboneNodes() {
            if (!backboneLayerGroup) return;
            backboneLayerGroup.clearLayers();

            const allNodes = @json($allNodes);
            const olts = allNodes.olts || [];
            const odcs = allNodes.odcs || [];
            const closures = allNodes.closures || [];
            const popupOpt = { maxWidth: 320, minWidth: 280, className: 'modern-map-popup' };

            function formatSafePhotoUrl(url) {
                if (!url || typeof url !== 'string') return '';
                if (url.startsWith('data:')) return url;
                // Normalize absolute URL containing /storage/ to relative /storage/ to avoid HTTP Mixed Content on HTTPS
                url = url.replace(/^https?:\/\/[^\/]+(\/storage\/.*)$/, '$1');
                if (url.startsWith('http://') || url.startsWith('https://')) return url;
                if (url.startsWith('/storage/')) return url;
                if (url.startsWith('storage/')) return '/' + url;
                if (url.startsWith('/')) return url;
                return '/storage/' + url;
            }

            // 1. Render OLT
            olts.forEach(o => {
                if (!o.latitude || !o.longitude) return;
                const icon = L.divIcon({
                    html: '<i class="fa fa-server" style="font-size:20px;"></i>',
                    className: 'custom-icon icon-olt',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                    popupAnchor: [0, -18]
                });
                const firstPhoto = (o.photos && o.photos.length > 0) ? formatSafePhotoUrl(o.photos[0]) : '';
                const photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onerror="this.onerror=null; this.src='/node-photo/olt/${o.id}';" onclick="window.open('/node-photo/olt/${o.id}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : `<div style="margin-bottom:6px;"><img src="/node-photo/olt/${o.id}" style="width:100%;height:65px;object-fit:cover;border-radius:8px;border:1px dashed #334155;"></div>`;
                const gmapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + o.latitude + ',' + o.longitude;

                const popupDiv = document.createElement('div');
                popupDiv.innerHTML = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-server text-blue-400" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${o.name}">${o.name}</div>
                                    <div class="network-subtitle">${o.code || 'OLT-CORE'}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-olt">OLT</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">IP Host:</span>
                                    <span class="node-kv-value font-mono text-cyan-400">${o.ip_address || '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Port PON:</span>
                                    <span class="node-kv-value text-blue-300 font-bold">${o.pon_port_count || '-'} Port</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Model:</span>
                                    <span class="node-kv-value">${o.model || '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${o.address || '-'}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <a href="${gmapsUrl}" target="_blank" class="node-btn" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155; width:100%;">
                                    ➔ Buka Rute Google Maps
                                </a>
                            </div>
                        </div>
                    </div>`;
                L.marker([o.latitude, o.longitude], { icon, interactive: true }).addTo(backboneLayerGroup).bindPopup(popupDiv, popupOpt);
            });

            // 2. Render ODC
            odcs.forEach(c => {
                if (!c.latitude || !c.longitude) return;
                const icon = L.divIcon({
                    html: '<i class="fa fa-hdd" style="font-size:18px;"></i>',
                    className: 'custom-icon icon-odc',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                    popupAnchor: [0, -16]
                });
                const firstPhoto = (c.photos && c.photos.length > 0) ? formatSafePhotoUrl(c.photos[0]) : '';
                const photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onerror="this.onerror=null; this.src='/node-photo/odc/${c.id}';" onclick="window.open('/node-photo/odc/${c.id}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : `<div style="margin-bottom:6px;"><img src="/node-photo/odc/${c.id}" style="width:100%;height:65px;object-fit:cover;border-radius:8px;border:1px dashed #334155;"></div>`;
                const gmapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + c.latitude + ',' + c.longitude;

                const popupDiv = document.createElement('div');
                popupDiv.innerHTML = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-hdd text-amber-400" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${c.name}">${c.name}</div>
                                    <div class="network-subtitle">${c.code || 'ODC-NODE'}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-odc">ODC</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Uplink OLT:</span>
                                    <span class="node-kv-value text-sky-400 font-bold">${c.olt_name || 'N/A'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Port PON:</span>
                                    <span class="node-kv-value font-mono text-emerald-400">${c.pon_port || '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Kapasitas:</span>
                                    <span class="node-kv-value text-amber-300 font-bold">${c.capacity || 48} Port</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${c.address || '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Jarak ke OLT:</span>
                                    <span class="node-kv-value text-cyan-400 font-mono">${c.olt_distance ? c.olt_distance + ' km' : '-'}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <button type="button" class="node-btn btn-upload-photo" style="background:#d97706; color:white;">
                                    📸 Tambah / Ganti Foto Tiang
                                </button>
                                <a href="${gmapsUrl}" target="_blank" class="node-btn" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155;">
                                    ➔ Rute Maps
                                </a>
                            </div>
                        </div>
                    </div>`;

                const btnPhoto = popupDiv.querySelector('.btn-upload-photo');
                if (btnPhoto) {
                    btnPhoto.onclick = function() { openUploadPhotoModalFromMap('odc', c.id, c.name); };
                }

                L.marker([c.latitude, c.longitude], { icon, interactive: true }).addTo(backboneLayerGroup).bindPopup(popupDiv, popupOpt);
            });

            // 3. Render Closures (Joint Closure FO)
            closures.forEach(jc => {
                if (!jc.latitude || !jc.longitude) return;
                const icon = L.divIcon({
                    html: '<i class="fa fa-archive" style="font-size:15px;"></i>',
                    className: 'custom-icon icon-closure',
                    iconSize: [26, 26],
                    iconAnchor: [13, 13],
                    popupAnchor: [0, -13]
                });
                const firstPhoto = (jc.photos && jc.photos.length > 0) ? formatSafePhotoUrl(jc.photos[0]) : '';
                const photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onerror="this.onerror=null; this.src='/node-photo/closure/${jc.id}';" onclick="window.open('/node-photo/closure/${jc.id}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : `<div style="margin-bottom:6px;"><img src="/node-photo/closure/${jc.id}" style="width:100%;height:65px;object-fit:cover;border-radius:8px;border:1px dashed #334155;"></div>`;
                const gmapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + jc.latitude + ',' + jc.longitude;

                const popupDiv = document.createElement('div');
                popupDiv.innerHTML = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-archive text-slate-300" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${jc.name}">${jc.name}</div>
                                    <div class="network-subtitle">${jc.code || 'CLOSURE'}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-closure">CLOSURE</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Tipe:</span>
                                    <span class="node-kv-value">${jc.type || 'Dome'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Kapasitas Core:</span>
                                    <span class="node-kv-value text-teal-300 font-bold">${jc.port_count || 24} Core</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Uplink ODC:</span>
                                    <span class="node-kv-value text-amber-300">${jc.odc_name || 'N/A'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${jc.address || '-'}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <button type="button" class="node-btn btn-upload-photo" style="background:#0d9488; color:white; width:100%;">
                                    📸 Tambah / Ganti Foto Tiang
                                </button>
                            </div>
                        </div>
                    </div>`;

                const btnPhoto = popupDiv.querySelector('.btn-upload-photo');
                if (btnPhoto) {
                    btnPhoto.onclick = function() { openUploadPhotoModalFromMap('closure', jc.id, jc.name); };
                }

                L.marker([jc.latitude, jc.longitude], { icon, interactive: true }).addTo(backboneLayerGroup).bindPopup(popupDiv, popupOpt);
            });

            // Draw OLT -> ODC AntPath Lines
            odcs.forEach(c => {
                if (!c.latitude || !c.longitude || !c.olt_id) return;
                const olt = olts.find(o => o.id == c.olt_id);
                if (olt && olt.latitude && olt.longitude) {
                    const path = [[olt.latitude, olt.longitude], [c.latitude, c.longitude]];
                    if (L.polyline && L.polyline.antPath) {
                        L.polyline.antPath(path, {
                            color: '#6f42c1',
                            weight: 5,
                            opacity: 0.9,
                            pulseColor: '#ffffff',
                            delay: 400
                        }).addTo(backboneLayerGroup);
                    } else {
                        L.polyline(path, { color: '#6f42c1', weight: 5, opacity: 0.9 }).addTo(backboneLayerGroup);
                    }
                }
            });

            // Draw Closure Uplink AntPath Lines (OLT -> Closure, Parent Closure -> Closure, or ODC -> Closure)
            closures.forEach(jc => {
                if (!jc.latitude || !jc.longitude) return;
                let uplinkNode = null;
                let lineColor = '#0d9488';

                if (jc.parent_closure_id) {
                    uplinkNode = closures.find(c => c.id == jc.parent_closure_id);
                    lineColor = '#14b8a6';
                } else if (jc.odc_id) {
                    uplinkNode = odcs.find(c => c.id == jc.odc_id);
                    lineColor = '#0d9488';
                } else if (jc.olt_id) {
                    uplinkNode = olts.find(o => o.id == jc.olt_id);
                    lineColor = '#0284c7';
                }

                if (uplinkNode && uplinkNode.latitude && uplinkNode.longitude) {
                    const path = [[uplinkNode.latitude, uplinkNode.longitude], [jc.latitude, jc.longitude]];
                    if (L.polyline && L.polyline.antPath) {
                        L.polyline.antPath(path, {
                            color: lineColor,
                            weight: 3.5,
                            opacity: 0.85,
                            dashArray: [4, 8],
                            pulseColor: '#ffffff',
                            delay: 400
                        }).addTo(backboneLayerGroup);
                    } else {
                        L.polyline(path, { color: lineColor, weight: 3.5, opacity: 0.85 }).addTo(backboneLayerGroup);
                    }
                }
            });
        }

        function updateUserMarker(lat, lng) {
            if (userMarker) {
                map.removeLayer(userMarker);
            }

            const pinIcon = L.divIcon({
                className: 'custom-user-pin',
                html: '<div style="background-color:#4f46e5; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow:0 0 10px rgba(0,0,0,0.5);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            userMarker = L.marker([lat, lng], { icon: pinIcon }).addTo(map);
            const popupContent = `
                <div style="font-size:11px; font-weight:bold; min-width:150px;">
                    <div>📍 Titik Acuan GPS</div>
                    <div style="font-size:10px; color:#64748b; margin-top:2px;">${lat.toFixed(5)}, ${lng.toFixed(5)}</div>
                    <button type="button" onclick="openCreateModalFromMap(${lat}, ${lng})"
                            style="margin-top:6px; background:#10b981; color:white; border:none; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:bold; cursor:pointer; width:100%;">
                        + Tambah Node di Sini
                    </button>
                </div>
            `;
            userMarker.bindPopup(popupContent).openPopup();
        }

        function openCreateModalFromMap(lat, lng) {
            @this.openCreateModal(lat, lng);
        }

        function openUploadPhotoModalFromMap(type, id, name) {
            @this.openUploadPhotoModal(type, id, name);
        }

        function renderOdpsOnMap(odps, center, hasGps) {
            if (!odpLayerGroup) return;

            odpLayerGroup.clearLayers();
            odpMarkers = {};

            if (hasGps && center && center.lat && center.lng) {
                updateUserMarker(center.lat, center.lng);
            }

            const boundsGroup = [];
            if (userMarker) {
                boundsGroup.push(userMarker);
            }

            odps.forEach(odp => {
                if (!odp.latitude || !odp.longitude) return;

                const totalP = parseInt(odp.port_count) || 8;
                const availP = parseInt(odp.available_port_count) || 0;
                const usedP = Math.max(0, totalP - availP);
                const pct = Math.min(100, Math.round((usedP / totalP) * 100));
                const barColor = pct > 90 ? '#ef4444' : (pct > 70 ? '#f59e0b' : '#10b981');
                const lossDb = totalP == 4 ? 7.2 : (totalP == 8 ? 10.5 : (totalP == 16 ? 13.5 : (totalP == 32 ? 17.0 : 0)));

                const odpIcon = L.divIcon({
                    className: 'custom-icon icon-odp',
                    html: '<span style="font-weight:900; font-size:11px; color:white;">' + availP + '</span>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    popupAnchor: [0, -14]
                });

                const marker = L.marker([odp.latitude, odp.longitude], { icon: odpIcon, interactive: true }).addTo(odpLayerGroup);
                odpMarkers[odp.id] = marker;
                boundsGroup.push(marker);

                // Connect Closure -> ODP or ODC -> ODP line
                const allNodesData = (typeof allNodes !== 'undefined') ? allNodes : (@json($allNodes) || {});
                let uplinkNode = null;
                let lineColor = '#fd7e14';

                if (odp.joint_closure_id) {
                    uplinkNode = ((allNodesData.closures || [])).find(c => c.id == odp.joint_closure_id);
                    lineColor = '#10b981';
                }
                if (!uplinkNode && odp.odc_id) {
                    uplinkNode = ((allNodesData.odcs || [])).find(c => c.id == odp.odc_id);
                    lineColor = '#fd7e14';
                }

                if (uplinkNode && uplinkNode.latitude && uplinkNode.longitude) {
                    const path = [[uplinkNode.latitude, uplinkNode.longitude], [odp.latitude, odp.longitude]];
                    if (L.polyline && L.polyline.antPath) {
                        L.polyline.antPath(path, {
                            color: lineColor,
                            weight: 4,
                            opacity: 0.8,
                            pulseColor: '#ffffff',
                            delay: 400
                        }).addTo(odpLayerGroup);
                    } else {
                        L.polyline(path, { color: lineColor, weight: 4, opacity: 0.8 }).addTo(odpLayerGroup);
                    }
                }

                const distanceText = odp.distance !== null ? (odp.distance + ' km') : '-';
                const gmapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + odp.latitude + ',' + odp.longitude;
                const firstPhoto = (odp.photos && odp.photos.length > 0) ? formatSafePhotoUrl(odp.photos[0]) : '';
                const photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onerror="this.onerror=null; this.src='/node-photo/odp/${odp.id}';" onclick="window.open('/node-photo/odp/${odp.id}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : `<div style="margin-bottom:6px;"><img src="/node-photo/odp/${odp.id}" style="width:100%;height:65px;object-fit:cover;border-radius:8px;border:1px dashed #334155;"></div>`;

                const popupDiv = document.createElement('div');
                popupDiv.innerHTML = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-box text-emerald-400" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${odp.name}">${odp.name}</div>
                                    <div class="network-subtitle">${odp.code || 'ODP-NODE'}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-odp">ODP</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Splitter Ratio:</span>
                                    <span class="node-kv-value font-mono text-sky-400 font-bold">1:${totalP} <span class="text-xs text-slate-400 font-normal">(-${lossDb} dB)</span></span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Port Terpakai:</span>
                                    <span class="node-kv-value"><b>${usedP}</b> / ${totalP} (${pct}%)</span>
                                </div>
                                <div class="node-progress-bg">
                                    <div class="node-progress-bar" style="width: ${pct}%; background: ${barColor};"></div>
                                </div>
                                <div class="node-kv" style="margin-top:2px;">
                                    <span class="node-kv-label">Port Tersedia:</span>
                                    <span class="node-kv-value text-emerald-400 font-bold">${availP} Port Siap Pakai</span>
                                </div>
                            </div>
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Uplink ODC:</span>
                                    <span class="node-kv-value text-amber-300 font-bold">${odp.odc_name || 'N/A'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${odp.address || '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Jarak ke ODC:</span>
                                    <span class="node-kv-value text-cyan-400 font-mono">${odp.odc_distance ? odp.odc_distance + ' km' : '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Posisi Teknisi:</span>
                                    <span class="node-kv-value font-mono text-cyan-300 font-bold">${distanceText}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <button type="button" class="node-btn btn-upload-photo" style="background:#0284c7; color:white;">
                                    📸 Tambah / Ganti Foto Tiang
                                </button>
                                <a href="${gmapsUrl}" target="_blank" class="node-btn" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155;">
                                    ➔ Rute Maps
                                </a>
                            </div>
                        </div>
                    </div>`;

                const btnPhoto = popupDiv.querySelector('.btn-upload-photo');
                if (btnPhoto) {
                    btnPhoto.onclick = function() { openUploadPhotoModalFromMap('odp', odp.id, odp.name); };
                }

                marker.bindPopup(popupDiv, { maxWidth: 320, minWidth: 280, className: 'modern-map-popup' });

                marker.on('click', function() {
                    @this.selectOdp(odp.id);
                });
            });

            // Auto-adjust view bounds if markers exist
            if (boundsGroup.length > 0) {
                const group = new L.featureGroup(boundsGroup);
                map.fitBounds(group.getBounds().pad(0.15), { maxZoom: 16 });
            } else if (center && center.lat && center.lng) {
                map.setView([center.lat, center.lng], 14);
            }
        }

        function focusOdp(lat, lng, id) {
            if (!map) return;
            map.setView([lat, lng], 17);
            if (odpMarkers[id]) {
                odpMarkers[id].openPopup();
            }
        }

        function requestLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    updateUserMarker(lat, lng);
                    map.setView([lat, lng], 15);
                    @this.setLocation(lat, lng);
                }, function(error) {
                    alert("Gagal mendapatkan lokasi. Pastikan izin lokasi (GPS) diaktifkan di browser Anda.");
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            } else {
                alert("Browser Anda tidak mendukung geolokasi GPS.");
            }
        }
    </script>
</div>
