@section('page_title')
    <span class="material-symbols-outlined notranslate text-[#00e5ff]" translate="no" style="font-size:24px">public</span>
    <span class="text-lg text-[#00e5ff] font-bold">Peta Topologi (Reseller)</span>
@endsection

<div class="space-y-5 pb-10">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .noc-map-container { background-color: #0a0e1a; border: 1px solid rgba(0, 229, 255, 0.3); box-shadow: 0 0 15px rgba(0, 229, 255, 0.1); border-radius: 0.75rem; overflow: hidden; display: flex; flex-direction: column; }
        .noc-map-header { background-color: rgba(10, 14, 26, 0.8); border-bottom: 1px solid rgba(0, 229, 255, 0.2); color: #00e5ff; }
        #reseller-map { height: calc(100vh - 200px); min-height: 500px; width: 100%; z-index: 1; background-color: #0a0e1a; }
        .map-smart-toolbar { background: rgba(17, 24, 39, 0.95); border-bottom: 1px solid rgba(0, 229, 255, 0.2); padding: 0.5rem 1rem; display: flex; gap: 1rem; align-items: center; }
        .legend-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #94a3b8; background: rgba(0,0,0,0.4); padding: 0.25rem 0.75rem; border-radius: 999px; border: 1px solid rgba(255,255,255,0.1); }
        .pulsing-icon { border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px currentColor; }
        .connection-line { filter: drop-shadow(0 0 4px currentColor); }
        .connection-online { animation: dash 30s linear infinite; filter: drop-shadow(0 0 6px rgba(0, 242, 255, 0.8)); }
        @keyframes dash { to { stroke-dashoffset: -1000; } }
        .noc-popup .leaflet-popup-content-wrapper { background: transparent; padding: 0; box-shadow: none; }
        .noc-popup .leaflet-popup-tip { background: #111827; border: 1px solid rgba(255,255,255,0.2); }
        .noc-popup .leaflet-popup-content { margin: 0; }
    </style>
    
    <div class="noc-map-container">
        <div class="px-4 py-3 noc-map-header flex justify-between items-center">
            <h5 class="font-bold m-0 flex items-center gap-2">
                <span class="material-symbols-outlined notranslate" style="font-size:20px" translate="no">satellite_alt</span>
                Peta Satelit Wilayah Anda
            </h5>
            <div class="text-xs text-slate-400">Mode Read-Only</div>
        </div>

        <div class="map-smart-toolbar flex-wrap">
            <div class="legend-item text-blue-400"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_8px_#3b82f6]"></span> OLT ({{ count($olts) }})</div>
            <div class="legend-item text-orange-400"><span class="w-2.5 h-2.5 rounded-full bg-orange-500 shadow-[0_0_8px_#f97316]"></span> ODC ({{ count($odcs) }})</div>
            <div class="legend-item text-emerald-400"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]"></span> ODP ({{ count($odps) }})</div>
            <div class="legend-item text-fuchsia-400"><span class="w-2.5 h-2.5 rounded-full bg-fuchsia-500 shadow-[0_0_8px_#d946ef]"></span> Pelanggan ({{ count($customers) }})</div>
            <div style="flex:1;"></div>
            <button type="button" onclick="location.reload()" class="p-1.5 rounded bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition" title="Refresh">
                <span class="material-symbols-outlined notranslate" style="font-size:18px" translate="no">refresh</span>
            </button>
        </div>

        <div id="reseller-map" wire:ignore></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" data-navigate-track></script>
<script>
(function() {
    let mapInstance = null;
    let initInterval = null;

    function buildMap() {
        const container = document.getElementById('reseller-map');
        if (!container) return;

        // Leaflet belum terload dari CDN?
        if (typeof window.L === 'undefined') {
            if (!initInterval) initInterval = setInterval(buildMap, 200);
            return;
        }
        
        if (initInterval) {
            clearInterval(initInterval);
            initInterval = null;
        }

        // Bersihkan map lama jika SPA back/forward
        if (container._leaflet_id) {
            container._leaflet_id = null;
        }
        if (mapInstance) {
            mapInstance.remove();
            mapInstance = null;
        }
        
        container.innerHTML = "";

        mapInstance = window.L.map('reseller-map').setView([-6.2088, 106.8456], 12);
        
        var googleHybrid = window.L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
            maxZoom: 22,
            subdomains:['mt0','mt1','mt2','mt3'],
            attribution: '&copy; Google'
        });
        var osm = window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        });

        googleHybrid.addTo(mapInstance);
        window.L.control.layers({ "Satelit (Google)": googleHybrid, "Peta Standar (OSM)": osm }).addTo(mapInstance);

        const linesLayer = window.L.layerGroup().addTo(mapInstance);
        const bounds = [];

        function createIcon(color, shadowColor) {
            return window.L.divIcon({
                className: 'custom-icon',
                html: <div class="pulsing-icon" style="background-color:  + color + ; color:  + shadowColor + ; width: 14px; height: 14px;"></div>,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });
        }

        const popupOptions = { className: 'noc-popup' };
        
        const olts = @json($olts);
        const odcs = @json($odcs);
        const odps = @json($odps);
        const customers = @json($customers);

        olts.forEach(item => {
            if(item.latitude && item.longitude) {
                window.L.marker([item.latitude, item.longitude], {icon: createIcon('#3b82f6', '#60a5fa')})
                 .addTo(mapInstance)
                 .bindPopup('<div style="background:#111827;color:#fff;padding:8px;border-radius:6px;border:1px solid #3b82f6;"><b style="color:#60a5fa">OLT:</b> ' + item.name + '<br><b>IP:</b> ' + (item.ip_address||'-') + '</div>', popupOptions);
                bounds.push([item.latitude, item.longitude]);
            }
        });

        odcs.forEach(item => {
            if(item.latitude && item.longitude) {
                window.L.marker([item.latitude, item.longitude], {icon: createIcon('#f97316', '#fb923c')})
                 .addTo(mapInstance)
                 .bindPopup('<div style="background:#111827;color:#fff;padding:8px;border-radius:6px;border:1px solid #f97316;"><b style="color:#fb923c">ODC:</b> ' + item.name + '<br><b>Kode:</b> ' + (item.code||'-') + '</div>', popupOptions);
                bounds.push([item.latitude, item.longitude]);
                
                if(item.olt_id) {
                    var olt = olts.find(o => o.id == item.olt_id);
                    if(olt && olt.latitude && olt.longitude) {
                        window.L.polyline([[olt.latitude, olt.longitude], [item.latitude, item.longitude]], 
                            { color: '#6f42c1', weight: 3, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer);
                    }
                }
            }
        });

        odps.forEach(item => {
            if(item.latitude && item.longitude) {
                window.L.marker([item.latitude, item.longitude], {icon: createIcon('#10b981', '#34d399')})
                 .addTo(mapInstance)
                 .bindPopup('<div style="background:#111827;color:#fff;padding:8px;border-radius:6px;border:1px solid #10b981;"><b style="color:#34d399">ODP:</b> ' + item.name + '<br><b>Port Terpakai:</b> ' + (item.used_port_count||0) + '/' + (item.port_count||0) + '</div>', popupOptions);
                bounds.push([item.latitude, item.longitude]);
                
                if(item.odc_id) {
                    var odc = odcs.find(o => o.id == item.odc_id);
                    if(odc && odc.latitude && odc.longitude) {
                        window.L.polyline([[odc.latitude, odc.longitude], [item.latitude, item.longitude]], 
                            { color: '#fd7e14', weight: 2, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer);
                    }
                }
            }
        });

        customers.forEach(item => {
            if(item.latitude && item.longitude) {
                window.L.marker([item.latitude, item.longitude], {icon: createIcon('#d946ef', '#e879f9')})
                 .addTo(mapInstance)
                 .bindPopup('<div style="background:#111827;color:#fff;padding:8px;border-radius:6px;border:1px solid #d946ef;"><b style="color:#e879f9">Pelanggan:</b> ' + item.name + '<br><b>ID:</b> ' + (item.customer_id||'-') + '</div>', popupOptions);
                bounds.push([item.latitude, item.longitude]);
                
                if(item.odp_id) {
                    var odp = odps.find(o => o.id == item.odp_id);
                    if(odp && odp.latitude && odp.longitude) {
                        var opts = item.is_online ? 
                            { color: '#00f2fff3', weight: 3, opacity: 1.0, dashArray: '4, 8', className: 'connection-online' } : 
                            { color: '#6b7280', weight: 2, opacity: 0.5, dashArray: '2, 6' };
                        window.L.polyline([[odp.latitude, odp.longitude], [item.latitude, item.longitude]], opts).addTo(linesLayer);
                    }
                }
            }
        });

        if (bounds.length > 0) {
            mapInstance.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildMap);
    } else {
        buildMap();
    }
    
    document.addEventListener('livewire:navigated', buildMap);

})();
</script>
