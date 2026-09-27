@section("header_title", "Sensus / Pendataan")

<div class="space-y-4 p-4">
    <div class="bg-indigo-600 text-white p-4 rounded-2xl shadow-md">
        <h2 class="text-lg font-black">Pendataan Pelanggan Lama</h2>
        <p class="text-xs text-indigo-200 mt-1">Petakan ONU yang sudah online tanpa memutus internet pelanggan.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-4">
        
        {{-- Pelanggan --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Pelanggan</label>
            <div class="relative mb-2">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px">search</span>
                <input type="text" wire:model.live.debounce.300ms="searchCustomer" class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100" placeholder="Cari nama atau no HP...">
            </div>
            <select wire:model="customer_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                <option value="">-- Pilih Pelanggan --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                @endforeach
            </select>
            @error("customer_id") <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- OLT & PON --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">OLT</label>
                <select wire:model="olt_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    <option value="">-- OLT --</option>
                    @foreach($olts as $olt)
                        <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">PON Port</label>
                <input type="number" wire:model="pon_port" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-bold text-center" placeholder="1">
            </div>
        </div>

        {{-- Paket Layanan --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Profil Layanan Aktif</label>
            <select wire:model="service_profile_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                <option value="">-- Pilih Profil --</option>
                @foreach($services as $svc)
                    <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Scan ONU --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Scan MAC / SN ONU</label>
            <div x-data="{
                scannerOpen: false,
                html5QrcodeScanner: null,
                startScanner() {
                    this.scannerOpen = true;
                    if (typeof Html5QrcodeScanner === 'undefined') {
                        let script = document.createElement('script');
                        script.src = 'https://unpkg.com/html5-qrcode';
                        script.onload = () => this.initScanner();
                        document.head.appendChild(script);
                    } else {
                        this.initScanner();
                    }
                },
                initScanner() {
                    this.$nextTick(() => {
                        this.html5QrcodeScanner = new Html5QrcodeScanner(
                            'reader-audit', { fps: 10, qrbox: {width: 250, height: 100} }, false);
                        this.html5QrcodeScanner.render(
                            (t) => { @this.set('onu_sn', t); this.closeScanner(); },
                            (e) => {}
                        );
                    });
                },
                closeScanner() {
                    if (this.html5QrcodeScanner) this.html5QrcodeScanner.clear();
                    this.scannerOpen = false;
                }
            }">
                <div x-show="scannerOpen" style="display: none;" class="bg-black rounded-2xl overflow-hidden relative mb-2">
                    <button type="button" @click="closeScanner" class="absolute top-2 right-2 z-50 bg-red-500 text-white p-2 rounded-full flex shadow-lg">
                        <span class="material-symbols-outlined" style="font-size:20px">close</span>
                    </button>
                    <div id="reader-audit" class="w-full bg-black min-h-[250px]"></div>
                </div>

                <div class="relative flex gap-2" x-show="!scannerOpen">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">barcode</span>
                        <input type="text" wire:model="onu_sn" class="w-full pl-12 pr-4 py-3 font-mono text-lg font-black tracking-widest border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 uppercase" placeholder="ZTEG...">
                    </div>
                    <button type="button" @click="startScanner" class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 flex flex-col items-center justify-center">
                        <span class="material-symbols-outlined" style="font-size:20px">photo_camera</span>
                    </button>
                </div>
                @error("onu_sn") <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- GPS --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Lokasi (GPS)</label>
            <div x-data="{
                loading: false,
                getLocation() {
                    this.loading = true;
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                @this.set('latitude', position.coords.latitude);
                                @this.set('longitude', position.coords.longitude);
                                this.loading = false;
                            },
                            (error) => { alert('Gagal mendapatkan lokasi GPS.'); this.loading = false; },
                            { enableHighAccuracy: true }
                        );
                    } else {
                        alert('Browser Anda tidak mendukung GPS.'); this.loading = false;
                    }
                }
            }" class="flex gap-2">
                <input type="text" disabled :value="$wire.latitude ? ($wire.latitude + ', ' + $wire.longitude) : ''" class="flex-1 px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm bg-slate-100 dark:bg-slate-900 text-slate-500" placeholder="Belum ada titik koordinat">
                <button type="button" @click="getLocation" class="shrink-0 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 px-4 rounded-xl flex items-center justify-center gap-1 font-bold text-sm">
                    <span x-show="!loading" class="material-symbols-outlined" style="font-size:18px">location_on</span>
                    <span x-show="loading" class="material-symbols-outlined animate-spin" style="font-size:18px">sync</span>
                    Ambil GPS
                </button>
            </div>
        </div>

        <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-800">
            <button wire:click="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-md">
                <span class="material-symbols-outlined" style="font-size:20px">save</span>
                Simpan Pendataan
            </button>
        </div>

    </div>
</div>

