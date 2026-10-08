<div>
    <div class="mb-6 flex flex-col gap-3 justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">share_location</span>
                Pemetaan ODP Terdekat
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Gunakan GPS perangkat Anda untuk menemukan titik ODP terdekat di sekitar lokasi instalasi.</p>
        </div>
        <div class="flex flex-col gap-2 w-full">
            <select wire:model.live="radius" wire:change="loadNearestOdps" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                <option value="1">Radius 1 KM</option>
                <option value="2">Radius 2 KM</option>
                <option value="5">Radius 5 KM</option>
                <option value="10">Radius 10 KM</option>
            </select>
            <button type="button" onclick="requestLocation()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <span class="material-symbols-outlined notranslate text-sm" translate="no">my_location</span>
                Lacak Lokasi Saya
            </button>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <!-- Peta -->
        <div class="w-full">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative">
                <div id="map" style="height: 500px; width: 100%; z-index: 10;"></div>
                
                <div wire:loading wire:target="loadNearestOdps" class="absolute inset-0 bg-slate-900/20 backdrop-blur-sm z-20 flex items-center justify-center">
                    <div class="bg-white dark:bg-slate-800 rounded-lg p-4 shadow-xl flex flex-col gap-2 w-full">
                        <span class="material-symbols-outlined notranslate animate-spin text-indigo-600" translate="no">autorenew</span>
                        <span class="font-medium text-slate-900 dark:text-white">Mencari ODP...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar ODP -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-0 flex flex-col h-[500px]">
            <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center justify-between">
                    <span>Hasil Pencarian</span>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-indigo-900 dark:text-indigo-300">{{ count($odps) }} Ditemukan</span>
                </h3>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @forelse($odps as $odp)
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 hover:border-indigo-500 transition-colors cursor-pointer" onclick="focusOdp({{ $odp['latitude'] }}, {{ $odp['longitude'] }})">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">{{ $odp['code'] }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $odp['name'] }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-md">
                                <span class="material-symbols-outlined notranslate text-[14px]" translate="no">route</span>
                                {{ $odp['distance'] }} km
                            </span>
                        </div>
                        
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-50 dark:bg-slate-900 p-2 rounded-lg text-center">
                                <span class="block text-slate-500 dark:text-slate-400">Total Port</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $odp['port_count'] }}</span>
                            </div>
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-2 rounded-lg text-center border {{ $odp['available_port_count'] > 0 ? 'border-indigo-200 dark:border-indigo-800' : 'border-red-200 dark:border-red-800' }}">
                                <span class="block text-slate-500 dark:text-slate-400">Port Kosong</span>
                                <span class="font-bold {{ $odp['available_port_count'] > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-600 dark:text-red-400' }}">{{ $odp['available_port_count'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">location_off</span>
                        <p>Tidak ada ODP ditemukan dalam radius {{ $radius }} km.</p>
                        <p class="text-xs mt-1">Klik tombol 'Lacak Lokasi Saya' atau klik area mana saja di peta.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Include Leaflet CSS/JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        let map, userMarker, odpLayerGroup;
        
        document.addEventListener('livewire:initialized', () => {
            initMap();
            
            // Listen for ODP updates from backend
            Livewire.on('odps-updated', (data) => {
                const odps = data.odps || [];
                renderOdpsOnMap(odps);
            });
        });

        function initMap() {
            // Setup map
            map = L.map('map').setView([{{ $latitude }}, {{ $longitude }}], 14);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
            }).addTo(map);
            
            odpLayerGroup = L.layerGroup().addTo(map);
            
            // Place marker for current center
            updateUserMarker({{ $latitude }}, {{ $longitude }});
            
            // Map click event
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                updateUserMarker(lat, lng);
                // Call Livewire Component
                @this.setLocation(lat, lng);
            });
        }
        
        function updateUserMarker(lat, lng) {
            if(userMarker) map.removeLayer(userMarker);
            
            const pinIcon = L.divIcon({
                className: 'custom-div-icon',
                html: '<div style="background-color:#4f46e5; width:16px; height:16px; border-radius:50%; border:3px solid white; box-shadow:0 0 10px rgba(0,0,0,0.5);"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            
            userMarker = L.marker([lat, lng], {icon: pinIcon}).addTo(map);
            userMarker.bindPopup('<b>Titik Acuan Pencarian</b>').openPopup();
        }
        
        function renderOdpsOnMap(odps) {
            odpLayerGroup.clearLayers();
            
            odps.forEach(odp => {
                if(!odp.latitude || !odp.longitude) return;
                
                const isFull = odp.available_port_count === 0;
                const markerColor = isFull ? '#ef4444' : '#10b981'; // red if full, green if available
                
                const odpIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: <div style="background-color:; width:24px; height:24px; border-radius:8px; border:2px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; color:white; font-size:12px; font-weight:bold;"></div>,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                
                const marker = L.marker([odp.latitude, odp.longitude], {icon: odpIcon}).addTo(odpLayerGroup);
                
                marker.bindPopup(
                    <div class="text-sm">
                        <strong class="block text-base mb-1"></strong>
                        <span class="block text-slate-600"></span>
                        <div class="mt-2 pt-2 border-t border-slate-200">
                            Port Tersedia: <strong style="color:"></strong> / <br>
                            Jarak: <strong> km</strong>
                        </div>
                    </div>
                );
            });
            
            // Adjust bounds if we have ODPs
            if(odps.length > 0) {
                const group = new L.featureGroup([userMarker, ...odpLayerGroup.getLayers()]);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
        
        function requestLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 15);
                    updateUserMarker(lat, lng);
                    @this.setLocation(lat, lng);
                }, function(error) {
                    alert("Gagal mendapatkan lokasi. Pastikan izin lokasi (GPS) aktif di browser Anda.");
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            } else {
                alert("Browser Anda tidak mendukung fitur geolokasi.");
            }
        }
        
        function focusOdp(lat, lng) {
            map.setView([lat, lng], 18);
            // Optionally, open popup
            odpLayerGroup.eachLayer(function(marker) {
                if (marker.getLatLng().lat === lat && marker.getLatLng().lng === lng) {
                    marker.openPopup();
                }
            });
        }
    </script>
</div>

