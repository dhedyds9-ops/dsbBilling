<div class="space-y-3.5 pb-6">
    @section("header_title", "Sensus / Pendataan")
    <div class="bg-indigo-600 text-white p-3.5 sm:p-4 rounded-2xl shadow-md">
        <div class="flex items-center gap-2 mb-1">
            <span class="material-symbols-outlined text-lg sm:text-xl">radar</span>
            <h2 class="text-base sm:text-lg font-black">Sensus & Audit Jalur Lapangan</h2>
        </div>
        <p class="text-xs text-indigo-100">Validasi ONU terpasang, port ODP & titik GPS tanpa memutus internet.</p>
    </div>

    {{-- Mode Toggle: Pelanggan vs Titipan --}}
    <div class="bg-slate-200/80 dark:bg-slate-800 p-1 rounded-xl flex items-center shadow-inner gap-1">
        <button type="button" wire:click="$set('is_titipan', false)"
            class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 {{ !$is_titipan ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
            <span class="material-symbols-outlined text-sm">person</span>
            <span>Pelanggan Resmi</span>
        </button>
        <button type="button" wire:click="$set('is_titipan', true)"
            class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 {{ $is_titipan ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
            <span class="material-symbols-outlined text-sm">home_work</span>
            <span>Titipan / Non-Pelanggan</span>
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-3.5 sm:p-5 space-y-3.5">
        
        {{-- Skenario 1: Pelanggan Resmi --}}
        @if(!$is_titipan)
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Pilih Pelanggan (*)</label>
                <x-ds-searchable-select
                    wire:model="customer_id"
                    strategy="remote"
                    search-action="searchCustomers"
                    placeholder="-- Pilih Pelanggan --"
                    search-placeholder="Cari nama atau no HP..."
                    :selected-label="$this->selectedCustomerLabel"
                    :error="$errors->first('customer_id')"
                />
            </div>
        @else
            {{-- Skenario 2: Titipan Perangkat / Lokasi Khusus --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Nama Pemilik / Lokasi Titipan (*)</label>
                    <input type="text" wire:model="titipan_name" 
                        class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium focus:ring-2 focus:ring-indigo-500" 
                        placeholder="Contoh: Titip di Rumah Pak RT 02 / Pos Ronda / Tower Relai">
                    @error('titipan_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Catatan / Keterangan Titipan (Opsional)</label>
                    <input type="text" wire:model="titipan_notes" 
                        class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium focus:ring-2 focus:ring-indigo-500" 
                        placeholder="Contoh: Modem titipan untuk distribusi CCTV / akses warga">
                </div>
            </div>
        @endif

        {{-- ODP (Optical Distribution Point) & Nomor Port ODP --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Titik ODP Lapangan (*)</label>
                <x-ds-searchable-select
                    wire:model="odp_id"
                    strategy="local"
                    :options="$odpsOptions"
                    placeholder="-- Pilih ODP --"
                    search-placeholder="Cari nama atau kode ODP..."
                    :error="$errors->first('odp_id')"
                />
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Port ODP</label>
                <input type="number" wire:model="odp_port" min="1" max="128" 
                    class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-bold text-center focus:ring-2 focus:ring-indigo-500" 
                    placeholder="Contoh: 1">
                @error('odp_port') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- OLT & PON Port --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">OLT Pusat (*)</label>
                <x-ds-searchable-select
                    wire:model="olt_id"
                    strategy="local"
                    :options="$oltsOptions"
                    placeholder="-- OLT --"
                    search-placeholder="Cari OLT..."
                    :error="$errors->first('olt_id')"
                />
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">PON Port</label>
                <input type="number" wire:model="pon_port" min="1" max="32" 
                    class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-bold text-center" 
                    placeholder="1">
                @error('pon_port') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Scan ONU / Serial Number --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Scan Barcode / Ketik SN ONT (*)</label>
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
                    <div id="reader-audit" class="w-full bg-black min-h-[220px]"></div>
                </div>

                <div class="relative flex gap-2" x-show="!scannerOpen">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:20px">barcode</span>
                        <input type="text" wire:model="onu_sn" class="w-full pl-10 pr-3 py-2.5 font-mono text-base font-black tracking-widest border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 uppercase" placeholder="ZTEG... / HWTC...">
                    </div>
                    <button type="button" @click="startScanner" class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-3.5 flex items-center justify-center">
                        <span class="material-symbols-outlined" style="font-size:20px">photo_camera</span>
                    </button>
                </div>
                @error("onu_sn") <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- GPS Koordinat --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Lokasi Titik Rumah / Pasang (GPS)</label>
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
                            (error) => { alert('Gagal mendapatkan lokasi GPS. Pastikan izin lokasi aktif.'); this.loading = false; },
                            { enableHighAccuracy: true }
                        );
                    } else {
                        alert('Browser Anda tidak mendukung GPS.'); this.loading = false;
                    }
                }
            }" class="flex gap-2">
                <input type="text" disabled :value="$wire.latitude ? ($wire.latitude + ', ' + $wire.longitude) : ''" class="flex-1 px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 font-mono" placeholder="Belum ada titik koordinat">
                <button type="button" @click="getLocation" class="shrink-0 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 px-3.5 rounded-xl flex items-center justify-center gap-1 font-bold text-xs transition-colors">
                    <span x-show="!loading" class="material-symbols-outlined" style="font-size:18px">location_on</span>
                    <span x-show="loading" class="material-symbols-outlined animate-spin" style="font-size:18px">sync</span>
                    <span>Ambil GPS</span>
                </button>
            </div>
        </div>

        <div class="pt-3 mt-1 border-t border-slate-100 dark:border-slate-800">
            <button type="button" wire:click="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.99]">
                <span class="material-symbols-outlined" style="font-size:20px">save</span>
                <span>Simpan Pendataan Sensus</span>
            </button>
        </div>

    </div>
</div>
