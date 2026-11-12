<div class="space-y-3.5 pb-8"
     x-data="{
         gpsLoading: true,
         gpsError: false,
         gpsAccuracy: null,
         userLat: @entangle('latitude'),
         userLng: @entangle('longitude'),
         map: null,
         userMarker: null,
         accuracyCircle: null,
         branchMarkers: [],
         branchesData: {{ json_encode($branches ?? []) }},
         homeBranchData: {{ json_encode($homeBranch ?? null) }},
         
         init() {
             this.$nextTick(() => {
                 this.initMap();
                 this.trackLocation();
             });
         },
         
         initMap() {
             if (this.map) return;
             
             const defaultLat = this.homeBranchData?.latitude ? parseFloat(this.homeBranchData.latitude) : -6.9175;
             const defaultLng = this.homeBranchData?.longitude ? parseFloat(this.homeBranchData.longitude) : 107.6191;
             
             this.map = L.map('attendance-map', {
                 zoomControl: false,
                 attributionControl: false
             }).setView([defaultLat, defaultLng], 16);
             
             L.control.zoom({ position: 'topleft' }).addTo(this.map);
             
             // Base Layers: Google Satellite Hybrid (default) & OSM & Carto
             const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                 subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                 maxZoom: 20
             });
             
             const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 maxZoom: 19
             });
             
             const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                 maxZoom: 20
             });
             
             googleHybrid.addTo(this.map);
             
             const baseLayers = {
                 'Satelit (Google)': googleHybrid,
                 'Street (OSM)': osm,
                 'Mode Gelap': cartoDark
             };
             
             L.control.layers(baseLayers, null, { position: 'topright', collapsed: true }).addTo(this.map);
             
             // Render Office / Branch Geofence Pins
             if (Array.isArray(this.branchesData)) {
                 this.branchesData.forEach(br => {
                     if (br.latitude && br.longitude) {
                         const lat = parseFloat(br.latitude);
                         const lng = parseFloat(br.longitude);
                         
                         // Geofence Circle 150m
                         L.circle([lat, lng], {
                             radius: 150,
                             color: '#10B981',
                             fillColor: '#10B981',
                             fillOpacity: 0.12,
                             weight: 1.5,
                             dashArray: '4, 4'
                         }).addTo(this.map);
                         
                         // Office Marker
                         const officeIcon = L.divIcon({
                             className: 'office-pin-wrapper',
                             html: '<div class="w-6 h-6 rounded-full bg-emerald-600 border-2 border-white shadow-md flex items-center justify-center text-white text-[10px] font-black"><span class="material-symbols-outlined notranslate text-xs">domain</span></div>',
                             iconSize: [24, 24],
                             iconAnchor: [12, 12]
                         });
                         
                         L.marker([lat, lng], { icon: officeIcon })
                             .bindPopup('<b>' + br.name + '</b><br><span class="text-xs text-slate-500">Radius Kantor: 150m</span>')
                             .addTo(this.map);
                     }
                 });
             }
         },
         
         trackLocation() {
             if (!navigator.geolocation) {
                 this.gpsLoading = false;
                 this.gpsError = true;
                 return;
             }
             
             this.gpsLoading = true;
             this.gpsError = false;
             
             navigator.geolocation.getCurrentPosition(
                 (pos) => this.handlePosition(pos, true),
                 (err) => {
                     this.gpsLoading = false;
                     this.gpsError = true;
                 },
                 { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
             );
             
             // Live Watch
             try {
                 navigator.geolocation.watchPosition(
                     (pos) => this.handlePosition(pos, false),
                     null,
                     { enableHighAccuracy: true, maximumAge: 5000 }
                 );
             } catch(e) {}
         },
         
         handlePosition(pos, shouldRecenter = false) {
             const lat = pos.coords.latitude;
             const lng = pos.coords.longitude;
             this.gpsAccuracy = Math.round(pos.coords.accuracy);
             this.userLat = lat;
             this.userLng = lng;
             this.gpsLoading = false;
             this.gpsError = false;
             
             // Dispatch to Livewire
             this.$dispatch('gps-located', { lat: lat, lng: lng });
             
             if (this.map) {
                 // Pulsing GPS Icon
                 const gpsPulseIcon = L.divIcon({
                     className: 'custom-gps-icon',
                     html: '<div class="relative w-8 h-8 flex items-center justify-center"><div class="absolute inset-0 rounded-full bg-indigo-500/30 animate-ping"></div><div class="w-4 h-4 rounded-full bg-indigo-600 border-2 border-white shadow-lg z-10"></div></div>',
                     iconSize: [32, 32],
                     iconAnchor: [16, 16]
                 });
                 
                 if (!this.userMarker) {
                     this.userMarker = L.marker([lat, lng], { icon: gpsPulseIcon }).addTo(this.map);
                 } else {
                     this.userMarker.setLatLng([lat, lng]);
                 }
                 
                 // Accuracy Circle
                 if (!this.accuracyCircle) {
                     this.accuracyCircle = L.circle([lat, lng], {
                         radius: this.gpsAccuracy || 10,
                         color: '#6366F1',
                         fillColor: '#6366F1',
                         fillOpacity: 0.1,
                         weight: 1
                     }).addTo(this.map);
                 } else {
                     this.accuracyCircle.setLatLng([lat, lng]);
                     this.accuracyCircle.setRadius(this.gpsAccuracy || 10);
                 }
                 
                 if (shouldRecenter) {
                     this.map.setView([lat, lng], 17);
                 }
             }
         },
         
         recenter() {
             if (this.userLat && this.userLng && this.map) {
                 this.map.setView([this.userLat, this.userLng], 17, { animate: true });
             } else {
                 this.trackLocation();
             }
         }
     }">
    <!-- LEAFLET CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Error / Success Alert -->
    @if($errorMessage)
        <div class="bg-rose-50 dark:bg-rose-950/40 p-3.5 rounded-2xl border border-rose-200 dark:border-rose-800/60 flex items-center gap-3 text-rose-800 dark:text-rose-300 text-xs shadow-sm">
            <span class="material-symbols-outlined text-rose-500 shrink-0 text-xl">error</span>
            <p class="font-bold flex-1">{{ $errorMessage }}</p>
        </div>
    @endif
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/40 p-3.5 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-3 text-emerald-800 dark:text-emerald-300 text-xs shadow-sm">
            <span class="material-symbols-outlined text-emerald-500 shrink-0 text-xl">check_circle</span>
            <p class="font-bold flex-1">{{ session('success') }}</p>
        </div>
    @endif

    <!-- 1. MAP SECTION (Matching Android APK Mockup) -->
    <div class="relative w-full h-56 sm:h-72 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-md bg-slate-200 dark:bg-slate-800">
        <!-- Leaflet Map Container -->
        <div id="attendance-map" class="w-full h-full z-0" wire:ignore></div>

        <!-- Floating Recenter FAB Button -->
        <button type="button"
                @click="recenter()"
                class="absolute bottom-3 right-3 z-10 w-10 h-10 rounded-full bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-95 transition-all"
                title="Pusatkan ke Lokasi Saya">
            <span class="material-symbols-outlined text-xl">my_location</span>
        </button>

        <!-- Floating GPS Status Pill -->
        <div class="absolute top-3 left-14 z-10 px-2.5 py-1 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur border border-slate-200 dark:border-slate-700 text-[11px] font-bold flex items-center gap-1.5 shadow-sm">
            <span class="w-2 h-2 rounded-full"
                  :class="gpsLoading ? 'bg-blue-500 animate-ping' : (gpsError ? 'bg-rose-500' : 'bg-emerald-500')"></span>
            <span class="text-slate-700 dark:text-slate-300"
                  x-text="gpsLoading ? 'Mencari GPS...' : (gpsError ? 'GPS Tidak Aktif' : ('GPS Akurat (±' + (gpsAccuracy || 9) + 'm)'))"></span>
        </div>
    </div>

    <!-- 2. STATUS KEHADIRAN CARD (Matching Screen 5 Mockup) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
        @if($attendance && $attendance->checked_in_at)
            <!-- Checked In -->
            <div class="w-12 h-12 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">Sudah Absen</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 font-medium">
                    Masuk: <strong>{{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i') }} WIB</strong>
                    @if($attendance->checked_out_at)
                        &bull; Pulang: <strong>{{ \Carbon\Carbon::parse($attendance->checked_out_at)->format('H:i') }} WIB</strong>
                    @endif
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5"
                   x-text="'Lokasi: ' + (gpsError ? 'GPS Non-Aktif' : ('Akurat (±' + (gpsAccuracy || 9) + ' m)'))">
                    Lokasi: Mendeteksi GPS...
                </p>
            </div>
        @else
            <!-- Not Checked In Yet -->
            <div class="w-12 h-12 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">Belum Absen Masuk</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
                    Masuk: --:-- WIB
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5"
                   x-text="'Lokasi: ' + (gpsLoading ? 'Mencari GPS...' : (gpsError ? 'GPS Tidak Aktif' : ('Akurat (±' + (gpsAccuracy || 9) + ' m)')))">
                    Lokasi: Mendeteksi GPS...
                </p>
            </div>
        @endif
    </div>

    <!-- 3. ACTION BUTTONS (ABSEN MASUK & ABSEN PULANG) -->
    <div class="space-y-2.5">
        <!-- ABSEN MASUK BUTTON -->
        @if(!$attendance)
            <button type="button"
                    wire:click="checkIn"
                    wire:loading.attr="disabled"
                    wire:target="checkIn"
                    :disabled="gpsLoading || gpsError"
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-sm uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 active:scale-[0.98] transition-all">
                <span wire:loading.remove wire:target="checkIn">ABSEN MASUK</span>
                <span wire:loading wire:target="checkIn" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Memverifikasi...
                </span>
            </button>
        @else
            <button type="button" disabled
                    class="w-full py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-2xl font-black text-sm uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                SUDAH ABSEN MASUK
            </button>
        @endif

        <!-- ABSEN PULANG BUTTON -->
        @if($attendance && !$attendance->checked_out_at)
            <button type="button"
                    wire:click="checkOut"
                    wire:loading.attr="disabled"
                    wire:target="checkOut"
                    :disabled="gpsLoading || gpsError"
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-sm uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 active:scale-[0.98] transition-all">
                <span wire:loading.remove wire:target="checkOut">ABSEN PULANG</span>
                <span wire:loading wire:target="checkOut" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Memproses Pulang...
                </span>
            </button>
        @elseif($attendance && $attendance->checked_out_at)
            <button type="button" disabled
                    class="w-full py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-2xl font-black text-sm uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                SUDAH ABSEN PULANG
            </button>
        @else
            <button type="button" disabled
                    class="w-full py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-2xl font-black text-sm uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
                ABSEN PULANG
            </button>
        @endif
    </div>

    <!-- 4. TUGAS LUAR / LAPANGAN CARD (Matching Image 1) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <label for="isFieldAssignment" class="text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                    Tugas Luar / Lapangan
                </label>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                    Aktifkan jika ditugaskan langsung ke rumah pelanggan / ODP
                </p>
            </div>
            <!-- Native-styled Toggle Switch -->
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="isFieldAssignment" wire:model.live="isFieldAssignment" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
            </label>
        </div>

        <div>
            <input type="text"
                   wire:model="notes"
                   placeholder="Catatan Keperluan / No. SPK Lapangan"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder:text-slate-400 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
        </div>
    </div>

    <!-- 5. RIWAYAT HARI INI CARD (Matching Image 1) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
            Riwayat Hari Ini
        </h4>

        <!-- Masuk -->
        <div class="flex items-start gap-3 pt-1">
            @if($attendance && $attendance->checked_in_at)
                <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">Masuk</span>
                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i') }} WIB</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate leading-tight">
                        {{ $attendance->notes ?: 'Presensi Teknisi Lapangan dsBilling' }}
                        @if($homeBranchData)
                            &bull; Lokasi: {{ $homeBranchData['name'] ?? 'Kantor Cabang' }}
                        @endif
                    </p>
                </div>
            @else
                <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-400 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">Masuk</span>
                        <span class="text-xs text-slate-400">Belum absen</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Pulang -->
        <div class="flex items-start gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
            @if($attendance && $attendance->checked_out_at)
                <div class="w-5 h-5 rounded-full bg-indigo-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">Pulang</span>
                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($attendance->checked_out_at)->format('H:i') }} WIB</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate leading-tight">
                        Check-Out Selesai Dinas
                    </p>
                </div>
            @else
                <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-400 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">Pulang</span>
                        <span class="text-xs text-slate-400">Belum absen</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>