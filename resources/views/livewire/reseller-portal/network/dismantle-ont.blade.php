<div class="space-y-6 pb-12">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-xl text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-xl text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-rose-600 dark:text-rose-400">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header Card --}}
    <div class="bg-gradient-to-r from-rose-700 via-pink-700 to-indigo-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-4 -bottom-6 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined text-9xl">power_off</span>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider text-rose-100">Reseller Field Operations</span>
                <h1 class="text-2xl font-black mt-1 text-white tracking-tight">Cabut ONT Pelanggan (Dismantle)</h1>
                <p class="text-sm text-rose-100 mt-1">Penarikan perangkat ONT pelanggan berhenti berlangganan atau relokasi. Unit otomatis masuk ke stok tangan Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reseller-portal.network.swap-ont') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold backdrop-blur-md border border-white/20 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">sync_alt</span>
                    <span>Ganti ONT (Swap)</span>
                </a>
                <a href="{{ route('inventory.custodies.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold backdrop-blur-md border border-white/20 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">inventory_2</span>
                    <span>Stok di Tangan</span>
                </a>
            </div>
        </div>
    </div>

    @if($isCompleted)
        {{-- SUKSES DISMANTLE VIEW --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-emerald-300 dark:border-emerald-700 shadow-xl text-center space-y-6 animate-fadeIn max-w-2xl mx-auto">
            <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center shadow-inner">
                <span class="material-symbols-outlined text-5xl">task_alt</span>
            </div>

            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white">Pencabutan ONT Selesai!</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $completedSummary }}</p>
            </div>

            <div class="p-5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700 text-left space-y-3 text-sm">
                <div class="flex justify-between items-center pb-2.5 border-b border-slate-200 dark:border-slate-800">
                    <span class="text-slate-500">Nama Pelanggan:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $customer?->name }}</span>
                </div>
                <div class="flex justify-between items-center pb-2.5 border-b border-slate-200 dark:border-slate-800">
                    <span class="text-slate-500">SN Modem Ditarik:</span>
                    <span class="font-mono font-bold text-rose-600 dark:text-rose-400">{{ $onu?->serial_number }}</span>
                </div>
                <div class="flex justify-between items-center pb-2.5 border-b border-slate-200 dark:border-slate-800">
                    <span class="text-slate-500">Kondisi Fisik:</span>
                    <span class="font-bold {{ $deviceCondition === 'good' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $deviceCondition === 'good' ? 'Layak Pakai (Stok Tangan)' : 'Rusak / Afkir' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Alasan:</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $dismantleReason }}</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <button wire:click="resetForm" type="button" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-md transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">power_off</span>
                    <span>Tarik ONT Lainnya</span>
                </button>
                <a href="{{ route('reseller-portal.dashboard') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-bold transition-colors">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @else
        {{-- FORM DISMANTLE --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom Kiri: Pemilihan Perangkat & Pelanggan --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">search</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Cari Pelanggan / Modem</h3>
                            <p class="text-xs text-slate-500">Scan atau ketik SN modem / nama pelanggan</p>
                        </div>
                    </div>

                    {{-- Search Box --}}
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="searchQuery"
                               placeholder="Ketik SN / Nama / No HP..."
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-rose-500 font-mono">
                    </div>

                    {{-- Dropdown Hasil Pencarian --}}
                    @if(!empty($searchResults))
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl divide-y divide-slate-100 dark:divide-slate-700 max-h-60 overflow-y-auto bg-white dark:bg-slate-800 shadow-md">
                            @foreach($searchResults as $res)
                                <button type="button"
                                        wire:click="selectOnu({{ $res['id'] }})"
                                        class="w-full text-left p-3 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center justify-between">
                                    <div>
                                        <div class="font-mono font-bold text-xs text-rose-600 dark:text-rose-400">{{ $res['serial_number'] }}</div>
                                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $res['customer_service']['customer']['name'] ?? 'Tanpa Pelanggan' }}</div>
                                    </div>
                                    <span class="material-symbols-outlined text-sm text-slate-400">chevron_right</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Perangkat Terpilih --}}
                    @if($onu)
                        <div class="p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-xl space-y-2.5 text-xs animate-fadeIn">
                            <div class="flex items-center justify-between pb-2 border-b border-rose-200 dark:border-rose-800/60">
                                <span class="font-bold text-rose-900 dark:text-rose-200">Perangkat Siap Ditarik</span>
                                <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white font-bold text-[10px] uppercase">Terpilih</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">SN Modem:</span>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $onu->serial_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Model:</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $onu->model ?: 'ONT GPON' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Pelanggan:</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $customer?->name ?: '-' }}</span>
                            </div>
                            @if($onu->odp)
                                <div class="flex justify-between">
                                    <span class="text-slate-500">ODP:</span>
                                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $onu->odp->code ?? $onu->odp->name }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-6 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl text-center space-y-2">
                            <span class="material-symbols-outlined text-3xl text-slate-400">devices</span>
                            <p class="text-xs text-slate-500">Belum ada perangkat yang dipilih.<br>Cari modem atau pelanggan di atas.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan: Detail Formulir Pembongkaran --}}
            <div class="lg:col-span-2 space-y-6">
                <form wire:submit.prevent="executeDismantle" class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">checklist</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Formulir Penarikan Alat</h3>
                            <p class="text-xs text-slate-500">Konfirmasi alasan penarikan dan kondisi fisik unit</p>
                        </div>
                    </div>

                    {{-- Alasan Pembongkaran --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Alasan Penarikan <span class="text-rose-500">*</span></label>
                        <select wire:model="dismantleReason" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-rose-500">
                            <option value="Berhenti Berlangganan (Permintaan Pelanggan)">Berhenti Berlangganan (Permintaan Pelanggan / Churn)</option>
                            <option value="Menunggak Pembayaran (Isolir Permanen)">Menunggak Pembayaran (Isolir Permanen / Nunggak)</option>
                            <option value="Relokasi / Pindah Alamat">Relokasi / Pindah Alamat</option>
                            <option value="Modem Rusak Total / Diganti">Modem Rusak Total / Diganti</option>
                            <option value="Penertiban Perangkat">Penertiban Perangkat Liar / Non-Aktif</option>
                        </select>
                    </div>

                    {{-- Kondisi Fisik Alat --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Kondisi Fisik Modem Hasil Tarikan <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3 {{ $deviceCondition === 'good' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                                <input type="radio" wire:model.live="deviceCondition" value="good" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Masih Normal / Layak Pakai</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Otomatis masuk ke stok tangan Anda (+1) siap dipasang ke pelanggan lain.</div>
                                </div>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3 {{ $deviceCondition === 'damaged' ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                                <input type="radio" wire:model.live="deviceCondition" value="damaged" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Rusak / Mati / Afkir</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Dicatat rusak untuk proses retur/klaim garansi ke gudang pusat.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Checklist Kelengkapan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Checklist Kelengkapan yang Berhasil Ditarik</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                <input type="checkbox" wire:model="checklist.modem" class="rounded text-rose-600">
                                <span>Unit Modem ONT</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                <input type="checkbox" wire:model="checklist.adapter" class="rounded text-rose-600">
                                <span>Adaptor Power 12V</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                <input type="checkbox" wire:model="checklist.patchcord" class="rounded text-rose-600">
                                <span>Patchcord Optik</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                <input type="checkbox" wire:model="checklist.lan_cable" class="rounded text-rose-600">
                                <span>Kabel LAN UTP</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                <input type="checkbox" wire:model="checklist.box" class="rounded text-rose-600">
                                <span>Kardus / Box Asli</span>
                            </label>
                        </div>
                    </div>

                    {{-- Catatan Lapangan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Catatan Penarikan (Opsional)</label>
                        <textarea wire:model="notes" rows="2" placeholder="Catatan kondisi lokasi, nomor kontak, atau fisik modem..." class="w-full px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-xs"></textarea>
                    </div>

                    {{-- Foto Bukti --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Foto Bukti Penarikan (Opsional)</label>
                        <input type="file" wire:model="photoProof" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                @if(!$selectedOnuId) disabled @endif
                                class="px-6 py-3 bg-rose-600 hover:bg-rose-700 disabled:bg-slate-300 dark:disabled:bg-slate-700 text-white rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2">
                            <span wire:loading.remove wire:target="executeDismantle" class="material-symbols-outlined text-sm">power_off</span>
                            <span wire:loading wire:target="executeDismantle" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            <span>Proses Cabut ONT Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
