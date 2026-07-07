<div>
    <!-- Map Container -->
    <div 
        id="gis-map" 
        class="w-full h-full rounded-lg"
        wire:ignore
    ></div>

    <!-- Map Controls Overlay -->
    <div class="absolute top-4 right-4 z-[1000] flex flex-col space-y-2">
        <!-- Zoom Controls -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <button 
                wire:click="zoomIn"
                class="block w-10 h-10 flex items-center justify-center hover:bg-gray-100 border-b border-gray-200 text-gray-700"
                title="Zoom In"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </button>
            <button 
                wire:click="zoomOut"
                class="block w-10 h-10 flex items-center justify-center hover:bg-gray-100 text-gray-700"
                title="Zoom Out"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
        </div>

        <!-- Fullscreen Toggle -->
        <button 
            wire:click="toggleFullscreen"
            class="bg-white rounded-lg shadow-lg w-10 h-10 flex items-center justify-center hover:bg-gray-100 text-gray-700"
            title="Toggle Fullscreen"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
            </svg>
        </button>

        <!-- Locate Me -->
        <button 
            wire:click="locateMe"
            class="bg-white rounded-lg shadow-lg w-10 h-10 flex items-center justify-center hover:bg-gray-100 text-gray-700"
            title="My Location"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </button>
    </div>

    <!-- Scale Bar -->
    <div class="absolute bottom-4 left-4 z-[1000] bg-white bg-opacity-90 rounded px-2 py-1 text-xs text-gray-600">
        <span id="map-scale"></span>
    </div>

    <!-- Coordinates Display -->
    <div class="absolute bottom-4 right-4 z-[1000] bg-white bg-opacity-90 rounded px-2 py-1 text-xs text-gray-600">
        <span id="map-coords">Lat: {{ $centerLat }} | Lon: {{ $centerLon }}</span>
    </div>

    <!-- Loading Overlay -->
    @if($isLoading)
    <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-[2000]">
        <div class="flex items-center space-x-2 text-gray-600">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading map...</span>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        // Leaflet map initialization
        document.addEventListener('livewire:load', function() {
            // Initialize map if not already initialized
            if (!window.gisMap) {
                window.gisMap = L.map('gis-map', {
                    center: [{{ $centerLat }}, {{ $centerLon }}],
                    zoom: {{ $zoom }},
                    zoomControl: false
                });

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(window.gisMap);

                // Add marker cluster group
                window.markers = L.markerClusterGroup();
                window.gisMap.addLayer(window.markers);

                // Add fiber lines layer
                window.fiberLines = L.layerGroup();
                window.gisMap.addLayer(window.fiberLines);

                // Event handlers
                window.gisMap.on('moveend', function(e) {
                    const center = window.gisMap.getCenter();
                    @this.set('centerLat', center.lat);
                    @this.set('centerLon', center.lng);
                    @this.set('zoom', window.gisMap.getZoom());
                });

                window.gisMap.on('click', function(e) {
                    // Handle map click
                });
            }
        });

        // Listen for tool changes
        window.addEventListener('toolChanged', function(e) {
            console.log('Tool changed to:', e.detail.tool);
        });

        // Listen for layer updates
        window.addEventListener('layersUpdated', function(e) {
            console.log('Layers updated:', e.detail.layers);
        });

        // Listen for zoom commands
        window.addEventListener('zoomIn', function() {
            if (window.gisMap) window.gisMap.zoomIn();
        });

        window.addEventListener('zoomOut', function() {
            if (window.gisMap) window.gisMap.zoomOut();
        });

        // Listen for fullscreen toggle
        window.addEventListener('toggleFullscreen', function(e) {
            const mapContainer = document.getElementById('gis-map');
            if (e.detail.state) {
                if (mapContainer.requestFullscreen) {
                    mapContainer.requestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });
    </script>
    @endpush
</div>
