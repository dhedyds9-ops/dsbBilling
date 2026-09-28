@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">public</span>
    <span class="text-lg">Peta Jaringan & Pelanggan (Wilayah Anda)</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #reseller-map { height: 75vh; width: 100%; border-radius: 1rem; z-index: 1; }
</style>
@endpush

<div class="space-y-5 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        
        <div class="flex flex-wrap gap-4 mb-4">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span> OLT ({{ count($olts) }})
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span> ODC ({{ count($odcs) }})
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> ODP ({{ count($odps) }})
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <span class="w-3 h-3 rounded-full bg-purple-500"></span> Pelanggan ({{ count($customers) }})
            </div>
        </div>

        <div id="reseller-map" wire:ignore></div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('livewire:initialized', function () {
    // Inisialisasi Peta
    const map = L.map('reseller-map').setView([-6.2088, 106.8456], 12);
    
    L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: 'Map data &copy; Google'
    }).addTo(map);

    const bounds = [];

    // Fungsi helper untuk icon
    function createIcon(color) {
        return L.divIcon({
            className: 'custom-icon',
            html: <div style="background-color:  + color + ; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.5);"></div>,
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });
    }

    // Data dari server
    const olts = @json($olts);
    const odcs = @json($odcs);
    const odps = @json($odps);
    const customers = @json($customers);

    olts.forEach(item => {
        L.marker([item.latitude, item.longitude], {icon: createIcon('#3b82f6')})
         .addTo(map)
         .bindPopup('<b>OLT:</b> ' + item.name + '<br><b>IP:</b> ' + item.ip_address);
        bounds.push([item.latitude, item.longitude]);
    });

    odcs.forEach(item => {
        L.marker([item.latitude, item.longitude], {icon: createIcon('#f59e0b')})
         .addTo(map)
         .bindPopup('<b>ODC:</b> ' + item.name + '<br><b>Kode:</b> ' + item.code);
        bounds.push([item.latitude, item.longitude]);
    });

    odps.forEach(item => {
        L.marker([item.latitude, item.longitude], {icon: createIcon('#10b981')})
         .addTo(map)
         .bindPopup('<b>ODP:</b> ' + item.name + '<br><b>Port Terpakai:</b> ' + (item.used_port_count||0) + '/' + (item.port_count||0));
        bounds.push([item.latitude, item.longitude]);
    });

    customers.forEach(item => {
        L.marker([item.latitude, item.longitude], {icon: createIcon('#a855f7')})
         .addTo(map)
         .bindPopup('<b>Pelanggan:</b> ' + item.name + '<br><b>ID:</b> ' + item.customer_id);
        bounds.push([item.latitude, item.longitude]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
});
</script>
@endpush
