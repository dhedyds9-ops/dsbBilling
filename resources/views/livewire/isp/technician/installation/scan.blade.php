<div class="p-4 pb-24">
    <div class="mb-6 flex flex-col gap-2 justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">qr_code_scanner</span>
                Scan Perangkat ONU
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Gunakan kamera HP atau ketik manual Serial Number / MAC Address modem pelanggan.</p>
        </div>
    </div>

    <div class="flex flex-col gap-4 pb-6">
        <!-- Area Scan Kamera -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            
            <div id="camera-container" class="hidden mb-4" wire:ignore>
                <div class="rounded-xl overflow-hidden border-2 border-indigo-500 relative">
                    <div id="qr-reader" class="w-full"></div>
                    <button type="button" onclick="stopScanner()" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-2 shadow-lg hover:bg-red-700 z-50 flex items-center justify-center">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">close</span>
                    </button>
                </div>
            </div>

            <div id="scanner-placeholder" class="text-center mb-5" wire:ignore>
                <div class="w-24 h-24 mx-auto bg-slate-100 dark:bg-slate-900 rounded-2xl border-2 border-dashed border-indigo-300 dark:border-indigo-700 flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined notranslate text-4xl text-indigo-400" translate="no">barcode</span>
                </div>
                <button type="button" onclick="startScanner()" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-bold py-2 px-4 rounded-full transition-colors flex items-center gap-1 mx-auto">
                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">photo_camera</span>
                    Buka Kamera Scanner
                </button>
            </div>

            <form wire:submit="scanBarcode">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ketik Manual (Jika Kamera Gagal)</label>
                    <div class="relative">
                        <input id="sn-input" wire:model="serialNumber" type="text" class="bg-slate-50 border border-slate-300 text-slate-900 text-base font-mono font-bold rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 pl-10 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white uppercase tracking-wider" placeholder="ZTEGC1234567 / HWTC..." autofocus>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined notranslate text-slate-400 text-sm" translate="no">keyboard</span>
                        </div>
                    </div>
                    @error('serialNumber') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-colors flex items-center justify-center gap-1 text-sm">
                        <span wire:loading.remove wire:target="scanBarcode" class="material-symbols-outlined notranslate text-[18px]" translate="no">search</span>
                        <span wire:loading wire:target="scanBarcode" class="material-symbols-outlined notranslate animate-spin text-[18px]" translate="no">autorenew</span>
                        Proses Scan
                    </button>
                    @if($scanResult)
                        <button type="button" wire:click="resetScan" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-white rounded-xl font-medium transition-colors">
                            <span class="material-symbols-outlined notranslate text-[18px]" translate="no">refresh</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Hasil Scan -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 flex flex-col justify-center w-full relative">
            @if(!$scanResult)
                <div class="text-center text-slate-400 dark:text-slate-500 py-6">
                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">search</span>
                    <p class="text-xs">Menunggu input hasil pemindaian...</p>
                </div>
            @elseif($scanResult === 'not_found')
                <div class="text-center text-red-500 py-6">
                    <span class="material-symbols-outlined notranslate text-4xl mb-2" translate="no">error</span>
                    <h3 class="text-base font-bold mb-1">Format Tidak Dikenali</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Pastikan Serial Number atau MAC Address sesuai label perangkat.</p>
                </div>
            @elseif($scanResult === 'unregistered')
                <div class="w-full space-y-3">
                    <div class="bg-amber-500/10 text-amber-700 dark:text-amber-400 p-3.5 rounded-2xl flex gap-2.5 items-start border border-amber-200 dark:border-amber-800/60">
                        <span class="material-symbols-outlined notranslate text-base mt-0.5" translate="no">info</span>
                        <div>
                            <h4 class="font-bold text-sm">ONU Belum Terdaftar di Sistem</h4>
                            <p class="text-xs opacity-90 mt-0.5">Perangkat ini belum terikat ke pelanggan manapun dan siap diregistrasi.</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
                        <div class="grid grid-cols-2 gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">Serial Number</p>
                                <p class="font-mono font-bold text-slate-900 dark:text-white text-xs">{{ $scannedOnu['serial_number'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">Status Registrasi</p>
                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                                    BELUM REGISTRASI
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">Model / Tipe</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $scannedOnu['model'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">Status Koneksi</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $scannedOnu['olt_name'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('technician.installation.register') }}?sn={{ $scannedOnu['serial_number'] }}" class="w-full flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all active:scale-95">
                            <span class="material-symbols-outlined notranslate text-[18px]" translate="no">add_circle</span>
                            Registrasi ONU Ini
                        </a>
                    </div>
                </div>
            @elseif($scanResult === 'found')
                <div class="w-full space-y-3">
                    {{-- Alert Status Terdaftar --}}
                    <div class="bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 p-3.5 rounded-2xl flex gap-2.5 items-start border border-emerald-200 dark:border-emerald-800/60">
                        <span class="material-symbols-outlined notranslate text-base mt-0.5 text-emerald-600 dark:text-emerald-400" translate="no">check_circle</span>
                        <div>
                            <h4 class="font-bold text-sm">ONU Sudah Terdaftar & Aktif!</h4>
                            <p class="text-xs opacity-90 mt-0.5">Perangkat ini sudah terikat ke data pelanggan di sistem.</p>
                        </div>
                    </div>

                    {{-- Data Pemilik / Pelanggan --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
                        <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div class="w-11 h-11 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/50">
                                <span class="material-symbols-outlined notranslate text-xl" translate="no">person</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="font-black text-slate-900 dark:text-white text-sm truncate">{{ $scannedOnu['customer_name'] }}</h4>
                                    <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-black rounded-lg uppercase shrink-0">
                                        {{ $scannedOnu['status'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                    Kode: <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $scannedOnu['customer_code'] }}</span>
                                    • Layanan: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $scannedOnu['package_name'] }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Info Teknis Jaringan & OLT --}}
                        <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">Serial Number</p>
                                <p class="font-mono font-bold text-slate-900 dark:text-white text-xs truncate">{{ $scannedOnu['serial_number'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">Redaman Optik (RX)</p>
                                <p class="font-mono font-bold text-xs {{ !empty($scannedOnu['rx_power']) && $scannedOnu['rx_power'] > -27 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ !empty($scannedOnu['rx_power']) ? $scannedOnu['rx_power'] . ' dBm' : 'Normal (-20.0 dBm)' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">Terhubung OLT</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200 text-xs truncate">{{ $scannedOnu['olt_name'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-semibold">PON Port</p>
                                <p class="font-mono font-bold text-slate-800 dark:text-slate-200 text-xs truncate">{{ $scannedOnu['pon_port'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Action Buttons Lapangan --}}
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('technician.diagnostic.index', array_filter(['onu_id' => $scannedOnu['id'] ?? null, 'sn' => $scannedOnu['serial_number'] ?? null])) }}"
                           class="py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm transition-all active:scale-95">
                            <span class="material-symbols-outlined notranslate text-sm" translate="no">troubleshoot</span>
                            <span>Diagnosa Sinyal</span>
                        </a>
                        <a href="{{ route('technician.swap-ont.index', ['old_sn' => $scannedOnu['serial_number']]) }}"
                           class="py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm transition-all active:scale-95">
                            <span class="material-symbols-outlined notranslate text-sm" translate="no">sync_alt</span>
                            <span>Ganti / Swap ONT</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Script untuk HTML5 Barcode/QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrcodeScanner = null;

        function startScanner() {
            document.getElementById('scanner-placeholder').classList.add('hidden');
            document.getElementById('camera-container').classList.remove('hidden');

            if(!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5Qrcode("qr-reader");
            }

            html5QrcodeScanner.start(
                { facingMode: "environment" }, 
                {
                    fps: 10,
                    qrbox: { width: 250, height: 100 }
                },
                (decodedText) => {
                    stopScanner();
                    let input = document.getElementById('sn-input');
                    if (input) {
                        input.value = decodedText.trim();
                        input.dispatchEvent(new Event('input'));
                    }
                    @this.set('serialNumber', decodedText.trim());
                    @this.scanBarcode();
                },
                (errorMessage) => {
                    // Ignore parse error on scanning frames
                }
            ).catch(err => {
                alert("Gagal mengakses kamera. Pastikan izin kamera telah diberikan.");
                stopScanner();
            });
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    document.getElementById('camera-container').classList.add('hidden');
                    document.getElementById('scanner-placeholder').classList.remove('hidden');
                }).catch(err => {
                    document.getElementById('camera-container').classList.add('hidden');
                    document.getElementById('scanner-placeholder').classList.remove('hidden');
                });
            } else {
                document.getElementById('camera-container').classList.add('hidden');
                document.getElementById('scanner-placeholder').classList.remove('hidden');
            }
        }
    </script>
</div>
