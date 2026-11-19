@props([
    'latModel' => 'latitude',
    'lngModel' => 'longitude',
    'labelClass' => 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1',
    'gridClass' => 'grid grid-cols-1 md:grid-cols-2 gap-6',
])

<div x-data="coordinatePicker({
    lat: @entangle($latModel),
    lng: @entangle($lngModel)
})" class="w-full">
    {{-- Header with action buttons --}}
    <div class="flex items-center justify-between gap-2 mb-2">
        <label class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">
            <span class="material-symbols-outlined text-indigo-500 text-[18px]">pin_drop</span>
            <span>Titik Koordinat Lokasi</span>
        </label>
        <div class="flex items-center gap-1.5">
            <button type="button" 
                    @click="useCurrentLocation()" 
                    :disabled="isLoadingGps"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors cursor-pointer shadow-2xs"
                    title="Deteksi Titik Koordinat Menggunakan GPS HP / Browser">
                <template x-if="!isLoadingGps">
                    <span class="material-symbols-outlined notranslate text-[15px]">my_location</span>
                </template>
                <template x-if="isLoadingGps">
                    <svg class="animate-spin h-3.5 w-3.5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="isLoadingGps ? 'Mencari...' : 'GPS Saya'"></span>
            </button>
            <a href="{{ url('/network/map') }}" 
               target="_blank" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition-colors shadow-2xs"
               title="Buka Peta Topologi Jaringan di Tab Baru">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <span>Peta Jaringan</span>
            </a>
            <button type="button" 
                    @click="openMapModal()" 
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition-colors cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Pilih di Peta</span>
            </button>
        </div>
    </div>

    <div class="{{ $gridClass }}">
        {{-- Latitude Input --}}
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                Latitude
            </label>
            <div class="relative flex rounded-lg shadow-2xs">
                <input type="text" 
                       x-model="lat" 
                       placeholder="e.g : -0.9345797" 
                       class="block w-full px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-l-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                
                {{-- Copy Latitude Button --}}
                <button type="button" 
                        @click="copyCoordinate(lat, 'Latitude')" 
                        title="Salin Latitude"
                        class="inline-flex items-center px-3 border-y border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
                
                {{-- Paste / Auto-split Button --}}
                <button type="button" 
                        @click="pasteCoordinates('lat')" 
                        title="Tempel dari Clipboard (Mendukung format 'lat, lng')"
                        class="inline-flex items-center px-3 border-y border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-r-lg text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </button>
            </div>
            @error($latModel) <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Longitude Input --}}
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                Longitude
            </label>
            <div class="relative flex rounded-lg shadow-2xs">
                <input type="text" 
                       x-model="lng" 
                       placeholder="e.g : 100.226458" 
                       class="block w-full px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-l-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                
                {{-- Copy Longitude Button --}}
                <button type="button" 
                        @click="copyCoordinate(lng, 'Longitude')" 
                        title="Salin Longitude"
                        class="inline-flex items-center px-3 border-y border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
                
                {{-- Paste Button --}}
                <button type="button" 
                        @click="pasteCoordinates('lng')" 
                        title="Tempel dari Clipboard (Mendukung format 'lat, lng')"
                        class="inline-flex items-center px-3 border-y border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-r-lg text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </button>
            </div>
            @error($lngModel) <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Link ke Google Maps & Peta Topologi jika koordinat sudah terisi --}}
    <template x-if="lat && lng">
        <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-mono">
                Koordinat Terpasang: <strong class="text-slate-700 dark:text-slate-200" x-text="lat + ', ' + lng"></strong>
            </span>
            <div class="flex items-center gap-3">
                <a href="{{ url('/network/map') }}" 
                   target="_blank" 
                   class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <span>Lihat di Peta Jaringan</span>
                </a>
                <a :href="'https://www.google.com/maps?q=' + lat + ',' + lng" 
                   target="_blank" 
                   class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                    <span>Lihat di Google Maps</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>
    </template>

    {{-- Modal Map Picker (Teleported to body to escape nested overflow containers) --}}
    <template x-teleport="body">
        <div x-show="showMapModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
             @click.self="closeMapModal()"
             style="display: none;">
            
            <div @click.stop
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Pilih Titik Lokasi Pada Peta</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Klik pada peta atau geser pin marker untuk menentukan koordinat.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/network/map') }}" 
                       target="_blank" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-lg border border-emerald-200 dark:border-emerald-800 transition-colors"
                       title="Buka Peta Topologi Jaringan di Tab Baru">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span>Peta Jaringan</span>
                    </a>
                    <button type="button" @click="closeMapModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Search & Toolbar --}}
            <div class="p-3 sm:p-4 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                {{-- Search Input Group (Tombol Cari tertanam rapi di dalam container input) --}}
                <div class="relative flex-1 flex items-center bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 focus-within:bg-white dark:focus-within:bg-slate-900 transition-all shadow-2xs">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           x-model="searchQuery" 
                           @keydown.enter.prevent="searchLocation()"
                           placeholder="Cari desa, jalan, kota, atau tempel koordinat / link Google Maps..." 
                           class="flex-1 min-w-0 bg-transparent border-0 outline-none px-2.5 py-1.5 text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400">
                    
                    {{-- Tombol Bersihkan Pencarian --}}
                    <button x-show="searchQuery" 
                            x-cloak
                            type="button" 
                            @click="searchQuery = ''" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 mr-1 transition-colors cursor-pointer"
                            title="Hapus teks pencarian">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Tombol Cari (Di dalam input bar) --}}
                    <button type="button" 
                            @click="searchLocation()" 
                            class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors shrink-0 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cari</span>
                    </button>
                </div>

                {{-- Tombol Lokasi GPS Saya Terpisah & Rapi --}}
                <button type="button" 
                        @click="useCurrentLocation()" 
                        :disabled="isLoadingGps"
                        class="shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-semibold shadow-2xs transition-colors cursor-pointer disabled:opacity-50">
                    <template x-if="!isLoadingGps">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="isLoadingGps">
                        <svg class="animate-spin h-4 w-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isLoadingGps ? 'Mendeteksi...' : 'Lokasi GPS Saya'"></span>
                </button>
            </div>

            {{-- Notice / Accuracy Banner --}}
            <div x-show="gpsNotice" class="px-4 py-2 bg-amber-50 dark:bg-amber-950/40 border-b border-amber-200 dark:border-amber-800/60 flex items-start justify-between gap-3 text-xs text-amber-900 dark:text-amber-200">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-html="gpsNotice"></span>
                </div>
                <button type="button" @click="gpsNotice = ''" class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-200 font-bold text-sm leading-none shrink-0 cursor-pointer">&times;</button>
            </div>

            {{-- Map Canvas Container with fixed fallback height --}}
            <div class="relative w-full bg-slate-100 dark:bg-slate-950 overflow-hidden" style="height: 480px; min-height: 420px;">
                <div :id="mapId" class="w-full h-full z-10" style="height: 480px; min-height: 420px;"></div>
                
                {{-- Loading Indicator --}}
                <div x-show="isLoading" class="absolute inset-0 bg-white/70 dark:bg-slate-900/70 z-20 flex items-center justify-center">
                    <div class="flex items-center gap-2 text-indigo-600 font-medium text-sm">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memuat Peta...</span>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4 text-xs font-mono">
                    <div>
                        <span class="text-slate-400">Lat:</span> 
                        <strong class="text-slate-800 dark:text-slate-200" x-text="tempLat || '-'"></strong>
                    </div>
                    <div>
                        <span class="text-slate-400">Lng:</span> 
                        <strong class="text-slate-800 dark:text-slate-200" x-text="tempLng || '-'"></strong>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="closeMapModal()" 
                            class="px-4 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button type="button" 
                            @click="applyLocation()" 
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-sm inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Gunakan Lokasi Ini</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>

@include('components.forms.coordinate-picker-script')

