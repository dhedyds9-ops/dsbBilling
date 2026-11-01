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
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
             style="display: none;">
            
            <div @click.away="closeMapModal()" 
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
                    <a href="{{ url('/reseller-portal/network/map') }}" 
                       target="_blank" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-lg border border-emerald-200 dark:border-emerald-800 transition-colors"
                       title="Buka Peta Topologi Jaringan Reseller di Tab Baru">
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
            <div class="p-3 sm:p-4 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-2.5">
                <div class="relative flex-1 min-w-[240px]">
                    <input type="text" 
                           x-model="searchQuery" 
                           @keydown.enter.prevent="searchLocation()"
                           placeholder="Cari desa, jalan, kota, atau tempel koordinat / link Google Maps..." 
                           class="w-full pl-9 pr-20 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-900 dark:text-slate-100">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" 
                            @click="searchLocation()" 
                            class="absolute right-1.5 top-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                        Cari
                    </button>
                </div>

                <button type="button" 
                        @click="useCurrentLocation()" 
                        :disabled="isLoadingGps"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50">
                    <template x-if="!isLoadingGps">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="isLoadingGps">
                        <svg class="animate-spin h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="none">
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

<script>
(function() {
    function registerCoordinatePicker() {
        if (!window.Alpine || window.__coordinatePickerRegistered) return;
        window.__coordinatePickerRegistered = true;

        Alpine.data('coordinatePicker', ({ lat, lng }) => ({
        lat: lat,
        lng: lng,
        tempLat: lat || '',
        tempLng: lng || '',
        showModal: false,
        isLoading: false,
        isLoadingGps: false,
        gpsNotice: '',
        accuracyCircle: null,
        searchQuery: '',
        mapId: 'map-picker-' + Math.random().toString(36).substring(2, 9),
        map: null,
        marker: null,

        openMapModal() {
            this.showModal = true;
            this.tempLat = this.lat || '';
            this.tempLng = this.lng || '';
            this.gpsNotice = '';
            this.$nextTick(() => {
                this.initLeaflet();
                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize();
                    }
                }, 150);
                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize();
                    }
                }, 400);
            });
        },

        closeMapModal() {
            this.showModal = false;
        },

        applyLocation() {
            if (this.tempLat && this.tempLng) {
                this.lat = this.tempLat;
                this.lng = this.tempLng;
            }
            this.closeMapModal();
        },

        async copyCoordinate(val, label) {
            if (!val) {
                alert(label + ' belum terisi.');
                return;
            }
            try {
                await navigator.clipboard.writeText(val);
                alert(label + ' berhasil disalin: ' + val);
            } catch (e) {
                const el = document.createElement('textarea');
                el.value = val;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                alert(label + ' berhasil disalin: ' + val);
            }
        },

        async pasteCoordinates(target) {
            try {
                const text = await navigator.clipboard.readText();
                const trimmed = text.trim();
                
                // Cek jika teks berupa "lat, lng" (misal: "-0.9345797, 100.226458")
                const pair = trimmed.match(/^([-+]?\d{1,2}(?:\.\d+)?)[,\s]+([-+]?\d{1,3}(?:\.\d+)?)$/);
                if (pair) {
                    this.lat = parseFloat(pair[1]).toFixed(7);
                    this.lng = parseFloat(pair[2]).toFixed(7);
                    alert('Berhasil menempelkan Latitude (' + this.lat + ') dan Longitude (' + this.lng + ')!');
                    return;
                }

                // Jika angka tunggal
                const single = trimmed.match(/^[-+]?\d{1,3}(?:\.\d+)?$/);
                if (single) {
                    if (target === 'lat') {
                        this.lat = parseFloat(single[0]).toFixed(7);
                    } else {
                        this.lng = parseFloat(single[0]).toFixed(7);
                    }
                    alert('Koordinat berhasil ditempel: ' + single[0]);
                } else {
                    alert('Teks di clipboard bukan format koordinat yang valid: ' + trimmed);
                }
            } catch (err) {
                const fallback = prompt('Tempelkan koordinat di sini (contoh: -0.9345797, 100.226458):');
                if (fallback) {
                    const pair = fallback.trim().match(/^([-+]?\d{1,2}(?:\.\d+)?)[,\s]+([-+]?\d{1,3}(?:\.\d+)?)$/);
                    if (pair) {
                        this.lat = parseFloat(pair[1]).toFixed(7);
                        this.lng = parseFloat(pair[2]).toFixed(7);
                    } else if (target === 'lat') {
                        this.lat = fallback.trim();
                    } else {
                        this.lng = fallback.trim();
                    }
                }
            }
        },

        initLeaflet() {
            this.loadLeafletAssets(() => {
                const defaultLat = parseFloat(this.tempLat) || -6.2088;
                const defaultLng = parseFloat(this.tempLng) || 106.8456;
                const zoom = (this.tempLat && this.tempLng) ? 16 : 13;

                const mapEl = document.getElementById(this.mapId);
                if (!mapEl) return;

                if (this.map) {
                    this.map.remove();
                    this.map = null;
                }

                this.map = L.map(this.mapId).setView([defaultLat, defaultLng], zoom);

                const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                    maxZoom: 22,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: 'Google'
                });

                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                });

                googleHybrid.addTo(this.map);
                L.control.layers({ 'Satelit (Google)': googleHybrid, 'Street (OSM)': osm }).addTo(this.map);

                const customIcon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                this.marker = L.marker([defaultLat, defaultLng], {
                    draggable: true,
                    icon: customIcon
                }).addTo(this.map);

                if (!this.tempLat && !this.tempLng) {
                    this.tempLat = defaultLat.toFixed(7);
                    this.tempLng = defaultLng.toFixed(7);
                }

                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.tempLat = pos.lat.toFixed(7);
                    this.tempLng = pos.lng.toFixed(7);
                });

                this.map.on('click', (e) => {
                    const pos = e.latlng;
                    this.tempLat = pos.lat.toFixed(7);
                    this.tempLng = pos.lng.toFixed(7);
                    this.marker.setLatLng(pos);
                });

                window.pickNearbyOdp = (lat, lng) => {
                    this.tempLat = parseFloat(lat).toFixed(7);
                    this.tempLng = parseFloat(lng).toFixed(7);
                    this.marker.setLatLng([lat, lng]);
                };

                let firstValidOdp = null;
                fetch('/map/odps')
                    .then(r => r.json())
                    .then(odps => {
                        if (!Array.isArray(odps)) return;
                        const odpIcon = L.divIcon({
                            html: '<div style="background:#10b981;border:2px solid #ffffff;border-radius:4px;width:20px;height:20px;display:flex;align-items:center;justify-content:center;color:#ffffff;font-size:10px;font-weight:bold;box-shadow:0 2px 6px rgba(0,0,0,0.5);">&#9632;</div>',
                            className: '',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });
                        odps.forEach(odp => {
                            const oLat = parseFloat(odp.latitude);
                            const oLng = parseFloat(odp.longitude);
                            if (isNaN(oLat) || isNaN(oLng)) return;
                            if (!firstValidOdp) firstValidOdp = [oLat, oLng];
                            const m = L.marker([oLat, oLng], { icon: odpIcon }).addTo(this.map);
                            m.bindPopup('<div style="min-width:140px;font-family:sans-serif;"><strong style="color:#0f172a;font-size:12px;">ODP: ' + (odp.name || '') + '</strong><br><span style="font-size:11px;color:#64748b;">' + (odp.code || '') + '</span><div style="margin-top:6px;"><button type="button" style="padding:4px 8px;background:#4f46e5;color:#fff;border:none;border-radius:4px;font-size:11px;cursor:pointer;font-weight:600;" onclick="window.pickNearbyOdp(' + oLat + ', ' + oLng + ')">Pilih Dekat Sini</button></div></div>');
                        });

                        // Center on ODP if no custom coords
                        if ((!this.lat || !this.lng) && firstValidOdp && defaultLat === -6.2088) {
                            this.tempLat = firstValidOdp[0].toFixed(7);
                            this.tempLng = firstValidOdp[1].toFixed(7);
                            this.marker.setLatLng(firstValidOdp);
                            this.map.setView(firstValidOdp, 15);
                        }
                    })
                    .catch(() => {});

                setTimeout(() => {
                    this.map.invalidateSize();
                }, 300);
            });
        },

        loadLeafletAssets(cb) {
            if (window.L) {
                cb();
                return;
            }
            this.isLoading = true;
            if (!document.getElementById('leaflet-css')) {
                const link = document.createElement('link');
                link.id = 'leaflet-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }

            if (!document.getElementById('leaflet-js')) {
                const script = document.createElement('script');
                script.id = 'leaflet-js';
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => {
                    this.isLoading = false;
                    cb();
                };
                document.head.appendChild(script);
            } else {
                const poll = setInterval(() => {
                    if (window.L) {
                        clearInterval(poll);
                        this.isLoading = false;
                        cb();
                    }
                }, 50);
            }
        },

        useCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation.');
                return;
            }
            this.isLoadingGps = true;
            this.gpsNotice = 'Sedang mendeteksi posisi GPS perangkat...';

            const handleSuccess = (pos) => {
                this.isLoadingGps = false;
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const accuracy = pos.coords.accuracy || 0;

                this.tempLat = lat.toFixed(7);
                this.tempLng = lng.toFixed(7);

                if (this.accuracyCircle && this.map) {
                    this.map.removeLayer(this.accuracyCircle);
                    this.accuracyCircle = null;
                }

                if (this.map && this.marker) {
                    this.marker.setLatLng([lat, lng]);

                    if (accuracy > 0 && accuracy < 100000) {
                        this.accuracyCircle = L.circle([lat, lng], {
                            radius: accuracy,
                            color: '#4f46e5',
                            fillColor: '#818cf8',
                            fillOpacity: 0.15,
                            weight: 1.5
                        }).addTo(this.map);
                    }

                    if (accuracy < 100) {
                        this.map.setView([lat, lng], 18);
                        this.gpsNotice = `<strong>Akurasi GPS Tinggi (±${Math.round(accuracy)}m).</strong> Titik koordinat berhasil diterapkan.`;
                    } else if (accuracy < 2500) {
                        this.map.setView([lat, lng], 16);
                        this.gpsNotice = `<strong>Akurasi ±${Math.round(accuracy)}m.</strong> Silakan geser pin merah di peta ke posisi tiang/atap yang persis.`;
                    } else {
                        this.map.setView([lat, lng], 13);
                        this.gpsNotice = `<strong>Info:</strong> Lokasi terdeteksi dari IP Internet provider (estimasi ±${Math.round(accuracy / 1000)} km). Di PC/Laptop tanpa GPS satelit, IP sering mengarah ke Gateway/NOC ISP di Jakarta/kota besar. <strong>Silakan ketik nama desa/alamat di kotak cari atau geser pin merah ke lokasi sebenarnya.</strong>`;
                    }
                }
            };

            const handleError = (err) => {
                // Retry dengan low accuracy jika high accuracy gagal/timeout (umum di desktop Windows)
                navigator.geolocation.getCurrentPosition(
                    handleSuccess,
                    (errFallback) => {
                        this.isLoadingGps = false;
                        if (errFallback.code === 1) {
                            this.gpsNotice = '<strong>Izin Lokasi Ditolak:</strong> Izinkan akses lokasi pada ikon setelan/gembok di address bar browser Anda.';
                        } else {
                            this.gpsNotice = '<strong>Lokasi Tidak Terdeteksi:</strong> ' + (errFallback.message || err.message) + '. Silakan ketik nama daerah/desa di kotak pencarian atau tempel titik koordinat.';
                        }
                    },
                    { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 }
                );
            };

            navigator.geolocation.getCurrentPosition(
                handleSuccess,
                handleError,
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 }
            );
        },

        async searchLocation() {
            const query = (this.searchQuery || '').trim();
            if (!query) return;

            // 1. Cek apakah input berupa koordinat langsung atau link Google Maps
            // Contoh format: "-6.91234, 107.61234", "@-6.91234,107.61234", "?q=-6.91234,107.61234"
            const coordRegex = /([-+]?\d{1,2}(?:\.\d+)?)[,\s]+([-+]?\d{1,3}(?:\.\d+)?)/;
            const coordMatch = query.match(coordRegex);
            if (coordMatch) {
                const lat = parseFloat(coordMatch[1]);
                const lng = parseFloat(coordMatch[2]);
                if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    this.tempLat = lat.toFixed(7);
                    this.tempLng = lng.toFixed(7);
                    if (this.map && this.marker) {
                        this.marker.setLatLng([lat, lng]);
                        this.map.setView([lat, lng], 17);
                    }
                    this.gpsNotice = `Peta diarahkan ke koordinat: <strong>${this.tempLat}, ${this.tempLng}</strong>`;
                    return;
                }
            }

            // 2. Pencarian Nama Tempat via OpenStreetMap Nominatim
            this.isLoading = true;
            this.gpsNotice = '';
            try {
                // Coba pencarian dengan prioritas Indonesia
                let res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=5&q=${encodeURIComponent(query)}`);
                let data = await res.json();
                
                // Fallback pencarian global
                if (!data || data.length === 0) {
                    res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(query)}`);
                    data = await res.json();
                }

                this.isLoading = false;
                if (data && data.length > 0) {
                    const first = data[0];
                    const lat = parseFloat(first.lat);
                    const lng = parseFloat(first.lon);
                    this.tempLat = lat.toFixed(7);
                    this.tempLng = lng.toFixed(7);
                    if (this.map && this.marker) {
                        this.marker.setLatLng([lat, lng]);
                        this.map.setView([lat, lng], 16);
                    }
                    this.gpsNotice = `Ditemukan: <strong>${first.display_name.split(',').slice(0, 3).join(', ')}</strong>`;
                } else {
                    alert('Lokasi tidak ditemukan. Coba ketik nama desa/kelurahan, jalan, kota, atau tempel koordinat lat, lng.');
                }
            } catch (e) {
                this.isLoading = false;
                alert('Gagal mencari lokasi: ' + e.message);
            }
        }
        }));
    }

    if (window.Alpine) {
        registerCoordinatePicker();
    } else {
        document.addEventListener('alpine:init', registerCoordinatePicker);
    }
})();
</script>

