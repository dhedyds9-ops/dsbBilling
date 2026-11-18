@section('page_title')
    <span class="material-symbols-outlined notranslate text-[#00e5ff]" translate="no" style="font-size:24px">public</span>
    <span class="text-lg text-[#00e5ff] font-bold">Peta Jaringan</span>
@endsection

<div class="h-[calc(100vh-100px)] w-full flex flex-col" style="min-height:600px;">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .map-smart-toolbar { display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px); border-bottom: 1px solid rgba(0, 229, 255, 0.2); overflow-x: auto; }
        .toolbar-group { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
        .tb-btn { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 20px; border: 1px solid transparent; cursor: pointer; transition: all 0.2s; color: white; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; flex-shrink: 0; }
        .tb-icon-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; color: white; }
        .tb-olt { background: linear-gradient(135deg, #1e3a8a, #3b82f6); border-color: #60a5fa; box-shadow: 0 0 10px rgba(59, 130, 246, 0.4); }
        .tb-odc { background: linear-gradient(135deg, #78350f, #f59e0b); border-color: #fbbf24; box-shadow: 0 0 10px rgba(245, 158, 11, 0.4); color: #fff; }
        .tb-odp { background: linear-gradient(135deg, #064e3b, #10b981); border-color: #34d399; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
        .tb-pelanggan { background: linear-gradient(135deg, #831843, #d946ef); border-color: #f0abfc; box-shadow: 0 0 10px rgba(217, 70, 239, 0.4); }
        .tb-refresh { background: rgba(6, 182, 212, 0.2); color: #06b6d4; border-color: rgba(6, 182, 212, 0.5); }
        .tb-refresh:hover { background: #06b6d4; color: white; }
        .tb-full { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.5); }
        .tb-full:hover { background: #cbd5e1; color: #0f172a; }
        .connection-line { filter: drop-shadow(0 0 4px currentColor); }
        .connection-online { animation: dash 30s linear infinite; filter: drop-shadow(0 0 6px rgba(0, 242, 255, 0.8)); }
        @keyframes dash { to { stroke-dashoffset: -1000; } }

        /* Unified Modern Map Popup Styling */
        .modern-map-popup .leaflet-popup-content-wrapper {
            background: #0f172a !important;
            padding: 0 !important;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5), 0 8px 10px -6px rgb(0 0 0 / 0.5) !important;
            border: 1px solid #1e293b !important;
            border-radius: 12px !important;
        }
        .modern-map-popup .leaflet-popup-content {
            margin: 0 !important;
            width: 300px !important;
        }
        .modern-map-popup .leaflet-popup-tip {
            background: #0f172a !important;
        }
        .modern-map-popup .leaflet-popup-close-button {
            color: #94a3b8 !important;
            padding: 10px 10px 0 0 !important;
            font-size: 18px !important;
            z-index: 100 !important;
        }
        .modern-map-popup .leaflet-popup-close-button:hover {
            color: #ffffff !important;
            background: transparent !important;
        }

        .network-card, .customer-card {
            width: 100%;
            padding: 0;
            color: #cbd5e1;
            font-family: "Inter", "Segoe UI", Tahoma, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            background: #0f172a;
            border-radius: 12px;
            overflow: hidden;
        }
        .network-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid #1e293b;
            background: rgba(30, 41, 59, 0.5);
        }
        .network-header-left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 1;
        }
        .network-title-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .network-title {
            font-size: 14px;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }
        .network-subtitle {
            font-size: 10px;
            color: #94a3b8;
            font-family: "Consolas", monospace;
        }
        .network-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .badge-olt { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: #fff; border: 1px solid #60a5fa; }
        .badge-odc { background: linear-gradient(135deg, #78350f, #f59e0b); color: #fff; border: 1px solid #fbbf24; }
        .badge-odp { background: linear-gradient(135deg, #064e3b, #10b981); color: #fff; border: 1px solid #34d399; }
        .badge-closure { background: #334155; color: #cbd5e1; border: 1px solid #64748b; }
        .badge-onu-online { background: #065f46; color: #34d399; border: 1px solid #10b981; }
        .badge-onu-offline { background: #7f1d1d; color: #f87171; border: 1px solid #ef4444; }

        .network-content {
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .node-panel {
            background: rgba(30, 41, 59, 0.45);
            border: 1px solid rgba(71, 85, 105, 0.4);
            border-radius: 8px;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .node-kv {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 11px;
        }
        .node-kv-label {
            color: #94a3b8;
            font-weight: 500;
            white-space: nowrap;
        }
        .node-kv-value {
            color: #f1f5f9;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }
        .node-progress-bg {
            width: 100%;
            height: 6px;
            background: #1e293b;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 3px;
            margin-bottom: 2px;
        }
        .node-progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width 0.3s;
        }
        .node-actions {
            display: flex;
            gap: 6px;
        }
        .node-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none !important;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .node-btn-primary { background: #2563eb; color: #fff; }
        .node-btn-primary:hover { background: #1d4ed8; }
        .node-btn-secondary { background: #1e293b; color: #cbd5e1; border-color: #334155; }
        .node-btn-secondary:hover { background: #334155; color: #fff; }
        .node-btn-success { background: #059669; color: #fff; }
        .node-btn-success:hover { background: #047857; }

        /* Unified Smart Map Icons (Matching Admin GIS) */
        .custom-icon {
            display: flex; align-items: center; justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .custom-icon i { filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); pointer-events: none; }
        .custom-icon:hover { transform: scale(1.15); }

        .custom-icon.icon-olt { 
            background: radial-gradient(circle, #3b82f6 0%, #1e3a8a 100%);
            border: 2px solid #93c5fd; 
            border-radius: 50%;
            box-shadow: 0 0 15px #3b82f6, inset 0 0 10px rgba(255,255,255,0.5);
        }
        .custom-icon.icon-odc { 
            background: linear-gradient(135deg, #f59e0b 0%, #92400e 100%);
            border: 2px solid #fde68a; 
            border-radius: 8px;
            transform: rotate(45deg);
            box-shadow: 0 0 15px #f59e0b;
        }
        .custom-icon.icon-odc i { transform: rotate(-45deg); }
        .custom-icon.icon-odp { 
            background: linear-gradient(to bottom right, #10b981, #065f46);
            border: 2px solid #a7f3d0; 
            border-radius: 3px;
            box-shadow: 0 0 12px #10b981;
        }
        .custom-icon.icon-closure { 
            background: #334155; border: 2px solid #94a3b8; border-radius: 12px 12px 4px 4px; 
        }
        .custom-icon.icon-customer-online { 
            background: #06b6d4; border: 2px solid #cffafe; 
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 0 10px #06b6d4;
        }
        .custom-icon.icon-customer-online i { transform: rotate(45deg); }
        .custom-icon.icon-customer-offline { 
            background: #ef4444; border: 2px solid #fecaca; color: white;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 0 10px #ef4444;
        }
        .custom-icon.icon-customer-offline i { transform: rotate(45deg); }
    </style>
    
    <div class="bg-[#111827] border border-[#00e5ff]/30 rounded-lg shadow-lg flex-1 flex flex-col overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-800 flex justify-between items-center bg-[#0a0e1a]/50">
            <h5 class="text-[#00e5ff] font-bold m-0 flex items-center">
                <i class="fa fa-map mr-2"></i> Peta Jaringan
            </h5>
            <div class="text-xs text-slate-400">Wilayah Kemitraan Reseller</div>
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
                <button type="button" onclick="document.getElementById('reseller-map').parentElement.requestFullscreen()" class="tb-icon-btn tb-full" title="Layar Penuh"><i class="fa fa-expand"></i></button>
                <button type="button" onclick="location.reload()" class="tb-icon-btn tb-refresh" title="Segarkan"><i class="fa fa-refresh"></i></button>
            </div>
        </div>

        <div id="reseller-map" class="flex-1 w-full bg-[#0a0e1a]" style="min-height:550px; height:100%; width:100%;" wire:ignore></div>
    </div>
</div>

<script>
(function() {
    window.mapData = {
        olts: @json($olts),
        odcs: @json($odcs),
        closures: @json($closures ?? []),
        odps: @json($odps),
        customers: @json($customers)
    };

    let mapInstance = null;

    function createUnifiedIcon(type) {
        let iconClass = 'fa-wifi';
        let colorClass = 'icon-customer-offline';
        let size = 26;

        if (type === 'olt') { iconClass = 'fa-server'; colorClass = 'icon-olt'; size = 36; }
        else if (type === 'odc') { iconClass = 'fa-hdd'; colorClass = 'icon-odc'; size = 32; }
        else if (type === 'odp') { iconClass = 'fa-box'; colorClass = 'icon-odp'; size = 28; }
        else if (type === 'closure') { iconClass = 'fa-archive'; colorClass = 'icon-closure'; size = 26; }
        else if (type === 'online') { iconClass = 'fa-wifi'; colorClass = 'icon-customer-online'; size = 26; }
        else { iconClass = 'fa-times'; colorClass = 'icon-customer-offline'; size = 26; }

        return window.L.divIcon({
            html: `<i class="fa ${iconClass}" style="font-size: ${Math.round(size/1.6)}px;"></i>`,
            className: `custom-icon ${colorClass}`,
            iconSize: [size, size],
            iconAnchor: [size/2, size/2],
            popupAnchor: [0, -size/2]
        });
    }

    function initResellerMap() {
        const container = document.getElementById('reseller-map');
        if (!container) return;
        
        if (typeof window.L === 'undefined') {
            setTimeout(initResellerMap, 200);
            return;
        }

        if (container._leaflet_id) { container._leaflet_id = null; }
        if (mapInstance) { mapInstance.remove(); mapInstance = null; }
        container.innerHTML = "";

        mapInstance = window.L.map('reseller-map').setView([-6.2088, 106.8456], 12);
        
        let googleHybrid = window.L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 22, subdomains:['mt0','mt1','mt2','mt3'], attribution: '&copy; Google Hybrid Satellite HD' });
        let googleRoad = window.L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 22, subdomains:['mt0','mt1','mt2','mt3'], attribution: '&copy; Google Road Clean HD' });
        let darkLayer = window.L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20, attribution: 'CARTO' });
        
        googleHybrid.addTo(mapInstance);
        window.L.control.layers({ '🛰️ Google Hybrid Satellite HD': googleHybrid, '🗺️ Google Road Clean HD': googleRoad, '🌙 Dark Mode': darkLayer }).addTo(mapInstance);
        
        let linesLayer = window.L.layerGroup().addTo(mapInstance);
        let markersLayer = window.L.layerGroup().addTo(mapInstance);
        let bounds = [];
        const popupOpt = { maxWidth: 320, minWidth: 280, className: 'modern-map-popup' };
        
        const olts = window.mapData.olts || [];
        const odcs = window.mapData.odcs || [];
        const closures = window.mapData.closures || [];
        const odps = window.mapData.odps || [];
        const customers = window.mapData.customers || [];
        
        // 1. Render OLT
        olts.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon('olt');
                let photoHtml = (i.photos && i.photos.length > 0) ? `<div style="margin-bottom:6px;"><img src="${i.photos[0]}" style="width:100%;height:85px;object-fit:cover;border-radius:6px;border:1px solid #334155;"></div>` : '';
                let cardHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <div class="network-title-group">
                                    <div class="network-title" title="${i.name}">${i.name}</div>
                                    <div class="network-subtitle">${i.code || 'OLT-CORE'}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-olt">OLT</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">IP Host:</span>
                                    <span class="node-kv-value font-mono text-cyan-400">${i.ip_address || '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Port PON:</span>
                                    <span class="node-kv-value text-blue-300 font-bold">${i.pon_port_count || i.port_count || '-'} Port</span>
                                </div>
                            </div>
                        </div>
                    </div>`;
                let m = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(cardHtml, popupOpt);
                m.on('click', function() { this.openPopup(); });
                bounds.push([i.latitude, i.longitude]);
            }
        });
        
        // 2. Render ODC
        odcs.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon('odc');
                let photoHtml = (i.photos && i.photos.length > 0) ? `<div style="margin-bottom:6px;"><img src="${i.photos[0]}" style="width:100%;height:85px;object-fit:cover;border-radius:6px;border:1px solid #334155;"></div>` : '';
                let cardHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <div class="network-title-group">
                                    <div class="network-title" title="${i.name}">${i.name}</div>
                                    <div class="network-subtitle">${i.code || 'ODC'}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-odc">ODC</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Kapasitas:</span>
                                    <span class="node-kv-value text-amber-300 font-bold">${i.capacity || 48} Port</span>
                                </div>
                            </div>
                        </div>
                    </div>`;
                let m = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(cardHtml, popupOpt);
                m.on('click', function() { this.openPopup(); });
                bounds.push([i.latitude, i.longitude]);
                if(i.olt_id) {
                    let olt = olts.find(o => o.id == i.olt_id);
                    if(olt && olt.latitude && olt.longitude) { window.L.polyline([[olt.latitude, olt.longitude], [i.latitude, i.longitude]], { color: '#6f42c1', weight: 3, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer); }
                }
            }
        });

        // 3. Render Closures
        closures.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon('closure');
                let photoHtml = (i.photos && i.photos.length > 0) ? `<div style="margin-bottom:6px;"><img src="${i.photos[0]}" style="width:100%;height:85px;object-fit:cover;border-radius:6px;border:1px solid #334155;"></div>` : '';
                let cardHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <div class="network-title-group">
                                    <div class="network-title" title="${i.name}">${i.name}</div>
                                    <div class="network-subtitle">${i.code || 'CLOSURE'}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-closure">CLOSURE</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Tipe:</span>
                                    <span class="node-kv-value">${i.type || 'Dome'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Kapasitas Core:</span>
                                    <span class="node-kv-value text-teal-300 font-bold">${i.port_count || 24} Core</span>
                                </div>
                            </div>
                        </div>
                    </div>`;
                let m = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(cardHtml, popupOpt);
                m.on('click', function() { this.openPopup(); });
                bounds.push([i.latitude, i.longitude]);
                if(i.odc_id) {
                    let odc = odcs.find(o => o.id == i.odc_id);
                    if(odc && odc.latitude && odc.longitude) { window.L.polyline([[odc.latitude, odc.longitude], [i.latitude, i.longitude]], { color: '#0d9488', weight: 2.5, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer); }
                }
            }
        });
        
        // 4. Render ODP
        odps.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon('odp');
                let totalP = parseInt(i.port_count) || 8;
                let usedP = parseInt(i.used_port_count) || 0;
                let availP = Math.max(0, totalP - usedP);
                let pct = Math.min(100, Math.round((usedP / totalP) * 100));
                let photoHtml = (i.photos && i.photos.length > 0) ? `<div style="margin-bottom:6px;"><img src="${i.photos[0]}" style="width:100%;height:85px;object-fit:cover;border-radius:6px;border:1px solid #334155;"></div>` : '';
                let barColor = pct > 90 ? '#ef4444' : (pct > 70 ? '#f59e0b' : '#10b981');
                let cardHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <div class="network-title-group">
                                    <div class="network-title" title="${i.name}">${i.name}</div>
                                    <div class="network-subtitle">${i.code}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-odp">ODP</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Port Terpakai:</span>
                                    <span class="node-kv-value">${usedP} / ${totalP} (${pct}%)</span>
                                </div>
                                <div class="node-progress-bg">
                                    <div class="node-progress-bar" style="width: ${pct}%; background: ${barColor};"></div>
                                </div>
                                <div class="node-kv" style="margin-top:4px;">
                                    <span class="node-kv-label">Tersedia:</span>
                                    <span class="node-kv-value text-emerald-400 font-bold">${availP} Port Kosong</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <a href="https://www.google.com/maps/dir/?api=1&destination=${i.latitude},${i.longitude}" target="_blank" class="node-btn node-btn-secondary" style="width:100%;">
                                    <i class="fa fa-map-marker-alt text-rose-400"></i> Rute Maps
                                </a>
                            </div>
                        </div>
                    </div>`;
                let m = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(cardHtml, popupOpt);
                m.on('click', function() { this.openPopup(); });
                bounds.push([i.latitude, i.longitude]);
                if(i.odc_id) {
                    let odc = odcs.find(o => o.id == i.odc_id);
                    if(odc && odc.latitude && odc.longitude) { window.L.polyline([[odc.latitude, odc.longitude], [i.latitude, i.longitude]], { color: '#fd7e14', weight: 2, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer); }
                }
            }
        });
        
        // 5. Render Pelanggan (ONU)
        customers.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon(i.is_online ? 'online' : 'offline');
                let waNumber = (i.phone || '').replace(/[^0-9]/g, '');
                if (waNumber.startsWith('0')) { waNumber = '62' + waNumber.substring(1); }
                let cardHtml = `
                    <div class="customer-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <div class="network-title-group">
                                    <div class="network-title" title="${i.name}">${i.name}</div>
                                    <div class="network-subtitle">ID: ${i.code || i.id}</div>
                                </div>
                            </div>
                            <span class="network-badge ${i.is_online ? 'badge-onu-online' : 'badge-onu-offline'}">
                                ${i.is_online ? 'ONLINE' : 'OFFLINE'}
                            </span>
                        </div>
                        <div class="network-content">
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Layanan:</span>
                                    <span class="node-kv-value font-bold text-cyan-400">${i.service_type || 'Internet'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">WiFi SSID:</span>
                                    <span class="node-kv-value font-mono text-amber-300">${i.ssid_name || '-'}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Telepon:</span>
                                    <span class="node-kv-value font-mono">${i.phone || '-'}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                ${waNumber ? `<a href="https://wa.me/${waNumber}" target="_blank" class="node-btn node-btn-success"><i class="fab fa-whatsapp"></i> Chat WA</a>` : ''}
                                <a href="https://www.google.com/maps/dir/?api=1&destination=${i.latitude},${i.longitude}" target="_blank" class="node-btn node-btn-secondary">
                                    <i class="fa fa-map-marker-alt text-rose-400"></i> Maps
                                </a>
                            </div>
                        </div>
                    </div>`;
                let m = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(cardHtml, popupOpt);
                m.on('click', function() { this.openPopup(); });
                bounds.push([i.latitude, i.longitude]);
                if(i.odp_id) {
                    let odp = odps.find(o => o.id == i.odp_id);
                    if(odp && odp.latitude && odp.longitude) {
                        let opts = i.is_online ? { color: '#00f2fff3', weight: 3, opacity: 1.0, dashArray: '4, 8', className: 'connection-online' } : { color: '#6b7280', weight: 2, opacity: 0.5, dashArray: '2, 6' };
                        window.L.polyline([[odp.latitude, odp.longitude], [i.latitude, i.longitude]], opts).addTo(linesLayer);
                    }
                }
            }
        });
        
        if (bounds.length > 0) { 
            mapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 17 }); 
        }
        
        setTimeout(() => { if(mapInstance) mapInstance.invalidateSize(); }, 250);
        setTimeout(() => { if(mapInstance) mapInstance.invalidateSize(); }, 800);
        window.addEventListener('resize', () => { if(mapInstance) mapInstance.invalidateSize(); });
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initResellerMap); } 
    else { initResellerMap(); }
    document.addEventListener('livewire:navigated', initResellerMap);
})();
</script>
