<div class="space-y-4 pb-6">
    @section('header_title', 'Peta & Cari ODP')

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
                <div id="map" style="height: 520px; width: 100%; z-index: 10;"></div>

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
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kapasitas Port / Core</label>
                        <input type="number" wire:model="newNodePortCount" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold" min="1" max="512">
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

    {{-- Modal Tambah / Update Foto Titik yang Sudah Ada --}}
    @if($showUploadPhotoModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md overflow-hidden animate-in fade-in duration-200">
            <div class="px-5 py-4 bg-gradient-to-r from-sky-600 to-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">add_a_photo</span>
                    <h3 class="font-bold text-sm sm:text-base">Unggah Foto Dokumentasi</h3>
                </div>
                <button type="button" wire:click="closeUploadPhotoModal" class="text-white/80 hover:text-white text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveAdditionalPhoto" class="p-5 space-y-4">
                <div>
                    <span class="text-xs text-slate-500">Titik Target:</span>
                    <div class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $uploadTargetName }} ({{ strtoupper($uploadTargetType) }})</div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Ambil / Pilih Foto Fisik</label>
                    <input type="file" wire:model="additionalPhoto" accept="image/*" capture="environment" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan kamera ponsel untuk mengambil foto tiang/box langsung di lokasi.</p>

                    @if ($additionalPhoto)
                        <div class="mt-3">
                            <img src="{{ $additionalPhoto->temporaryUrl() }}" class="h-32 w-full rounded-xl object-cover border border-slate-200 shadow-sm">
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
                    <button type="button" wire:click="closeUploadPhotoModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm flex items-center gap-1.5">
                        <span wire:loading.remove>Unggah Foto</span>
                        <span wire:loading>Mengunggah...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        let map = null;
        let userMarker = null;
        let odpMarkers = {};
        let odpLayerGroup = null;
        let backboneLayerGroup = null;

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

            const darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                maxZoom: 20
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
        }

        function renderBackboneNodes() {
            if (!backboneLayerGroup) return;
            backboneLayerGroup.clearLayers();

            const allNodes = @json($allNodes);
            const olts = allNodes.olts || [];
            const odcs = allNodes.odcs || [];
            const closures = allNodes.closures || [];

            // 1. Render OLT
            olts.forEach(o => {
                if (!o.latitude || !o.longitude) return;
                const icon = L.divIcon({
                    className: 'custom-olt-pin',
                    html: '<div style="background-color:#2563eb; width:28px; height:28px; border-radius:8px; border:2px solid white; box-shadow:0 2px 6px rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:white; font-size:13px;">🏢</div>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                const photo = (o.photos && o.photos.length > 0) ? `<img src="${o.photos[0]}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-top:6px;">` : '';
                const popup = `<div style="font-family:inherit;min-width:180px;"><b style="color:#2563eb;">OLT Core: ${o.name}</b><br><span style="font-size:11px;color:#64748b;">Kode: ${o.code}</span>${photo}</div>`;
                L.marker([o.latitude, o.longitude], { icon }).addTo(backboneLayerGroup).bindPopup(popup);
            });

            // 2. Render ODC
            odcs.forEach(c => {
                if (!c.latitude || !c.longitude) return;
                const icon = L.divIcon({
                    className: 'custom-odc-pin',
                    html: '<div style="background-color:#d97706; width:26px; height:26px; border-radius:8px; border:2px solid white; box-shadow:0 2px 6px rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:white; font-size:12px;">🗄️</div>',
                    iconSize: [26, 26],
                    iconAnchor: [13, 13]
                });
                const photo = (c.photos && c.photos.length > 0) ? `<img src="${c.photos[0]}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-top:6px;">` : '';
                const popup = `<div style="font-family:inherit;min-width:180px;"><b style="color:#d97706;">ODC: ${c.name}</b><br><span style="font-size:11px;color:#64748b;">Kode: ${c.code}</span>${photo}</div>`;
                L.marker([c.latitude, c.longitude], { icon }).addTo(backboneLayerGroup).bindPopup(popup);
            });

            // 3. Render Closures (Joint Closure FO)
            closures.forEach(jc => {
                if (!jc.latitude || !jc.longitude) return;
                const icon = L.divIcon({
                    className: 'custom-closure-pin',
                    html: '<div style="background-color:#0d9488; width:24px; height:24px; border-radius:8px; border:2px solid white; box-shadow:0 2px 6px rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:white; font-size:11px;">🧰</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                const photo = (jc.photos && jc.photos.length > 0) ? `<img src="${jc.photos[0]}" style="width:100%;height:85px;object-fit:cover;border-radius:6px;margin-top:6px;border:1px solid #334155;">` : '';
                const popup = `<div style="font-family:inherit;min-width:180px;"><b style="color:#0d9488;">Closure FO: ${jc.name}</b><br><span style="font-size:11px;color:#64748b;">Tipe: ${jc.type || 'Dome'} (${jc.code})</span>${photo}<button type="button" onclick="openUploadPhotoModalFromMap('closure', ${jc.id}, '${jc.name}')" style="margin-top:6px; background:#0d9488; color:white; border:none; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:bold; cursor:pointer; width:100%;">📸 Tambah / Ganti Foto</button></div>`;
                L.marker([jc.latitude, jc.longitude], { icon }).addTo(backboneLayerGroup).bindPopup(popup);
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

                const isAvailable = (odp.available_port_count > 0);
                const color = isAvailable ? '#10b981' : '#ef4444';

                const odpIcon = L.divIcon({
                    className: 'custom-odp-pin',
                    html: '<div style="background-color:' + color + '; width:26px; height:26px; border-radius:8px; border:2px solid white; box-shadow:0 2px 6px rgba(0,0,0,0.35); display:flex; align-items:center; justify-content:center; color:white; font-size:11px; font-weight:900;">' + (odp.available_port_count) + '</div>',
                    iconSize: [26, 26],
                    iconAnchor: [13, 13]
                });

                const marker = L.marker([odp.latitude, odp.longitude], { icon: odpIcon }).addTo(odpLayerGroup);
                odpMarkers[odp.id] = marker;
                boundsGroup.push(marker);

                const distanceText = odp.distance !== null ? ('<br>Jarak: <b>' + odp.distance + ' km</b>') : '';
                const gmapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + odp.latitude + ',' + odp.longitude;
                const photo = (odp.photos && odp.photos.length > 0) ? `<img src="${odp.photos[0]}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-top:6px;">` : '';

                const popupHtml = '<div style="font-family:inherit; min-width:180px; padding:2px;">' +
                    '<div style="font-weight:900; font-size:13px; color:#1e293b;">' + odp.code + '</div>' +
                    '<div style="font-size:11px; color:#64748b; margin-top:2px;">' + odp.name + '</div>' +
                    '<div style="margin-top:6px; padding-top:6px; border-top:1px solid #e2e8f0; font-size:11px; line-height:1.5;">' +
                        'Port Kosong: <strong style="color:' + color + ';">' + odp.available_port_count + '</strong> / ' + odp.port_count + ' Total' +
                        distanceText +
                    '</div>' +
                    photo +
                    '<div style="margin-top:8px; display:flex; flex-direction:column; gap:4px;">' +
                        '<button type="button" onclick="openUploadPhotoModalFromMap(\'odp\', ' + odp.id + ', \'' + odp.name + '\')" style="background:#0284c7; color:white; border:none; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:bold; cursor:pointer; width:100%;">📸 Tambah / Ganti Foto</button>' +
                        '<a href="' + gmapsUrl + '" target="_blank" style="display:inline-block; font-size:10px; font-weight:bold; color:#4f46e5; text-decoration:none;">➔ Buka Rute Google Maps</a>' +
                    '</div>' +
                '</div>';

                marker.bindPopup(popupHtml);

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
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            } else {
                alert("Browser Anda tidak mendukung geolokasi GPS.");
            }
        }
    </script>
</div>
