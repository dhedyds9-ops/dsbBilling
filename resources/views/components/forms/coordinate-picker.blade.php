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
    <div class="{{ $gridClass }}">
        {{-- Latitude Input --}}
        <div>
            <div class="flex items-center justify-between gap-2 mb-1">
                <label class="flex items-center gap-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">↕</span>
                    <span>Latitude</span>
                </label>
                <button type="button" 
                        @click="openMapModal()" 
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/80 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pilih Lokasi Di Maps</span>
                </button>
            </div>
            <div class="relative flex rounded-lg shadow-2xs">
                <input type="text" 
                       x-model="lat" 
                       placeholder="e.g : -0.9345797" 
                       class="block w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-l-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                
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
            <div class="flex items-center justify-between gap-2 mb-1">
                <label class="flex items-center gap-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">↔</span>
                    <span>Longitude</span>
                </label>
                <button type="button" 
                        @click="openMapModal()" 
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/80 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pilih Lokasi Di Maps</span>
                </button>
            </div>
            <div class="relative flex rounded-lg shadow-2xs">
                <input type="text" 
                       x-model="lng" 
                       placeholder="e.g : 100.226458" 
                       class="block w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-l-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                
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

    {{-- Link ke Google Maps jika koordinat sudah terisi --}}
    <template x-if="lat && lng">
        <div class="mt-2 flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-mono">
                Koordinat Terpasang: <strong class="text-slate-700 dark:text-slate-200" x-text="lat + ', ' + lng"></strong>
            </span>
            <a :href="'https://www.google.com/maps?q=' + lat + ',' + lng" 
               target="_blank" 
               class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                <span>Lihat di Google Maps</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
    </template>

    {{-- Modal Map Picker --}}
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
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

                <button type="button" @click="closeMapModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Search & Toolbar --}}
            <div class="p-4 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[240px]">
                    <input type="text" 
                           x-model="searchQuery" 
                           @keydown.enter.prevent="searchLocation()"
                           placeholder="Cari nama lokasi, kota, atau alamat..." 
                           class="w-full pl-9 pr-20 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-900 dark:text-slate-100">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" 
                            @click="searchLocation()" 
                            class="absolute right-1.5 top-1.5 px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition-colors">
                        Cari
                    </button>
                </div>

                <button type="button" 
                        @click="useCurrentLocation()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Lokasi GPS Saya</span>
                </button>
            </div>

            {{-- Map Canvas Container --}}
            <div class="relative flex-1 min-h-[420px] bg-slate-100 dark:bg-slate-950">
                <div :id="mapId" class="absolute inset-0 w-full h-full z-10"></div>
                
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
        searchQuery: '',
        mapId: 'map-picker-' + Math.random().toString(36).substring(2, 9),
        map: null,
        marker: null,

        openMapModal() {
            this.showModal = true;
            this.tempLat = this.lat || '';
            this.tempLng = this.lng || '';
            this.$nextTick(() => {
                this.initLeaflet();
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
                const zoom = (this.tempLat && this.tempLng) ? 16 : 12;

                const mapEl = document.getElementById(this.mapId);
                if (!mapEl) return;

                if (this.map) {
                    this.map.remove();
                    this.map = null;
                }

                this.map = L.map(this.mapId).setView([defaultLat, defaultLng], zoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(this.map);

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
            this.isLoading = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.isLoading = false;
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.tempLat = lat.toFixed(7);
                    this.tempLng = lng.toFixed(7);
                    if (this.map && this.marker) {
                        this.marker.setLatLng([lat, lng]);
                        this.map.setView([lat, lng], 17);
                    }
                },
                (err) => {
                    this.isLoading = false;
                    alert('Gagal mengambil lokasi GPS: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        async searchLocation() {
            if (!this.searchQuery.trim()) return;
            this.isLoading = true;
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();
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
                } else {
                    alert('Lokasi tidak ditemukan.');
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
