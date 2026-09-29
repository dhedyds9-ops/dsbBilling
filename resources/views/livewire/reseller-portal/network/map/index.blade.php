@section('page_title')
    <span class="material-symbols-outlined notranslate text-[#00e5ff]" translate="no" style="font-size:24px">public</span>
    <span class="text-lg text-[#00e5ff] font-bold">Peta Topologi (Reseller)</span>
@endsection

<div class="h-[calc(100vh-100px)] w-full flex flex-col" style="min-height:600px;">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .map-smart-toolbar {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(0, 229, 255, 0.2);
        }
        .toolbar-group { display: flex; gap: 8px; align-items: center; }
        
        .tb-btn {
            display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;
            padding: 6px 14px; border-radius: 20px; border: 1px solid transparent;
            cursor: pointer; transition: all 0.2s; color: white; text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tb-icon-btn {
            width: 32px; height: 32px; border-radius: 8px; border: 1px solid transparent;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: all 0.2s; color: white;
        }
        .tb-olt { background: linear-gradient(135deg, #1e3a8a, #3b82f6); border-color: #60a5fa; box-shadow: 0 0 10px rgba(59, 130, 246, 0.4); }
        .tb-olt:hover { box-shadow: 0 0 15px rgba(59, 130, 246, 0.8); transform: translateY(-1px); }
        
        .tb-odc { background: linear-gradient(135deg, #78350f, #f59e0b); border-color: #fbbf24; box-shadow: 0 0 10px rgba(245, 158, 11, 0.4); color: #fff; }
        .tb-odc:hover { box-shadow: 0 0 15px rgba(245, 158, 11, 0.8); transform: translateY(-1px); }
        
        .tb-odp { background: linear-gradient(135deg, #064e3b, #10b981); border-color: #34d399; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
        .tb-odp:hover { box-shadow: 0 0 15px rgba(16, 185, 129, 0.8); transform: translateY(-1px); }
        
        .tb-htb { background: linear-gradient(135deg, #4c1d95, #8b5cf6); border-color: #a78bfa; box-shadow: 0 0 10px rgba(139, 92, 246, 0.4); }
        .tb-pelanggan { background: linear-gradient(135deg, #831843, #d946ef); border-color: #f0abfc; box-shadow: 0 0 10px rgba(217, 70, 239, 0.4); }
        
        .tb-refresh { background: rgba(6, 182, 212, 0.2); color: #06b6d4; border-color: rgba(6, 182, 212, 0.5); }
        .tb-refresh:hover { background: #06b6d4; color: white; }
        .tb-full { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.5); }
        .tb-full:hover { background: #cbd5e1; color: #0f172a; }

        .connection-line { filter: drop-shadow(0 0 4px currentColor); }
        .connection-online { animation: dash 30s linear infinite; filter: drop-shadow(0 0 6px rgba(0, 242, 255, 0.8)); }
        @keyframes dash { to { stroke-dashoffset: -1000; } }
        
        /* Modern Map Popup */
        .modern-map-popup .leaflet-popup-content-wrapper {
            background: #0f172a !important; padding: 0 !important; border: 1px solid #1e293b !important; border-radius: 12px !important;
        }
        .modern-map-popup .leaflet-popup-tip { background: #0f172a !important; border: 1px solid #1e293b !important; }
        .modern-map-popup .leaflet-popup-content { margin: 0 !important; width: auto !important; min-width: 200px; padding: 12px !important; color: #cbd5e1;}
        .modern-map-popup .leaflet-popup-close-button { color: #94a3b8 !important; padding: 10px 10px 0 0 !important; }
        
        /* Custom Icons (matching NOC Map) */
        .custom-icon {
            display: flex; align-items: center; justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transition: transform 0.2s, box-shadow 0.2s;
            z-index: 500 !important;
        }
        .custom-icon i { filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); font-size: 14px; }
        .icon-olt { background: linear-gradient(135deg, #1e3a8a, #3b82f6); border-radius: 6px; border: 2px solid #60a5fa; }
        .icon-odc { background: linear-gradient(135deg, #78350f, #f59e0b); border-radius: 50%; border: 2px solid #fbbf24; }
        .icon-odp { background: linear-gradient(135deg, #064e3b, #10b981); border-radius: 4px; border: 2px solid #34d399; }
        .icon-htb { background: linear-gradient(135deg, #4c1d95, #8b5cf6); border-radius: 50%; border: 2px solid #a78bfa; }
        .icon-customer { background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 50%; border: 2px solid #cbd5e1; }
        .icon-customer-online { border-color: #22c55e; box-shadow: 0 0 10px #22c55e; }
        .icon-customer-offline { border-color: #ef4444; box-shadow: 0 0 10px #ef4444; }
    </style>
    
    <div class="bg-[#111827] border border-[#00e5ff]/30 rounded-lg shadow-lg flex-1 flex flex-col overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-800 flex justify-between items-center bg-[#0a0e1a]/50">
            <h5 class="text-[#00e5ff] font-bold m-0 flex items-center">
                <i class="fa fa-map mr-2"></i> Peta Distribusi & Topologi (Wilayah Reseller)
            </h5>
            <div class="text-xs text-slate-400">Read-Only Mode</div>
        </div>

        <div class="map-smart-toolbar">
            <div class="toolbar-group">
                <button type="button" class="tb-btn tb-olt cursor-default"><i class="fa fa-server"></i> OLT ({{ count($olts) }})</button>
                <button type="button" class="tb-btn tb-odc cursor-default"><i class="fa fa-hdd"></i> ODC ({{ count($odcs) }})</button>
                <button type="button" class="tb-btn tb-odp cursor-default"><i class="fa fa-box"></i> ODP ({{ count($odps) }})</button>
                <button type="button" class="tb-btn tb-pelanggan cursor-default"><i class="fa fa-users"></i> Pelanggan ({{ count($customers) }})</button>
            </div>
            <div style="flex:1;"></div>
            <div class="toolbar-group">
                <button type="button" id="btnFullscreen" class="tb-icon-btn tb-full" title="Layar Penuh"><i class="fa fa-expand"></i></button>
                <button type="button" onclick="location.reload()" class="tb-icon-btn tb-refresh" title="Segarkan"><i class="fa fa-refresh"></i></button>
            </div>
        </div>

        <div id="reseller-map" class="flex-1 w-full bg-[#0a0e1a]" wire:ignore></div>
    </div>
</div>

<script>
(function() {
    let mapInstance = null;

    function loadLeaflet(callback) {
        let cssLoaded = false;
        let jsLoaded = false;

        if (!document.getElementById('leaflet-css')) {
            let link = document.createElement('link');
            link.id = 'leaflet-css'; link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
            cssLoaded = true;
        } else { cssLoaded = true; }

        if (typeof window.L === 'undefined') {
            if (!document.getElementById('leaflet-js')) {
                let script = document.createElement('script');
                script.id = 'leaflet-js'; script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => { jsLoaded = true; if (cssLoaded && jsLoaded) callback(); };
                document.head.appendChild(script);
            } else {
                let check = setInterval(() => {
                    if (typeof window.L !== 'undefined') { clearInterval(check); callback(); }
                }, 100);
            }
        } else { callback(); }
    }

    function buildMap() {
        const container = document.getElementById('reseller-map');
        if (!container) return;

        if (container._leaflet_id) { container._leaflet_id = null; }
        if (mapInstance) { mapInstance.remove(); mapInstance = null; }
        container.innerHTML = "";

        mapInstance = window.L.map('reseller-map').setView([-6.2088, 106.8456], 12);
        
        var googleHybrid = window.L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
            maxZoom: 22, subdomains:['mt0','mt1','mt2','mt3'], attribution: '&copy; Google'
        });
        var osm = window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap'
        });

        googleHybrid.addTo(mapInstance);
        window.L.control.layers({ "Satelit (Google)": googleHybrid, "Peta Standar (OSM)": osm }).addTo(mapInstance);

        const linesLayer = window.L.layerGroup().addTo(mapInstance);
        const bounds = [];
        const popupOptions = { className: 'modern-map-popup' };

        const olts = @json($olts);
        const odcs = @json($odcs);
        const odps = @json($odps);
        const customers = @json($customers);

        olts.forEach(item => {
            if(item.latitude && item.longitude) {
                const icon = window.L.divIcon({ className: 'custom-icon icon-olt', html: '<i class="fa fa-server"></i>', iconSize: [28,28] });
                window.L.marker([item.latitude, item.longitude], {icon: icon})
                 .addTo(mapInstance).bindPopup(<h6 class="font-bold text-[#60a5fa] border-b border-gray-700 pb-2 mb-2">OLT: </h6>IP: , popupOptions);
                bounds.push([item.latitude, item.longitude]);
            }
        });

        odcs.forEach(item => {
            if(item.latitude && item.longitude) {
                const icon = window.L.divIcon({ className: 'custom-icon icon-odc', html: '<i class="fa fa-hdd"></i>', iconSize: [24,24] });
                window.L.marker([item.latitude, item.longitude], {icon: icon})
                 .addTo(mapInstance).bindPopup(<h6 class="font-bold text-[#fbbf24] border-b border-gray-700 pb-2 mb-2">ODC: </h6>Kode: , popupOptions);
                bounds.push([item.latitude, item.longitude]);
                if(item.olt_id) {
                    var olt = olts.find(o => o.id == item.olt_id);
                    if(olt && olt.latitude && olt.longitude) {
                        window.L.polyline([[olt.latitude, olt.longitude], [item.latitude, item.longitude]], { color: '#6f42c1', weight: 3, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer);
                    }
                }
            }
        });

        odps.forEach(item => {
            if(item.latitude && item.longitude) {
                const icon = window.L.divIcon({ className: 'custom-icon icon-odp', html: '<i class="fa fa-box"></i>', iconSize: [20,20] });
                window.L.marker([item.latitude, item.longitude], {icon: icon})
                 .addTo(mapInstance).bindPopup(<h6 class="font-bold text-[#34d399] border-b border-gray-700 pb-2 mb-2">ODP: </h6>Port: /, popupOptions);
                bounds.push([item.latitude, item.longitude]);
                if(item.odc_id) {
                    var odc = odcs.find(o => o.id == item.odc_id);
                    if(odc && odc.latitude && odc.longitude) {
                        window.L.polyline([[odc.latitude, odc.longitude], [item.latitude, item.longitude]], { color: '#fd7e14', weight: 2, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer);
                    }
                }
            }
        });

        customers.forEach(item => {
            if(item.latitude && item.longitude) {
                let statusClass = item.is_online ? 'icon-customer-online' : 'icon-customer-offline';
                let htmlIcon = item.is_online ? '<i class="fa fa-wifi text-green-400"></i>' : '<i class="fa fa-times text-red-400"></i>';
                const icon = window.L.divIcon({ className: custom-icon icon-customer , html: htmlIcon, iconSize: [22,22] });
                window.L.marker([item.latitude, item.longitude], {icon: icon})
                 .addTo(mapInstance).bindPopup(<h6 class="font-bold text-[#cbd5e1] border-b border-gray-700 pb-2 mb-2">Pelanggan: </h6>ID: <br>Status: , popupOptions);
                bounds.push([item.latitude, item.longitude]);
                if(item.odp_id) {
                    var odp = odps.find(o => o.id == item.odp_id);
                    if(odp && odp.latitude && odp.longitude) {
                        var opts = item.is_online ? { color: '#00f2fff3', weight: 3, opacity: 1.0, dashArray: '4, 8', className: 'connection-online' } : { color: '#6b7280', weight: 2, opacity: 0.5, dashArray: '2, 6' };
                        window.L.polyline([[odp.latitude, odp.longitude], [item.latitude, item.longitude]], opts).addTo(linesLayer);
                    }
                }
            }
        });

        if (bounds.length > 0) { mapInstance.fitBounds(bounds, { padding: [50, 50] }); }

        // Fullscreen logic
        document.getElementById('btnFullscreen').onclick = function() {
            var mapContainer = document.getElementById('reseller-map').parentElement;
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen().catch(err => { alert('Fullscreen error'); });
            } else {
                document.exitFullscreen();
            }
        };
    }

    function initAll() { loadLeaflet(buildMap); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initAll); } 
    else { initAll(); }
    document.addEventListener('livewire:navigated', initAll);
})();
</script>
