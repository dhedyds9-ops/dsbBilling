@section('page_title')
    <span class="material-symbols-outlined notranslate text-[#00e5ff]" translate="no" style="font-size:24px">public</span>
    <span class="text-lg text-[#00e5ff] font-bold">Peta Jaringan</span>
@endsection

<div class="h-[calc(100vh-100px)] w-full flex flex-col relative" style="min-height:600px;" x-data="{ clickToAddMode: false, clickAddType: 'odp' }">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/leaflet-ant-path@1.3.0/dist/leaflet-ant-path.min.js"></script>

    <style>
        .map-tiles-dark { filter: invert(100%) hue-rotate(180deg) brightness(85%) contrast(90%) !important; }
        .map-smart-toolbar { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            padding: 10px 16px; 
            background: rgba(15, 23, 42, 0.95); 
            backdrop-filter: blur(8px); 
            border-bottom: 1px solid rgba(0, 229, 255, 0.2); 
            overflow-x: auto; 
            flex-wrap: nowrap;
        }
        .toolbar-group { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
        .tb-btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            font-size: 12px; 
            font-weight: 600; 
            padding: 6px 14px; 
            border-radius: 20px; 
            border: 1px solid transparent; 
            transition: all 0.2s; 
            color: white; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            white-space: nowrap; 
            flex-shrink: 0; 
        }
        .tb-btn-action {
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .tb-btn-action:hover {
            transform: translateY(-1px);
            filter: brightness(1.15);
        }
        .tb-icon-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; color: white; }
        .tb-olt { background: linear-gradient(135deg, #1e3a8a, #3b82f6); border-color: #60a5fa; box-shadow: 0 0 10px rgba(59, 130, 246, 0.4); }
        .tb-odc { background: linear-gradient(135deg, #78350f, #f59e0b); border-color: #fbbf24; box-shadow: 0 0 10px rgba(245, 158, 11, 0.4); color: #fff; }
        .tb-odp { background: linear-gradient(135deg, #064e3b, #10b981); border-color: #34d399; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
        .tb-pelanggan { background: linear-gradient(135deg, #831843, #d946ef); border-color: #f0abfc; box-shadow: 0 0 10px rgba(217, 70, 239, 0.4); }
        .tb-add-odp { background: linear-gradient(135deg, #059669, #10b981); border-color: #34d399; font-weight: 700; }
        .tb-add-odc { background: linear-gradient(135deg, #d97706, #f59e0b); border-color: #fbbf24; font-weight: 700; }
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
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.7), 0 8px 10px -6px rgb(0 0 0 / 0.7) !important;
            border: 1px solid #334155 !important;
            border-radius: 12px !important;
        }
        .modern-map-popup .leaflet-popup-content {
            margin: 0 !important;
            width: 310px !important;
        }
        .modern-map-popup .leaflet-popup-tip {
            background: #0f172a !important;
        }
        .modern-map-popup .leaflet-popup-close-button {
            color: #94a3b8 !important;
            padding: 10px 10px 0 0 !important;
            font-size: 20px !important;
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
            background: rgba(30, 41, 59, 0.7);
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
            max-width: 175px;
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
            background: rgba(30, 41, 59, 0.5);
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
            margin-top: 4px;
        }
        .node-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none !important;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .node-btn-primary { background: #2563eb; color: #fff; }
        .node-btn-primary:hover { background: #1d4ed8; filter: brightness(1.1); }
        .node-btn-warning { background: #d97706; color: #fff; }
        .node-btn-warning:hover { background: #b45309; filter: brightness(1.1); }
        .node-btn-secondary { background: #1e293b; color: #cbd5e1; border-color: #334155; }
        .node-btn-secondary:hover { background: #334155; color: #fff; }
        .node-btn-success { background: #059669; color: #fff; }
        .node-btn-success:hover { background: #047857; }

        /* Unified Smart Map Icons - ISOLATED INNER BADGE (NO LEAFLET TRANSFORM COLLISION) */
        .custom-icon {
            display: flex; 
            align-items: center; 
            justify-content: center;
            cursor: pointer !important;
            pointer-events: auto !important;
            background: transparent !important;
            border: none !important;
            transition: filter 0.2s;
        }
        .custom-icon:hover {
            filter: drop-shadow(0 0 10px rgba(0, 229, 255, 0.9));
            z-index: 1000 !important;
        }
        .custom-icon .icon-badge {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transition: transform 0.2s, box-shadow 0.2s;
            pointer-events: auto;
        }
        .custom-icon:hover .icon-badge {
            transform: scale(1.18);
        }
        .custom-icon i { 
            filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); 
            pointer-events: none; 
        }

        .icon-olt .icon-badge { 
            background: radial-gradient(circle, #3b82f6 0%, #1e3a8a 100%);
            border: 2px solid #93c5fd; 
            border-radius: 50%;
            box-shadow: 0 0 15px #3b82f6, inset 0 0 10px rgba(255,255,255,0.5);
        }
        .icon-odc .icon-badge { 
            background: linear-gradient(135deg, #f59e0b 0%, #92400e 100%);
            border: 2px solid #fde68a; 
            border-radius: 8px;
            transform: rotate(45deg);
            box-shadow: 0 0 15px #f59e0b;
        }
        .icon-odc .icon-badge i { 
            transform: rotate(-45deg); 
        }
        .custom-icon.icon-odc:hover .icon-badge {
            transform: rotate(45deg) scale(1.18);
        }
        .icon-odp .icon-badge { 
            background: linear-gradient(to bottom right, #10b981, #065f46);
            border: 2px solid #a7f3d0; 
            border-radius: 4px;
            box-shadow: 0 0 12px #10b981;
        }
        .icon-closure .icon-badge { 
            background: #334155; 
            border: 2px solid #94a3b8; 
            border-radius: 12px 12px 4px 4px; 
        }
        .icon-customer-online .icon-badge { 
            background: #06b6d4; 
            border: 2px solid #cffafe; 
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 0 10px #06b6d4;
        }
        .icon-customer-online .icon-badge i { 
            transform: rotate(45deg); 
        }
        .custom-icon.icon-customer-online:hover .icon-badge {
            transform: rotate(-45deg) scale(1.18);
        }
        .icon-customer-offline .icon-badge { 
            background: #ef4444; 
            border: 2px solid #fecaca; 
            color: white;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 0 10px #ef4444;
        }
        .icon-customer-offline .icon-badge i { 
            transform: rotate(45deg); 
        }
        .custom-icon.icon-customer-offline:hover .icon-badge {
            transform: rotate(-45deg) scale(1.18);
        }
    </style>
    
    <div class="bg-[#111827] border border-[#00e5ff]/30 rounded-lg shadow-lg flex-1 flex flex-col overflow-hidden">
        {{-- Header Bar --}}
        <div class="px-4 py-2.5 border-b border-gray-800 flex justify-between items-center bg-[#0a0e1a]/80">
            <div class="flex items-center gap-3">
                <h5 class="text-[#00e5ff] font-bold m-0 flex items-center text-sm sm:text-base">
                    <i class="fa fa-map mr-2"></i> Peta Jaringan Satelit
                </h5>
                <span class="hidden sm:inline-block text-xs px-2.5 py-0.5 rounded-full bg-cyan-950/70 border border-cyan-800 text-cyan-300">
                    Wilayah Kemitraan Reseller
                </span>
            </div>
            
            {{-- Quick Stats / Status --}}
            <div class="flex items-center gap-2 text-xs">
                <span x-show="clickToAddMode" x-cloak class="px-2.5 py-1 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 animate-pulse flex items-center gap-1.5 font-semibold">
                    <i class="fa fa-crosshairs"></i> Mode Klik Peta Aktif (Klik titik untuk tambah <span x-text="clickAddType.toUpperCase()"></span>)
                </span>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="map-smart-toolbar">
            <div class="toolbar-group">
                <button type="button" class="tb-btn tb-olt cursor-default" title="Jumlah OLT Terhubung">
                    <i class="fa fa-server"></i> OLT ({{ count($olts) }})
                </button>
                <button type="button" class="tb-btn tb-odc cursor-default" title="Jumlah ODC Terhubung">
                    <i class="fa fa-hdd"></i> ODC ({{ count($odcs) }})
                </button>
                <button type="button" class="tb-btn tb-odp cursor-default" title="Jumlah ODP Terhubung">
                    <i class="fa fa-box"></i> ODP ({{ count($odps) }})
                </button>
                <button type="button" class="tb-btn tb-pelanggan cursor-default" title="Jumlah Pelanggan Terpasang">
                    <i class="fa fa-users"></i> Pelanggan ({{ count($customers) }})
                </button>
            </div>

            <div class="h-6 w-px bg-slate-700/60 mx-1 flex-shrink-0"></div>

            {{-- Node Management Actions for Reseller --}}
            <div class="toolbar-group">
                <button type="button" wire:click="openCreateModal('odp')" class="tb-btn tb-add-odp tb-btn-action" title="Daftarkan ODP Baru">
                    <i class="fa fa-plus-circle"></i> + Tambah ODP
                </button>
                <button type="button" wire:click="openCreateModal('odc')" class="tb-btn tb-add-odc tb-btn-action" title="Daftarkan ODC Baru">
                    <i class="fa fa-plus-circle"></i> + Tambah ODC
                </button>
                <button type="button" 
                        @click="clickToAddMode = !clickToAddMode" 
                        :class="clickToAddMode ? 'bg-amber-600 text-white border-amber-400' : 'bg-slate-800 text-slate-300 hover:text-white border-slate-700'"
                        class="tb-btn tb-btn-action" 
                        title="Klik titik di peta untuk langsung mendaftarkan koordinat ODP/ODC">
                    <i class="fa fa-crosshairs"></i> <span x-text="clickToAddMode ? 'Batal Klik Titik' : 'Pilih Titik di Peta'"></span>
                </button>
            </div>

            <div style="flex:1;"></div>

            {{-- Utilities --}}
            <div class="toolbar-group">
                <button type="button" onclick="document.getElementById('reseller-map').parentElement.requestFullscreen()" class="tb-icon-btn tb-full" title="Layar Penuh"><i class="fa fa-expand"></i></button>
                <button type="button" onclick="location.reload()" class="tb-icon-btn tb-refresh" title="Segarkan Data"><i class="fa fa-refresh"></i></button>
            </div>
        </div>

        {{-- Map Canvas --}}
        <div id="reseller-map" class="flex-1 w-full bg-[#0a0e1a]" style="min-height:550px; height:100%; width:100%;" wire:ignore></div>
    </div>

    {{-- MODAL TAMBAH & EDIT ODP / ODC --}}
    @if($showModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4 bg-black/75 backdrop-blur-sm overflow-y-auto">
            <div class="relative w-full max-w-xl bg-slate-900 border border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-200">
                {{-- Modal Header --}}
                <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between {{ $nodeType === 'odc' ? 'bg-gradient-to-r from-amber-950/40 to-slate-900' : 'bg-gradient-to-r from-emerald-950/40 to-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $nodeType === 'odc' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                            <i class="fa {{ $nodeType === 'odc' ? 'fa-hdd' : 'fa-box' }} text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white leading-tight">
                                {{ $modalMode === 'edit' ? 'Edit ' . strtoupper($nodeType) : 'Tambah ' . strtoupper($nodeType) . ' Baru' }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $modalMode === 'edit' ? 'Perbarui data perangkat, port & foto lokasi' : 'Daftarkan titik jaringan baru di wilayah Anda' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-800 transition-colors">
                        <i class="fa fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Modal Form Body --}}
                <form wire:submit.prevent="save" class="p-5 space-y-4 max-h-[75vh] overflow-y-auto text-slate-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nama Node --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Nama {{ strtoupper($nodeType) }} <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" wire:model="nodeName" placeholder="Contoh: {{ $nodeType === 'odc' ? 'ODC-CISARAP-01' : 'ODP-RT02-RW04' }}"
                                   class="w-full px-3.5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                            @error('nodeName') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Kode Node --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Kode Unik <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" wire:model="nodeCode" placeholder="Kode identitas..."
                                   class="w-full px-3.5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm font-mono text-cyan-300 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                            @error('nodeCode') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Parent Uplink --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                {{ $nodeType === 'odc' ? 'Uplink OLT' : 'Uplink ODC' }}
                            </label>
                            <select wire:model="parentId"
                                    class="w-full px-3.5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all">
                                <option value="">-- Pilih Induk Uplink --</option>
                                @if($nodeType === 'odc')
                                    @foreach($oltOptions as $olt)
                                        <option value="{{ $olt->id }}">{{ $olt->name }} ({{ $olt->code }})</option>
                                    @endforeach
                                @else
                                    @foreach($odcOptions as $odc)
                                        <option value="{{ $odc->id }}">{{ $odc->name }} ({{ $odc->code }})</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('parentId') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Kapasitas Port --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Kapasitas Port <span class="text-rose-400">*</span>
                            </label>
                            @if($nodeType === 'odp')
                                <select wire:model="portCount"
                                        class="w-full px-3.5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                    <option value="4">1:4 (4 Port / -7.2 dB)</option>
                                    <option value="8">1:8 (8 Port / -10.5 dB)</option>
                                    <option value="16">1:16 (16 Port / -13.5 dB)</option>
                                    <option value="24">1:24 (24 Port)</option>
                                    <option value="32">1:32 (32 Port / -17.0 dB)</option>
                                </select>
                            @else
                                <select wire:model="portCount"
                                        class="w-full px-3.5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                    <option value="24">24 Port (Mini ODC)</option>
                                    <option value="48">48 Port (Standar Distribusi)</option>
                                    <option value="72">72 Port</option>
                                    <option value="96">96 Port</option>
                                    <option value="144">144 Port (Kapasitas Besar)</option>
                                    <option value="288">288 Port (ODC Backbone)</option>
                                </select>
                            @endif
                            @error('portCount') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status Operasional</label>
                            <select wire:model="status"
                                    class="w-full px-3.5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                <option value="active">Aktif (Operasional)</option>
                                <option value="inactive">Nonaktif / Dalam Perbaikan</option>
                            </select>
                        </div>
                    </div>

                    {{-- Koordinat GPS --}}
                    <div class="p-3.5 bg-slate-950/60 border border-slate-800 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa fa-map-marker-alt text-rose-500"></i> Koordinat Titik Peta
                            </span>
                            <button type="button" onclick="window.fetchMyLocation()" 
                                    class="px-2.5 py-1 text-xs font-semibold bg-cyan-900/50 hover:bg-cyan-800 text-cyan-200 border border-cyan-700 rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer">
                                <i class="fa fa-crosshairs"></i> Ambil Lokasi GPS Saya
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] text-slate-400 mb-1 font-mono">Latitude</label>
                                <input type="text" wire:model="latitude" id="input-modal-lat" placeholder="-6.2088..."
                                       class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs font-mono text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                @error('latitude') <span class="text-rose-400 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-400 mb-1 font-mono">Longitude</label>
                                <input type="text" wire:model="longitude" id="input-modal-lng" placeholder="106.8456..."
                                       class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs font-mono text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                @error('longitude') <span class="text-rose-400 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Alamat / Patokan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat / Patokan Lokasi</label>
                        <textarea wire:model="address" rows="2" placeholder="Nama tiang, patokan jalan, RT/RW, nomor rumah..."
                                  class="w-full px-3.5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all"></textarea>
                    </div>

                    {{-- Upload Foto Lokasi / Perangkat --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                            <span>Foto Dokumentasi Titik</span>
                            <span class="text-[10px] text-slate-400">Maks. 10MB (JPG, PNG, WEBP)</span>
                        </label>
                        
                        {{-- Preview Existing Photos --}}
                        @if(!empty($existingPhotos))
                            <div class="flex items-center gap-2 mb-2 overflow-x-auto py-1">
                                @foreach($existingPhotos as $idx => $ph)
                                    <div class="relative group w-16 h-16 rounded-lg overflow-hidden border border-slate-700 shrink-0">
                                        <img src="{{ $ph }}" class="w-full h-full object-cover">
                                        <button type="button" wire:click="deleteExistingPhoto({{ $idx }})" 
                                                class="absolute inset-0 bg-rose-950/80 text-rose-300 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus Foto">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="relative flex items-center justify-center p-3 border-2 border-dashed border-slate-700 hover:border-cyan-500/70 rounded-xl bg-slate-800/40 transition-colors">
                            <input type="file" wire:model="photo" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="text-center py-1">
                                <i class="fa fa-camera text-slate-400 text-lg mb-1"></i>
                                <p class="text-xs text-slate-300">
                                    @if($photo)
                                        <span class="text-emerald-400 font-semibold"><i class="fa fa-check"></i> Foto siap diunggah ({{ $photo->getClientOriginalName() }})</span>
                                    @else
                                        Klik atau seret foto tiang / kotak perangkat ke sini
                                    @endif
                                </p>
                            </div>
                        </div>
                        @error('photo') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal" 
                                class="px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-sm font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r {{ $nodeType === 'odc' ? 'from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400' : 'from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400' }} text-white text-sm font-bold shadow-lg transition-all flex items-center gap-2">
                            <span wire:loading.remove wire:target="save"><i class="fa fa-save"></i> Simpan Data</span>
                            <span wire:loading wire:target="save"><i class="fa fa-spinner fa-spin"></i> Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Toast Notification --}}
    <div id="map-toast" 
         class="fixed bottom-6 right-6 z-[10000] px-4 py-3 bg-slate-900/95 border border-cyan-500 text-cyan-200 text-xs font-semibold rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300 opacity-0 pointer-events-none transform translate-y-3">
        <i class="fa fa-check-circle text-emerald-400 text-base"></i>
        <span id="toast-message">Data berhasil diperbarui!</span>
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
    let markersLayer = null;
    let linesLayer = null;

    function showToast(message, isError = false) {
        const toast = document.getElementById('map-toast');
        const text = document.getElementById('toast-message');
        if (!toast || !text) return;
        text.innerText = message;
        toast.className = isError 
            ? 'fixed bottom-6 right-6 z-[10000] px-4 py-3 bg-slate-900/95 border border-rose-500 text-rose-200 text-xs font-semibold rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300 opacity-100 pointer-events-auto transform translate-y-0'
            : 'fixed bottom-6 right-6 z-[10000] px-4 py-3 bg-slate-900/95 border border-cyan-500 text-cyan-200 text-xs font-semibold rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300 opacity-100 pointer-events-auto transform translate-y-0';
        setTimeout(() => {
            toast.className = 'fixed bottom-6 right-6 z-[10000] px-4 py-3 bg-slate-900/95 border border-cyan-500 text-cyan-200 text-xs font-semibold rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300 opacity-0 pointer-events-none transform translate-y-3';
        }, 4000);
    }

    window.fetchMyLocation = function() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung Geolocation GPS.');
            return;
        }
        navigator.geolocation.getCurrentPosition(function(pos) {
            const lat = pos.coords.latitude.toFixed(7);
            const lng = pos.coords.longitude.toFixed(7);
            
            const latInput = document.getElementById('input-modal-lat');
            const lngInput = document.getElementById('input-modal-lng');
            if (latInput) {
                latInput.value = lat;
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (lngInput) {
                lngInput.value = lng;
                lngInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            showToast('Lokasi GPS saat ini berhasil diambil: ' + lat + ', ' + lng);
        }, function(err) {
            alert('Gagal mengambil lokasi GPS: ' + err.message);
        }, { enableHighAccuracy: true, timeout: 10000 });
    };

    window.escapeHtml = function(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    };

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
            html: `<div class="icon-badge"><i class="fa ${iconClass}" style="font-size: ${Math.round(size/1.6)}px;"></i></div>`,
            className: `custom-icon ${colorClass}`,
            iconSize: [size, size],
            iconAnchor: [size/2, size/2],
            popupAnchor: [0, -size/2]
        });
    }

    function formatSafePhotoUrl(url) {
        if (!url || typeof url !== 'string') return '';
        if (url.startsWith('data:')) return url;
        url = url.replace(/^https?:\/\/[^\/]+(\/storage\/.*)$/, '$1');
        if (url.startsWith('http://') || url.startsWith('https://')) return url;
        if (url.startsWith('/storage/')) return url;
        if (url.startsWith('storage/')) return '/' + url;
        if (url.startsWith('/')) return url;
        return '/storage/' + url;
    }

    function initResellerMap() {
        const container = document.getElementById('reseller-map');
        if (!container) return;
        
        if (typeof window.L === 'undefined') {
            setTimeout(initResellerMap, 200);
            return;
        }

        if (container._leaflet_id) { container._leaflet_id = null; }
        if (mapInstance) { 
            try { mapInstance.remove(); } catch(e){}
            mapInstance = null; 
        }
        container.innerHTML = "";

        mapInstance = window.L.map('reseller-map', {
            zoomControl: true,
            attributionControl: true
        }).setView([-6.2088, 106.8456], 12);
        
        let googleHybrid = window.L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 22, subdomains:['mt0','mt1','mt2','mt3'], attribution: '&copy; Google Hybrid Satellite HD' });
        let googleRoad = window.L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 22, subdomains:['mt0','mt1','mt2','mt3'], attribution: '&copy; Google Road Clean HD' });
        let darkLayer = window.L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 22, subdomains:['mt0','mt1','mt2','mt3'], className: 'map-tiles-dark', attribution: '&copy; dsBilling Night Mode' });
        
        googleHybrid.addTo(mapInstance);
        window.L.control.layers({ '🛰️ Google Hybrid Satellite HD': googleHybrid, '🗺️ Google Road Clean HD': googleRoad, '🌙 Dark Mode': darkLayer }, null, { position: 'topright' }).addTo(mapInstance);
        
        linesLayer = window.L.layerGroup().addTo(mapInstance);
        markersLayer = window.L.layerGroup().addTo(mapInstance);
        let bounds = [];
        const popupOpt = { maxWidth: 330, minWidth: 280, className: 'modern-map-popup', autoPan: true };
        
        const olts = window.mapData.olts || [];
        const odcs = window.mapData.odcs || [];
        const closures = window.mapData.closures || [];
        const odps = window.mapData.odps || [];
        const customers = window.mapData.customers || [];
        
        // 1. Render OLT
        olts.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon('olt');
                let firstPhoto = (i.photos && i.photos.length > 0) ? formatSafePhotoUrl(i.photos[0]) : '';
                let photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onclick="window.open('${firstPhoto}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : '';
                let gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${i.latitude},${i.longitude}`;

                let popupHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-server text-blue-400" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${window.escapeHtml(i.name)}">${window.escapeHtml(i.name)}</div>
                                    <div class="network-subtitle">${window.escapeHtml(i.code || 'OLT-CORE')}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-olt">OLT</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">IP Host:</span>
                                    <span class="node-kv-value font-mono text-cyan-400">${window.escapeHtml(i.ip_address || '-')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Port PON:</span>
                                    <span class="node-kv-value text-blue-300 font-bold">${i.pon_port_count || i.port_count || '-'} Port</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Model:</span>
                                    <span class="node-kv-value">${window.escapeHtml(i.model || '-')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${window.escapeHtml(i.address || '-')}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <a href="${gmapsUrl}" target="_blank" class="node-btn node-btn-secondary" style="width:100%;">
                                    <i class="fa fa-map-marker-alt text-rose-400"></i> Rute Google Maps
                                </a>
                            </div>
                        </div>
                    </div>`;

                let marker = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(popupHtml, popupOpt);
                bounds.push([i.latitude, i.longitude]);
            }
        });
        
        // 2. Render ODC
        odcs.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon('odc');
                let firstPhoto = (i.photos && i.photos.length > 0) ? formatSafePhotoUrl(i.photos[0]) : '';
                let photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onclick="window.open('${firstPhoto}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : '';
                let gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${i.latitude},${i.longitude}`;

                let popupHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-hdd text-amber-400" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${window.escapeHtml(i.name)}">${window.escapeHtml(i.name)}</div>
                                    <div class="network-subtitle">${window.escapeHtml(i.code || 'ODC')}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-odc">ODC</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Uplink OLT:</span>
                                    <span class="node-kv-value text-sky-400 font-bold">${window.escapeHtml(i.olt_name || 'OLT Default')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Port PON:</span>
                                    <span class="node-kv-value font-mono text-emerald-400">${window.escapeHtml(i.pon_port || '-')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Kapasitas:</span>
                                    <span class="node-kv-value text-amber-300 font-bold">${i.capacity || 48} Port</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${window.escapeHtml(i.address || '-')}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <button type="button" onclick="Livewire.dispatch('open-edit-modal', { type: 'odc', id: ${i.id} })" class="node-btn node-btn-warning">
                                    <i class="fa fa-pencil"></i> Edit ODC
                                </button>
                                <a href="${gmapsUrl}" target="_blank" class="node-btn node-btn-secondary">
                                    <i class="fa fa-map-marker-alt text-rose-400"></i> Rute Maps
                                </a>
                            </div>
                        </div>
                    </div>`;

                let marker = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(popupHtml, popupOpt);
                bounds.push([i.latitude, i.longitude]);

                if(i.olt_id) {
                    let olt = olts.find(o => o.id == i.olt_id);
                    if(olt && olt.latitude && olt.longitude) {
                        let path = [[olt.latitude, olt.longitude], [i.latitude, i.longitude]];
                        if (window.L.polyline && window.L.polyline.antPath) {
                            window.L.polyline.antPath(path, { color: '#6f42c1', weight: 4, opacity: 0.9, pulseColor: '#ffffff', delay: 400 }).addTo(linesLayer);
                        } else {
                            window.L.polyline(path, { color: '#6f42c1', weight: 3, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer);
                        }
                    }
                }
            }
        });

        // 3. Render Closures
        closures.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon('closure');
                let firstPhoto = (i.photos && i.photos.length > 0) ? formatSafePhotoUrl(i.photos[0]) : '';
                let photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onclick="window.open('${firstPhoto}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : '';
                let gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${i.latitude},${i.longitude}`;

                let popupHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-archive text-slate-300" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${window.escapeHtml(i.name)}">${window.escapeHtml(i.name)}</div>
                                    <div class="network-subtitle">${window.escapeHtml(i.code || 'CLOSURE')}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-closure">CLOSURE</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Tipe:</span>
                                    <span class="node-kv-value">${window.escapeHtml(i.type || 'Dome')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Kapasitas Core:</span>
                                    <span class="node-kv-value text-teal-300 font-bold">${i.port_count || 24} Core</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Uplink ODC:</span>
                                    <span class="node-kv-value text-amber-300 font-bold">${window.escapeHtml(i.odc_name || 'N/A')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${window.escapeHtml(i.address || '-')}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <a href="${gmapsUrl}" target="_blank" class="node-btn node-btn-secondary" style="width:100%;">
                                    <i class="fa fa-map-marker-alt text-rose-400"></i> Rute Maps
                                </a>
                            </div>
                        </div>
                    </div>`;

                let marker = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(popupHtml, popupOpt);
                bounds.push([i.latitude, i.longitude]);

                if(i.odc_id) {
                    let odc = odcs.find(o => o.id == i.odc_id);
                    if(odc && odc.latitude && odc.longitude) {
                        let path = [[odc.latitude, odc.longitude], [i.latitude, i.longitude]];
                        if (window.L.polyline && window.L.polyline.antPath) {
                            window.L.polyline.antPath(path, { color: '#0d9488', weight: 3, opacity: 0.8, dashArray: [4, 8], pulseColor: '#ffffff', delay: 400 }).addTo(linesLayer);
                        } else {
                            window.L.polyline(path, { color: '#0d9488', weight: 2.5, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer);
                        }
                    }
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
                let barColor = pct > 90 ? '#ef4444' : (pct > 70 ? '#f59e0b' : '#10b981');
                let lossDb = totalP == 4 ? 7.2 : (totalP == 8 ? 10.5 : (totalP == 16 ? 13.5 : (totalP == 32 ? 17.0 : 0)));
                let firstPhoto = (i.photos && i.photos.length > 0) ? formatSafePhotoUrl(i.photos[0]) : '';
                let photoHtml = firstPhoto ? `<div style="margin-bottom:6px;"><img src="${firstPhoto}" onclick="window.open('${firstPhoto}', '_blank')" style="width:100%;height:95px;object-fit:cover;border-radius:8px;border:1px solid #334155;cursor:pointer;" title="Klik untuk perbesar"></div>` : '';
                let gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${i.latitude},${i.longitude}`;

                let popupHtml = `
                    <div class="network-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-box text-emerald-400" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${window.escapeHtml(i.name)}">${window.escapeHtml(i.name)}</div>
                                    <div class="network-subtitle">${window.escapeHtml(i.code || 'ODP-NODE')}</div>
                                </div>
                            </div>
                            <span class="network-badge badge-odp">ODP</span>
                        </div>
                        <div class="network-content">
                            ${photoHtml}
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Splitter Ratio:</span>
                                    <span class="node-kv-value font-mono text-sky-400 font-bold">1:${totalP} <span class="text-xs text-slate-400 font-normal">(-${lossDb} dB)</span></span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Port Terpakai:</span>
                                    <span class="node-kv-value"><b>${usedP}</b> / ${totalP} (${pct}%)</span>
                                </div>
                                <div class="node-progress-bg">
                                    <div class="node-progress-bar" style="width: ${pct}%; background: ${barColor};"></div>
                                </div>
                                <div class="node-kv" style="margin-top:2px;">
                                    <span class="node-kv-label">Port Tersedia:</span>
                                    <span class="node-kv-value text-emerald-400 font-bold">${availP} Port Kosong</span>
                                </div>
                            </div>
                            <div class="node-panel">
                                <div class="node-kv">
                                    <span class="node-kv-label">Uplink ODC:</span>
                                    <span class="node-kv-value text-amber-300 font-bold">${window.escapeHtml(i.odc_name || 'N/A')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Area / Lokasi:</span>
                                    <span class="node-kv-value">${window.escapeHtml(i.address || '-')}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                <button type="button" onclick="Livewire.dispatch('open-edit-modal', { type: 'odp', id: ${i.id} })" class="node-btn node-btn-primary">
                                    <i class="fa fa-pencil"></i> Edit ODP
                                </button>
                                <a href="${gmapsUrl}" target="_blank" class="node-btn node-btn-secondary">
                                    <i class="fa fa-map-marker-alt text-rose-400"></i> Rute Maps
                                </a>
                            </div>
                        </div>
                    </div>`;

                let marker = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(popupHtml, popupOpt);
                bounds.push([i.latitude, i.longitude]);

                if(i.odc_id) {
                    let odc = odcs.find(o => o.id == i.odc_id);
                    if(odc && odc.latitude && odc.longitude) {
                        let path = [[odc.latitude, odc.longitude], [i.latitude, i.longitude]];
                        if (window.L.polyline && window.L.polyline.antPath) {
                            window.L.polyline.antPath(path, { color: '#fd7e14', weight: 4, opacity: 0.8, pulseColor: '#ffffff', delay: 400 }).addTo(linesLayer);
                        } else {
                            window.L.polyline(path, { color: '#fd7e14', weight: 3, opacity: 0.8, className: 'connection-line' }).addTo(linesLayer);
                        }
                    }
                }
            }
        });
        
        // 5. Render Pelanggan (ONU)
        customers.forEach(i => {
            if(i.latitude && i.longitude) {
                let icon = createUnifiedIcon(i.is_online ? 'online' : 'offline');
                let waNumber = (i.phone || '').replace(/[^0-9]/g, '');
                if (waNumber.startsWith('0')) { waNumber = '62' + waNumber.substring(1); }
                let gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${i.latitude},${i.longitude}`;

                let popupHtml = `
                    <div class="customer-card">
                        <div class="network-header">
                            <div class="network-header-left">
                                <i class="fa fa-wifi text-sky-400" style="font-size:14px;"></i>
                                <div class="network-title-group">
                                    <div class="network-title" title="${window.escapeHtml(i.name)}">${window.escapeHtml(i.name)}</div>
                                    <div class="network-subtitle">ID: ${window.escapeHtml(i.code || i.id)}</div>
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
                                    <span class="node-kv-value font-bold text-cyan-400">${window.escapeHtml(i.service_type || 'Internet')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">WiFi SSID:</span>
                                    <span class="node-kv-value font-mono text-amber-300">${window.escapeHtml(i.ssid_name || '-')}</span>
                                </div>
                                <div class="node-kv">
                                    <span class="node-kv-label">Telepon:</span>
                                    <span class="node-kv-value font-mono">${window.escapeHtml(i.phone || '-')}</span>
                                </div>
                            </div>
                            <div class="node-actions">
                                ${waNumber ? `<a href="https://wa.me/${waNumber}" target="_blank" class="node-btn node-btn-success"><i class="fab fa-whatsapp"></i> Chat WA</a>` : ''}
                                <a href="${gmapsUrl}" target="_blank" class="node-btn node-btn-secondary">
                                    <i class="fa fa-map-marker-alt text-rose-400"></i> Maps
                                </a>
                            </div>
                        </div>
                    </div>`;

                let marker = window.L.marker([i.latitude, i.longitude], { icon: icon, interactive: true })
                    .addTo(markersLayer)
                    .bindPopup(popupHtml, popupOpt);
                bounds.push([i.latitude, i.longitude]);

                if(i.odp_id) {
                    let odp = odps.find(o => o.id == i.odp_id);
                    if(odp && odp.latitude && odp.longitude) {
                        let path = [[odp.latitude, odp.longitude], [i.latitude, i.longitude]];
                        if (i.is_online && window.L.polyline && window.L.polyline.antPath) {
                            window.L.polyline.antPath(path, { color: '#00f2ff', weight: 3, opacity: 1.0, dashArray: [2, 10], pulseColor: '#ffffff', delay: 320 }).addTo(linesLayer);
                        } else {
                            let opts = i.is_online ? { color: '#00f2fff3', weight: 3, opacity: 1.0, dashArray: '4, 8', className: 'connection-online' } : { color: '#6b7280', weight: 2, opacity: 0.5, dashArray: '2, 6' };
                            window.L.polyline(path, opts).addTo(linesLayer);
                        }
                    }
                }
            }
        });
        
        // Map Click Listener for "Click to Add Node" mode
        mapInstance.on('click', function(e) {
            const root = document.querySelector('[x-data]');
            if (root && root.__x && root.__x.$data.clickToAddMode) {
                const lat = e.latlng.lat.toFixed(7);
                const lng = e.latlng.lng.toFixed(7);
                const type = root.__x.$data.clickAddType || 'odp';
                Livewire.dispatch('open-create-modal', { type: type, lat: parseFloat(lat), lng: parseFloat(lng) });
            }
        });

        if (bounds.length > 0) { 
            mapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 17 }); 
        }
        
        setTimeout(() => { if(mapInstance) mapInstance.invalidateSize(); }, 250);
        setTimeout(() => { if(mapInstance) mapInstance.invalidateSize(); }, 800);
        window.addEventListener('resize', () => { if(mapInstance) mapInstance.invalidateSize(); });
    }

    // Listen to Livewire events
    window.addEventListener('node-saved', function(e) {
        const detail = e.detail || {};
        showToast(detail.message || 'Data berhasil disimpan!');
        // Refresh page data or reinit
        setTimeout(() => {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('$refresh');
            }
        }, 1200);
    });

    if (document.readyState === 'loading') { 
        document.addEventListener('DOMContentLoaded', initResellerMap); 
    } else { 
        initResellerMap(); 
    }
    document.addEventListener('livewire:navigated', initResellerMap);
})();
</script>
