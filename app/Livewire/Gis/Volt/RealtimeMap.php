<?php

use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use Illuminate\Support\Facades\Cache;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

state([
    'centerLat' => -6.2088,
    'centerLon' => 106.8456,
    'zoom' => 12,
    'markers' => [],
    'selectedMarker' => null,
]);

computed(fn () => $this->loadMarkers());

$loadMarkers = function () {
    $this->markers = Cache::remember('gis_map_markers', 30, function () {
        return $this->fetchMarkers();
    });
};

$fetchMarkers = function () {
    $oltMarkers = Olt::select(['id', 'name', 'latitude', 'longitude', 'status', 'utilization'])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->limit(100)
        ->get()
        ->map(fn($olt) => [
            'id' => $olt->id,
            'type' => 'olt',
            'name' => $olt->name,
            'lat' => $olt->latitude,
            'lng' => $olt->longitude,
            'status' => $olt->status ?? 'active',
            'utilization' => $olt->utilization ?? 0,
        ]);

    $odpMarkers = Odp::select(['id', 'name', 'latitude', 'longitude', 'status', 'utilization'])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->limit(500)
        ->get()
        ->map(fn($odp) => [
            'id' => $odp->id,
            'type' => 'odp',
            'name' => $odp->name,
            'lat' => $odp->latitude,
            'lng' => $odp->longitude,
            'status' => $odp->status ?? 'active',
            'utilization' => $odp->utilization ?? 0,
        ]);

    return $oltMarkers->concat($odpMarkers)->toArray();
};

$selectMarker = function ($markerId) {
    $this->selectedMarker = collect($this->markers)->firstWhere('id', $markerId);
};

$clearSelection = function () {
    $this->selectedMarker = null;
};

$refreshMarkers = function () {
    Cache::forget('gis_map_markers');
    $this->loadMarkers();
};

$setCenter = function ($lat, $lon) {
    $this->centerLat = $lat;
    $this->centerLon = $lon;
};

$setZoom = function ($level) {
    $this->zoom = max(1, min(18, $level));
};

$fitBounds = function ($bounds) {
    // Set map center and zoom based on bounds
    // bounds format: ['north' => lat, 'south' => lat, 'east' => lon, 'west' => lon]
};
?>

<div class="relative" wire:ignore>
    <!-- Map Container -->
    <div 
        id="realtime-map"
        class="w-full h-full rounded-lg"
        x-data="{
            map: null,
            markersLayer: null,
            
            init() {
                this.initMap();
            },
            
            initMap() {
                this.map = L.map('realtime-map', {
                    center: [{{ $centerLat }}, {{ $centerLon }}],
                    zoom: {{ $zoom }},
                });
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(this.map);
                
                this.markersLayer = L.layerGroup().addTo(this.map);
                this.renderMarkers();
                
                this.map.on('moveend', () => {
                    const center = this.map.getCenter();
                    @this.set('centerLat', center.lat);
                    @this.set('centerLon', center.lng);
                    @this.set('zoom', this.map.getZoom());
                });
            },
            
            renderMarkers() {
                this.markersLayer.clearLayers();
                
                @foreach($markers as $marker)
                (() => {
                    const color = @json($marker['status']) === 'critical' ? '#EF4444' : 
                                  @json($marker['status']) === 'warning' ? '#F59E0B' : 
                                  @json($marker['status']) === 'inactive' ? '#6B7280' : '#10B981';
                    
                    const icon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style='background-color: ${color}; width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);'></div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12],
                    });
                    
                    const m = L.marker([{{ $marker['lat'] }}, {{ $marker['lng'] }}], { icon })
                        .bindPopup(`
                            <div class='p-2'>
                                <h4 class='font-semibold'>{{ $marker['name'] }}</h4>
                                <p class='text-sm text-gray-500'>{{ strtoupper($marker['type']) }}</p>
                                <p class='text-sm mt-1'>Utilization: {{ $marker['utilization'] }}%</p>
                                <button 
                                    wire:click='selectMarker({{ json_encode($marker['id']) }})'
                                    class='mt-2 px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600'
                                >
                                    View Details
                                </button>
                            </div>
                        `);
                    
                    this.markersLayer.addLayer(m);
                })();
                @endforeach
            }
        }"
    >
    </div>

    <!-- Selected Marker Info -->
    @if($selectedMarker)
    <div class="absolute top-4 left-4 z-[1000] bg-white rounded-lg shadow-lg p-4 w-64">
        <div class="flex items-center justify-between mb-2">
            <span class="px-2 py-1 text-xs font-medium rounded bg-{{ $selectedMarker['type'] === 'olt' ? 'blue' : 'green' }}-100 text-{{ $selectedMarker['type'] === 'olt' ? 'blue' : 'green' }}-800">
                {{ strtoupper($selectedMarker['type']) }}
            </span>
            <button wire:click="clearSelection" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <h4 class="font-semibold text-gray-900">{{ $selectedMarker['name'] }}</h4>
        <p class="text-sm text-gray-500 mt-1">
            Lat: {{ $selectedMarker['lat'] }}, Lng: {{ $selectedMarker['lng'] }}
        </p>
        <div class="flex items-center space-x-2 mt-2">
            <span class="status-indicator status-{{ $selectedMarker['status'] }}"></span>
            <span class="text-sm capitalize">{{ $selectedMarker['status'] }}</span>
        </div>
        <div class="mt-2">
            <p class="text-xs text-gray-500">Utilization</p>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                <div 
                    class="h-2 rounded-full {{ ($selectedMarker['utilization'] ?? 0) >= 90 ? 'bg-red-500' : (($selectedMarker['utilization'] ?? 0) >= 75 ? 'bg-amber-500' : 'bg-green-500') }}"
                    style="width: {{ $selectedMarker['utilization'] ?? 0 }}%"
                ></div>
            </div>
            <p class="text-xs text-right mt-1">{{ $selectedMarker['utilization'] ?? 0 }}%</p>
        </div>
    </div>
    @endif

    <!-- Controls -->
    <div class="absolute bottom-4 right-4 z-[1000] flex flex-col space-y-2">
        <button 
            wire:click="refreshMarkers"
            class="bg-white rounded-lg shadow-lg w-10 h-10 flex items-center justify-center hover:bg-gray-100 text-gray-700"
            title="Refresh Markers"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    <!-- Stats -->
    <div class="absolute bottom-4 left-4 z-[1000] bg-white bg-opacity-90 rounded-lg px-3 py-2 text-xs text-gray-600">
        <span>Markers: {{ count($markers) }}</span>
        <span class="mx-2">|</span>
        <span>Center: {{ $centerLat }}, {{ $centerLon }}</span>
    </div>
</div>

@push('styles')
<style>
    .custom-marker {
        background: transparent;
        border: none;
    }
</style>
@endpush
