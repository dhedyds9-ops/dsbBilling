<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">qr_code_scanner</span>
                Scan Perangkat ONU
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pindai barcode atau masukkan Serial Number (SN) / MAC Address modem pelanggan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Area Scan -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <form wire:submit="scanBarcode">
                <div class="text-center mb-6">
                    <div class="w-32 h-32 mx-auto bg-slate-100 dark:bg-slate-900 rounded-2xl border-4 border-dashed border-indigo-300 dark:border-indigo-700 flex items-center justify-center relative overflow-hidden mb-4">
                        <div class="absolute inset-0 bg-indigo-500/10 animate-pulse"></div>
                        <div class="absolute top-0 w-full h-1 bg-indigo-500 shadow-[0_0_15px_rgba(99,102,241,1)] animate-[scan_2s_ease-in-out_infinite]"></div>
                        <span class="material-symbols-outlined notranslate text-5xl text-indigo-400" translate="no">barcode</span>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Scanner Aktif</h3>
                    <p class="text-xs text-slate-500">Gunakan scanner fisik atau kamera untuk memindai otomatis.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Serial Number / MAC Address</label>
                    <div class="relative">
                        <input wire:model="serialNumber" type="text" class="bg-slate-50 border border-slate-300 text-slate-900 text-lg font-mono font-bold rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-4 pl-12 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white uppercase tracking-wider shadow-inner" placeholder="Contoh: ZTEGC1234567" autofocus>
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined notranslate text-slate-400" translate="no">keyboard</span>
                        </div>
                    </div>
                    @error('serialNumber') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="scanBarcode" class="material-symbols-outlined notranslate" translate="no">search</span>
                        <span wire:loading wire:target="scanBarcode" class="material-symbols-outlined notranslate animate-spin" translate="no">autorenew</span>
                        Proses Scan
                    </button>
                    @if($scanResult)
                        <button type="button" wire:click="resetScan" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-white rounded-xl font-medium transition-colors">
                            Reset
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Hasil Scan -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col items-center justify-center min-h-[400px]">
            @if(!$scanResult)
                <div class="text-center text-slate-400 dark:text-slate-500">
                    <span class="material-symbols-outlined notranslate text-6xl mb-3 opacity-50" translate="no">search</span>
                    <p>Menunggu input hasil pemindaian...</p>
                </div>
            @elseif($scanResult === 'not_found')
                <div class="text-center text-red-500">
                    <span class="material-symbols-outlined notranslate text-6xl mb-3" translate="no">error</span>
                    <h3 class="text-lg font-bold mb-1">Perangkat Tidak Terdeteksi</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Pastikan format Serial Number benar atau perangkat sudah terhubung ke OLT.</p>
                </div>
            @elseif($scanResult === 'unregistered')
                <div class="w-full">
                    <div class="bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 p-4 rounded-xl mb-6 flex gap-3 items-start border border-amber-200 dark:border-amber-800">
                        <span class="material-symbols-outlined notranslate" translate="no">info</span>
                        <div>
                            <h4 class="font-bold">ONU Terdeteksi di OLT (Belum Diregistrasi)</h4>
                            <p class="text-sm mt-1">Perangkat ini sudah memancarkan sinyal ke OLT namun belum terdaftar di sistem. Lanjutkan ke proses registrasi.</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                        <div class="grid grid-cols-2 gap-4 border-b border-slate-200 dark:border-slate-700 pb-4">
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Serial Number</p>
                                <p class="font-mono font-bold text-slate-900 dark:text-white">{{ $scannedOnu['serial_number'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Redaman (RX Power)</p>
                                <p class="font-bold {{ $scannedOnu['rx_power'] > -25 ? 'text-emerald-600' : 'text-red-600' }}">{{ $scannedOnu['rx_power'] }} dBm</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Terhubung di OLT</p>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $scannedOnu['olt_detected'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">PON Port</p>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $scannedOnu['pon_port'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('technician.installation.register') }}?sn={{ $scannedOnu['serial_number'] }}" class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors">
                            <span class="material-symbols-outlined notranslate text-sm" translate="no">add_circle</span>
                            Registrasi ONU Sekarang
                        </a>
                    </div>
                </div>
            @elseif($scanResult === 'found')
                <div class="w-full">
                    <div class="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 p-4 rounded-xl mb-6 flex gap-3 items-start border border-emerald-200 dark:border-emerald-800">
                        <span class="material-symbols-outlined notranslate" translate="no">check_circle</span>
                        <div>
                            <h4 class="font-bold">ONU Sudah Terdaftar!</h4>
                            <p class="text-sm mt-1">Perangkat ini sudah diregistrasi di dalam sistem database.</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                        <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-700 pb-4">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined notranslate text-indigo-600" translate="no">person</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Pemilik / Pelanggan</p>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $scannedOnu->customerService?->customer?->name ?? 'Belum ada pelanggan' }}</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Serial Number</p>
                                <p class="font-mono font-bold text-slate-900 dark:text-white">{{ $scannedOnu->serial_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Status</p>
                                <p class="font-bold text-emerald-600 uppercase">{{ $scannedOnu->status }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Terkoneksi ODP</p>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $scannedOnu->odp?->code ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Rx Power (Terakhir)</p>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $scannedOnu->rx_power_dbm ?? '-' }} dBm</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <style>
        @keyframes scan {
            0%, 100% { top: 0; }
            50% { top: 100%; }
        }
    </style>
</div>
