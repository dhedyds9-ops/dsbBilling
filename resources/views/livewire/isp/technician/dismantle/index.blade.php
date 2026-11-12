<div class="space-y-3.5 pb-6" x-data="dismantleManager()">
    @section('header_title', 'Pembongkaran / Cabut ONT')
    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-3.5 bg-emerald-500 text-white rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 bg-rose-500 text-white rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-sm">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header Banner --}}
    <div class="p-4 rounded-2xl shadow-md relative overflow-hidden text-white"
         style="background: linear-gradient(135deg, #334155 0%, #9f1239 50%, #0f172a 100%) !important; color: #ffffff !important;">
        <div class="absolute -right-2 -bottom-2 opacity-15 pointer-events-none">
            <span class="material-symbols-outlined text-8xl text-white notranslate" translate="no">power_off</span>
        </div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase tracking-wider text-white">Field Service Work Order</span>
                <h2 class="text-lg font-black mt-1 text-white">Pembongkaran Perangkat (Dismantle)</h2>
                <p class="text-xs text-rose-100">Penarikan modem ONT pelanggan churn, isolir, atau relokasi</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shrink-0">
                <span class="material-symbols-outlined text-2xl text-white notranslate" translate="no">power_off</span>
            </div>
        </div>
    </div>

    @if($isCompleted)
        {{-- SUKSES PEMBONGKARAN --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-emerald-300 dark:border-emerald-700 shadow-xl text-center space-y-4 animate-fadeIn">
            <div class="w-16 h-16 rounded-3xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center shadow-inner">
                <span class="material-symbols-outlined text-3xl">task_alt</span>
            </div>

            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Pembongkaran Selesai!</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $completedSummary }}</p>
            </div>

            <div class="flex flex-col gap-2 pt-2">
                <button wire:click="resetForm" type="button"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">power_off</span>
                    <span>Bongkar Perangkat Lainnya</span>
                </button>
                <a href="{{ route('technician.dashboard') }}"
                   class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-colors">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @else
        {{-- STEP 1: CARI / PILIH PELANGGAN & PERANGKAT --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-[10px]">1</span>
                    <span>Pelanggan & Perangkat yang Ditarik</span>
                </span>
                @if($selectedOnuId)
                    <button wire:click="$set('selectedOnuId', null)" type="button"
                            class="text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:underline">
                        Ganti Perangkat
                    </button>
                @endif
            </div>

            @if(!$selectedOnuId)
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input type="text" wire:model.live.debounce.300ms="searchQuery"
                               placeholder="Ketik Nama Pelanggan / SN ONT / PPPoE..."
                               class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-rose-500">
                    </div>
                    <button type="button" @click="openScanner()"
                            class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold border border-rose-200 dark:border-rose-800 flex items-center gap-1 shrink-0 active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                        <span>Scan</span>
                    </button>
                </div>

                @if(!empty($searchResults))
                    <div class="divide-y divide-slate-100 dark:divide-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-slate-900 max-h-60 overflow-y-auto">
                        @foreach($searchResults as $res)
                            <div wire:click="selectOnu({{ $res['onu_id'] }})"
                                 class="p-3 hover:bg-rose-50/70 dark:hover:bg-slate-800 cursor-pointer transition-colors flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $res['customer_name'] }}</h4>
                                    <p class="text-[10px] text-slate-500 truncate">PPPoE: {{ $res['pppoe_username'] }} • {{ $res['package'] }}</p>
                                    <p class="text-[10px] text-rose-600 dark:text-rose-400 font-mono font-bold truncate">ONT: {{ $res['serial_number'] }} ({{ $res['model'] }})</p>
                                </div>
                                <span class="px-2 py-1 bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 rounded-lg text-[10px] font-bold shrink-0">Pilih</span>
                            </div>
                        @endforeach
                    </div>
                @elseif(strlen(trim($searchQuery)) >= 2)
                    <p class="text-[11px] text-slate-400 text-center py-2">Tidak ditemukan pelanggan dengan ONT aktif.</p>
                @endif
            @else
                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-700/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $customer?->name ?? 'Pelanggan' }}</h4>
                            <p class="text-[11px] text-slate-500">{{ $customerService?->serviceProfile?->name ?? 'Layanan' }} • {{ $customer?->phone ?? '-' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            Siap Ditarik
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200 dark:border-slate-800 text-[11px]">
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">Serial Number (SN):</span>
                            <span class="font-mono font-black text-rose-600 dark:text-rose-400">{{ $onu->serial_number }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">Model Perangkat:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $onu->model ?: 'ONT GPON' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">PPPoE User:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $customerService?->pppoeUser?->username ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">Titik ODP:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $onu->odp?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- STEP 2: ALASAN, KONDISI & KELENGKAPAN --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-4">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                <span class="w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-[10px]">2</span>
                <span>Detail Penarikan Perangkat</span>
            </span>

            {{-- Alasan Pembongkaran --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alasan Penarikan / Pembongkaran</label>
                <select wire:model="dismantleReason"
                        class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                    <option value="Berhenti Berlangganan (Permintaan Pelanggan)">Berhenti Berlangganan (Permintaan Pelanggan / Churn)</option>
                    <option value="Menunggak / Isolir Permanen">Menunggak / Isolir Permanen</option>
                    <option value="Pindah Alamat / Relokasi">Pindah Alamat / Relokasi</option>
                    <option value="Perangkat Rusak Permanen">Perangkat Rusak Permanen</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            {{-- Kondisi Fisik Perangkat --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kondisi Perangkat Saat Ditarik</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="$set('deviceCondition', 'good')"
                            class="p-3 rounded-xl border text-left transition-colors {{ $deviceCondition === 'good' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-400 text-emerald-800 dark:text-emerald-200' : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-500' }}">
                        <div class="flex items-center gap-1.5 font-bold text-xs">
                            <span class="material-symbols-outlined text-sm text-emerald-600">check_circle</span>
                            <span>Bagus & Normal</span>
                        </div>
                        <p class="text-[10px] mt-1 text-slate-500 dark:text-slate-400">Unit masuk kembali ke Stok Saya teknisi (+1) untuk dipasang di tempat lain.</p>
                    </button>

                    <button type="button" wire:click="$set('deviceCondition', 'damaged')"
                            class="p-3 rounded-xl border text-left transition-colors {{ $deviceCondition === 'damaged' ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-400 text-rose-800 dark:text-rose-200' : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-500' }}">
                        <div class="flex items-center gap-1.5 font-bold text-xs">
                            <span class="material-symbols-outlined text-sm text-rose-600">dangerous</span>
                            <span>Rusak / Mati Total</span>
                        </div>
                        <p class="text-[10px] mt-1 text-slate-500 dark:text-slate-400">Unit afkir untuk disetor retur ke gudang pusat.</p>
                    </button>
                </div>
            </div>

            {{-- Checklist Kelengkapan yang Diserahkan --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Checklist Kelengkapan yang Ditarik</label>
                <div class="space-y-1.5 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklist.modem" class="rounded text-rose-600">
                        <span>Unit Modem ONT Utama</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklist.adapter" class="rounded text-rose-600">
                        <span>Power Adaptor 12V</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklist.patchcord" class="rounded text-rose-600">
                        <span>Kabel Patchcord Fiber Optik</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklist.lan_cable" class="rounded text-rose-600">
                        <span>Kabel LAN UTP RJ45</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklist.box" class="rounded text-rose-600">
                        <span>Kardus / Box Modem</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- STEP 3: BUKTI & TANDA TANGAN SERAH TERIMA --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-4">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                <span class="w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-[10px]">3</span>
                <span>Bukti Serah Terima Penarikan</span>
            </span>

            {{-- Foto Bukti --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Foto Perangkat yang Dicabut</label>
                <input type="file" wire:model="photoProof" accept="image/*" capture="environment"
                       class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                @if($photoProof)
                    <div class="mt-1.5 h-24 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                        <img src="{{ $photoProof->temporaryUrl() }}" class="w-full h-full object-cover">
                    </div>
                @endif
            </div>

            {{-- Digital Signature Pad Pelanggan --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-rose-500">draw</span>
                        <span>Tanda Tangan Pelanggan / Penghuni</span>
                    </label>
                    <button type="button" @click="clearSignature()" class="text-[10px] font-bold text-rose-500 hover:underline">
                        Hapus
                    </button>
                </div>
                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl bg-white dark:bg-slate-100 relative overflow-hidden h-28 touch-none">
                    <canvas id="dismantle-signature-pad" class="w-full h-full"></canvas>
                    <div x-show="!hasSignature" class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-400 text-xs">
                        Tanda tangan serah terima penarikan barang
                    </div>
                </div>
            </div>

            {{-- Catatan Teknisi --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Catatan Lapangan</label>
                <textarea wire:model="notes" rows="2" placeholder="Catatan kondisi perangkat atau keterangan pelanggan..."
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100"></textarea>
            </div>
        </div>

        {{-- TOMBOL EKSEKUSI --}}
        <div class="pt-2">
            <button type="button" @click="submitDismantle()"
                    class="w-full py-3.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white rounded-2xl text-sm font-black shadow-lg shadow-rose-500/25 flex items-center justify-center gap-2 active:scale-98 transition-all">
                <span wire:loading.remove wire:target="executeDismantle" class="material-symbols-outlined text-lg">power_off</span>
                <span wire:loading wire:target="executeDismantle" class="material-symbols-outlined animate-spin text-lg">autorenew</span>
                <span wire:loading.remove wire:target="executeDismantle">Konfirmasi Penarikan & Cabut Perangkat</span>
                <span wire:loading wire:target="executeDismantle">Memproses Pencabutan...</span>
            </button>
        </div>
    @endif

    {{-- MODAL KAMERA SCANNER BARCODE / QR --}}
    <div x-show="scannerOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 max-w-sm w-full border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/50 text-rose-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Scan Barcode / SN ONT</h3>
                </div>
                <button type="button" @click="closeScanner()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            <div class="rounded-2xl overflow-hidden border-2 border-rose-500 relative bg-black aspect-video flex items-center justify-center">
                <div id="dismantle-camera-reader" class="w-full h-full"></div>
            </div>

            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center">
                Arahkan kamera ke stiker barcode atau QR Code pada modem yang dicabut.
            </p>

            <button type="button" @click="closeScanner()"
                    class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                Tutup Kamera
            </button>
        </div>
    </div>

    <script>
        function dismantleManager() {
            return {
                scannerOpen: false,
                html5Scanner: null,
                hasSignature: false,

                init() {
                    this.$nextTick(() => {
                        this.initSignaturePad();
                    });
                },

                openScanner() {
                    this.scannerOpen = true;
                    this.$nextTick(() => {
                        if (!this.html5Scanner) {
                            this.html5Scanner = new Html5Qrcode("dismantle-camera-reader");
                        }
                        this.html5Scanner.start(
                            { facingMode: "environment" },
                            { fps: 10, qrbox: { width: 250, height: 120 } },
                            (decodedText) => {
                                this.closeScanner();
                                @this.set('searchQuery', decodedText.trim());
                            }
                        ).catch((err) => {
                            alert("Kamera tidak dapat diakses: " + err);
                            this.closeScanner();
                        });
                    });
                },

                closeScanner() {
                    if (this.html5Scanner) {
                        this.html5Scanner.stop().catch(() => {}).finally(() => {
                            this.scannerOpen = false;
                        });
                    } else {
                        this.scannerOpen = false;
                    }
                },

                initSignaturePad() {
                    const canvas = document.getElementById('dismantle-signature-pad');
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    let isDrawing = false;

                    const resizeCanvas = () => {
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        ctx.scale(ratio, ratio);
                        ctx.lineWidth = 2.5;
                        ctx.lineCap = 'round';
                        ctx.strokeStyle = '#1e293b';
                    };

                    resizeCanvas();
                    window.addEventListener('resize', resizeCanvas);

                    const getPos = (e) => {
                        const rect = canvas.getBoundingClientRect();
                        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                        return { x: clientX - rect.left, y: clientY - rect.top };
                    };

                    const start = (e) => {
                        isDrawing = true;
                        this.hasSignature = true;
                        const pos = getPos(e);
                        ctx.beginPath();
                        ctx.moveTo(pos.x, pos.y);
                    };

                    const draw = (e) => {
                        if (!isDrawing) return;
                        e.preventDefault();
                        const pos = getPos(e);
                        ctx.lineTo(pos.x, pos.y);
                        ctx.stroke();
                    };

                    const stop = () => {
                        if (isDrawing) {
                            isDrawing = false;
                            @this.set('digitalSignature', canvas.toDataURL());
                        }
                    };

                    canvas.addEventListener('mousedown', start);
                    canvas.addEventListener('mousemove', draw);
                    window.addEventListener('mouseup', stop);

                    canvas.addEventListener('touchstart', start, { passive: false });
                    canvas.addEventListener('touchmove', draw, { passive: false });
                    window.addEventListener('touchend', stop);
                },

                clearSignature() {
                    const canvas = document.getElementById('dismantle-signature-pad');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    this.hasSignature = false;
                    @this.set('digitalSignature', '');
                },

                submitDismantle() {
                    @this.call('executeDismantle');
                }
            };
        }
    </script>
</div>
