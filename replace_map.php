<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$oldMap = '<div class="h-72 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700">
                      <iframe class="w-full h-full rounded-xl"
                          src="https://maps.google.com/maps?q={{ $customer->latitude }},{{ $customer->longitude }}&z=16&output=embed"
                          frameborder="0" allowfullscreen loading="lazy"></iframe>
                  </div>';

$newMap = '<div class="h-72 bg-slate-100 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 relative" style="z-index: 10;" wire:ignore>
                      <div id="customer-map" class="w-full h-full rounded-xl"></div>
                  </div>';

$content = str_replace($oldMap, $newMap, $content);

$scriptCode = <<<'HTML'

@assets
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endassets

@script
<script>
    let map = null;
    
    function initMap() {
        const mapEl = document.getElementById('customer-map');
        if (!mapEl) return;
        
        if (map !== null) {
            map.invalidateSize();
            return;
        }
        
        let lat = {{ $customer->latitude ?? 0 }};
        let lng = {{ $customer->longitude ?? 0 }};
        
        map = L.map('customer-map').setView([lat, lng], 16);
        
        let osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        });
        
        let googleSat = L.tileLayer('https://mt{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['0', '1', '2', '3']
        });
        
        osm.addTo(map);
        
        L.control.layers({
            "Street (OSM)": osm,
            "Satellite (Google)": googleSat
        }).addTo(map);
        
        L.marker([lat, lng]).addTo(map).bindPopup("{{ $customer->name }}");
    }
    
    initMap();
    
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            setTimeout(() => {
                initMap();
            }, 50);
        });
    });
</script>
@endscript
HTML;

$content = preg_replace('/(<\/div>\s*)$/s', "$scriptCode\n$1", $content);
file_put_contents($file, $content);
echo "Successfully replaced iframe with Leaflet map.";
?>
