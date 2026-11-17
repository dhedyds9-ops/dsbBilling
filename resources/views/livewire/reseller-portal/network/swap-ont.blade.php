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
    <div class="bg-gradient-to-r from-teal-600 via-emerald-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-4 -bottom-6 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined text-9xl">sync_alt</span>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider text-teal-100">Reseller Field Operations</span>
                <h1 class="text-2xl font-black mt-1 text-white tracking-tight">Ganti ONT Pelanggan (Swap Modem)</h1>
                <p class="text-sm text-teal-100 mt-1">Ganti modem pelanggan yang rusak dengan ONT baru tanpa perlu setup ulang username PPPoE & Wi-Fi.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reseller-portal.network.onus.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold backdrop-blur-md border border-white/20 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">list</span>
                    <span>Data ONU</span>
                </a>
                <a href="{{ route('reseller-portal.network.acs-devices.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold backdrop-blur-md border border-white/20 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">settings_remote</span>
                    <span>GenieACS</span>
                </a>
            </div>
        </div>
    </div>

    @if($swapCompleted)
        {{-- SUKSES SWAP VIEW --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-emerald-300 dark:border-emerald-700 shadow-xl text-center space-y-6 animate-fadeIn max-w-2xl mx-auto">
            <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center shadow-inner">
                <span class="material-symbols-outlined text-5xl">task_alt</span>
            </div>

            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white">
                    {{ $swapMode === 'accessory' ? 'Penggantian Aksesoris Berhasil!' : 'Ganti ONT Berhasil!' }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $completedSummary }}</p>
            </div>

            @if($swapMode === 'accessory')
                <div class="p-5 bg-amber-50/60 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-left space-y-3 text-sm">
                    <div class="flex justify-between items-center pb-2.5 border-b border-amber-200/60 dark:border-amber-800/60">
                        <span class="text-slate-500">Nama Pelanggan:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $customer?->name }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-amber-200/60 dark:border-amber-800/60">
                        <span class="text-slate-500">Modem Pelanggan:</span>
                        <span class="font-mono font-bold text-teal-600 dark:text-teal-400">{{ $oldSerialNumber }} (Tetap Aktif)</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-amber-200/60 dark:border-amber-800/60">
                        <span class="text-slate-500">Material / Aksesoris:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">
                            {{ $accessoryType === 'adaptor' ? 'Adaptor 12V' : ($accessoryType === 'fatcore' ? 'Fast Connector (Fatcore)' : ($accessoryType === 'patchcord' ? 'Patchcord Optik' : 'Material Aksesoris')) }} ({{ $accessoryQuantity }} unit)
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Status Stok:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Tercatat di Riwayat & Stok Berkurang</span>
                    </div>
                </div>
            @else
                <div class="p-5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700 text-left space-y-3 text-sm">
                    <div class="flex justify-between items-center pb-2.5 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">Nama Pelanggan:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $customer?->name }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">ONT Pengganti (Baru):</span>
                        <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $newSerialNumber }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500">SSID Wi-Fi Ditransfer:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $newWifiSsid }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Redaman Optik (OPM):</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $opticalPower }} dBm</span>
                    </div>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-3 pt-3">
                <button wire:click="resetForm" type="button"
                        class="flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-bold shadow-md transition-colors flex items-center justify-center gap-2 cursor-pointer">
                    <span class="material-symbols-outlined text-lg">sync_alt</span>
                    <span>Ganti ONT Lainnya</span>
                </button>
                <a href="{{ route('reseller-portal.network.onus.index') }}"
                   class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-bold transition-colors text-center">
                    Lihat Data ONU
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- KOLOM KIRI: PILIH PELANGGAN & DETAIL ONT LAMA (5 COLS) --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- KARTU 1: PENCARIAN PELANGGAN --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">1</span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Pilih Pelanggan / ONT Lama</h3>
                        </div>
                        @if($customer)
                            <button wire:click="resetForm" type="button" class="text-xs text-rose-500 hover:underline font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">close</span>
                                <span>Ganti</span>
                            </button>
                        @endif
                    </div>

                    @if(!$customer)
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Cari Pelanggan atau SN Modem Lama</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-lg">search</span>
                                <input type="text"
                                       wire:model.live.debounce.300ms="searchQuery"
                                       placeholder="Ketik Nama, No HP, User PPPoE, atau SN ONT..."
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                            </div>

                            {{-- Dropdown Hasil Pencarian --}}
                            @if(!empty($searchResults))
                                <div class="mt-2 divide-y divide-slate-100 dark:divide-slate-700/50 max-h-64 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg">
                                    @foreach($searchResults as $res)
                                        <div wire:click="selectCustomer({{ $res['id'] }})"
                                             class="p-3 hover:bg-teal-50 dark:hover:bg-teal-950/40 cursor-pointer transition-colors space-y-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $res['name'] }}</span>
                                                <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{{ $res['code'] }}</span>
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-3">
                                                <span>PPPoE: <strong class="text-teal-600 dark:text-teal-400">{{ $res['pppoe_username'] }}</strong></span>
                                                <span>SN: <strong class="font-mono text-indigo-600 dark:text-indigo-400">{{ $res['onu_sn'] }}</strong></span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(strlen($searchQuery) >= 2)
                                <p class="text-xs text-slate-400 italic mt-2 text-center">Tidak ditemukan pelanggan aktif milik reseller Anda yang cocok.</p>
                            @endif
                        </div>
                    @else
                        {{-- Kartu Info Pelanggan Terpilih --}}
                        <div class="p-4 bg-teal-50/60 dark:bg-teal-950/30 rounded-xl border border-teal-200 dark:border-teal-800/60 space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-black text-slate-900 dark:text-white text-base">{{ $customer->name }}</h4>
                                    <p class="text-xs text-slate-500 font-mono">{{ $customer->customer_code ?? $customer->code ?? ('CUST-' . $customer->id) }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 text-[10px] font-bold">Aktif</span>
                            </div>
                            <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1 pt-1 border-t border-teal-200/60 dark:border-teal-800/40">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">No. WhatsApp / HP:</span>
                                    <span class="font-semibold">{{ $customer->phone ?: '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Alamat:</span>
                                    <span class="font-semibold text-right truncate max-w-[200px]">{{ $customer->address ?: '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Paket Layanan:</span>
                                    <span class="font-bold text-teal-600 dark:text-teal-400">{{ $customerService?->serviceProfile?->name ?? 'Layanan Aktif' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- KARTU 2: DETAIL ONT LAMA (YANG DIGANTI) --}}
                @if($oldOnu)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700/50">
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-rose-500 text-base">device_reset</span>
                                <span>Informasi Modem Lama (Akan Diganti)</span>
                            </h3>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 font-bold">Status: Replaced</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 block">Serial Number Lama</span>
                                <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">{{ $oldSerialNumber }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 block">Model & Vendor</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $oldVendor }} {{ $oldModel }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 block">User PPPoE</span>
                                <span class="font-mono font-bold text-teal-600 dark:text-teal-400">{{ $oldPppoeUsername }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 block">ODP / Distribusi</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $oldOdpName }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 block">SSID Wi-Fi Eksisting</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 truncate block">{{ $oldWifiSsid }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 block">Password Wi-Fi</span>
                                <span class="font-mono font-semibold text-slate-800 dark:text-slate-200 truncate block">{{ $oldWifiPassword }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- KOLOM KANAN: FORM ONT BARU & EKSEKUSI (7 COLS) --}}
            <div class="lg:col-span-7">
                <form wire:submit.prevent="{{ $swapMode === 'accessory' ? 'executeAccessoryReplacement' : 'executeSwap' }}" class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="w-7 h-7 rounded-lg bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">2</span>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                {{ $swapMode === 'accessory' ? 'Penggantian Adaptor / Aksesoris' : 'Konfigurasi ONT Baru & Migrasi' }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                {{ $swapMode === 'accessory' ? 'Ganti aksesoris atau perlengkapan tanpa perlu mengganti unit modem pelanggan.' : 'Konfigurasi WiFi dan PPPoE akan ditransfer otomatis ke modem pengganti.' }}
                            </p>
                        </div>
                    </div>

                    @if(!$selectedCustomerId)
                        <div class="p-8 text-center bg-slate-50 dark:bg-slate-900/30 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-slate-400">
                            <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">touch_app</span>
                            <p class="text-sm font-semibold mt-2">Silakan pilih pelanggan terlebih dahulu di kolom sebelah kiri.</p>
                        </div>
                    @else
                        {{-- TABS PILIHAN MODE: GANTI UNIT ONT vs GANTI ADAPTOR/AKSESORIS --}}
                        <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-100 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <button type="button"
                                    wire:click="$set('swapMode', 'ont')"
                                    class="py-2.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 {{ $swapMode === 'ont' ? 'bg-white dark:bg-slate-800 text-teal-600 dark:text-teal-400 shadow-sm border border-teal-500/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <span class="material-symbols-outlined text-base">router</span>
                                <span>Ganti Unit Modem (Swap ONT)</span>
                            </button>
                            <button type="button"
                                    wire:click="$set('swapMode', 'accessory')"
                                    class="py-2.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 {{ $swapMode === 'accessory' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 shadow-sm border border-amber-500/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <span class="material-symbols-outlined text-base">power</span>
                                <span>Ganti Adaptor / Aksesoris</span>
                            </button>
                        </div>

                        @if($swapMode === 'accessory')
                            {{-- MODE B: GANTI ADAPTOR / AKSESORIS SAJA --}}
                            <div class="space-y-4">
                                {{-- Banner Info Modem Tetap --}}
                                <div class="p-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-xl flex items-start gap-3">
                                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-xl mt-0.5">info</span>
                                    <div class="text-xs text-amber-900 dark:text-amber-200">
                                        <p class="font-bold">Modem Pelanggan Tetap Digunakan & TIDAK Diganti</p>
                                        <p class="mt-0.5 text-amber-800 dark:text-amber-300">
                                            Modem SN <strong class="font-mono font-bold">{{ $oldSerialNumber ?: 'Eksisting' }}</strong> tetap aktif di rumah pelanggan. Anda hanya mencatat penggantian suku cadang/material berikut dari stok Anda.
                                        </p>
                                    </div>
                                </div>

                                {{-- Pilihan Jenis Aksesoris --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                                        Pilih Material / Aksesoris yang Diganti <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        {{-- ADAPTOR 12V --}}
                                        <div wire:click="$set('accessoryType', 'adaptor')"
                                             class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ $accessoryType === 'adaptor' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-slate-50 dark:bg-slate-900/40' }}">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-2xl">power</span>
                                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ $accessoryType === 'adaptor' ? 'border-amber-500 bg-amber-500' : 'border-slate-300 dark:border-slate-600' }}">
                                                    @if($accessoryType === 'adaptor')
                                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="font-bold text-xs text-slate-900 dark:text-white">Adaptor 12V</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Power Supply ONT</div>
                                            <div class="mt-2 text-[10px] font-semibold px-2 py-0.5 rounded inline-block {{ $availableAdaptorStock > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                                Stok Anda: {{ $availableAdaptorStock }} unit
                                            </div>
                                        </div>

                                        {{-- FAST CONNECTOR / FATCORE --}}
                                        <div wire:click="$set('accessoryType', 'fatcore')"
                                             class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ $accessoryType === 'fatcore' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-slate-50 dark:bg-slate-900/40' }}">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-2xl">cable</span>
                                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ $accessoryType === 'fatcore' ? 'border-amber-500 bg-amber-500' : 'border-slate-300 dark:border-slate-600' }}">
                                                    @if($accessoryType === 'fatcore')
                                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="font-bold text-xs text-slate-900 dark:text-white">Fast Connector</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Fatcore / Konektor SC</div>
                                            <div class="mt-2 text-[10px] font-semibold px-2 py-0.5 rounded inline-block {{ $availableFatcoreStock > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                                Stok Anda: {{ $availableFatcoreStock }} pcs
                                            </div>
                                        </div>

                                        {{-- PATCHCORD FIBER OPTIC --}}
                                        <div wire:click="$set('accessoryType', 'patchcord')"
                                             class="p-3.5 rounded-xl border-2 cursor-pointer transition-all {{ $accessoryType === 'patchcord' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 shadow-sm' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-slate-50 dark:bg-slate-900/40' }}">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-2xl">settings_ethernet</span>
                                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ $accessoryType === 'patchcord' ? 'border-amber-500 bg-amber-500' : 'border-slate-300 dark:border-slate-600' }}">
                                                    @if($accessoryType === 'patchcord')
                                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="font-bold text-xs text-slate-900 dark:text-white">Patchcord Optik</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kabel SC-UPC / APC</div>
                                            <div class="mt-2 text-[10px] font-semibold px-2 py-0.5 rounded inline-block {{ $availablePatchcordStock > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                                Stok Anda: {{ $availablePatchcordStock }} pcs
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Pilihan Spesifik dari Custody (Opsional) --}}
                                @if($custodyAccessories->isNotEmpty())
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                            Pilih Spesifik dari Stok Custody Anda (Opsional)
                                        </label>
                                        <select wire:model="selectedAccessoryItemId" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium">
                                            <option value="">-- Otomatis Sesuai Tipe di Atas --</option>
                                            @foreach($custodyAccessories as $ca)
                                                <option value="{{ $ca->item_id }}">{{ $ca->name }} (Sisa: {{ $ca->stock }} {{ $ca->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- JUMLAH BARANG --}}
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                            Jumlah yang Diganti <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="1" min="1" wire:model="accessoryQuantity" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white">
                                    </div>

                                    {{-- ALASAN PENGGANTIAN AKSESORIS --}}
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                            Alasan Penggantian <span class="text-rose-500">*</span>
                                        </label>
                                        <select wire:model="accessoryReason" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold">
                                            <option value="Adaptor Rusak / Tidak Menyala (Mati Total)">Adaptor Mati Total / Tidak Ada Daya</option>
                                            <option value="Adaptor Meledak / Bau Gosong / Korslet">Adaptor Korslet / Meledak</option>
                                            <option value="Adaptor Drop Voltase / Arus Kurang">Adaptor Drop Voltase</option>
                                            <option value="Fast Connector Redaman Drop / Patah">Fast Connector Redaman Drop / Patah</option>
                                            <option value="Patchcord Tertekuk / Putus Digigit Tikus">Patchcord Tertekuk / Putus</option>
                                            <option value="Konektor Longgar / Sering LOS">Konektor Longgar / Sering LOS</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>

                                    {{-- CATATAN LAPANGAN --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan Pekerjaan</label>
                                        <textarea wire:model="accessoryNotes" rows="2" placeholder="Catatan teknis penggantian aksesoris..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white"></textarea>
                                    </div>
                                </div>

                                {{-- TOMBOL SUBMIT AKSESORIS --}}
                                <div class="pt-4 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                                    <span class="text-xs text-slate-400">Modem: <code class="font-mono text-teal-600 dark:text-teal-400 font-bold">{{ $oldSerialNumber ?: '-' }}</code></span>
                                    <button type="submit"
                                            wire:loading.attr="disabled"
                                            class="px-6 py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                                        <span wire:loading.remove class="material-symbols-outlined text-lg">power</span>
                                        <span wire:loading class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                                        <span wire:loading.remove>Simpan Penggantian Aksesoris</span>
                                        <span wire:loading>Menyimpan...</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- MODE A: GANTI UNIT ONT LAMA KE BARU --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- SN ONT BARU --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Serial Number (SN) ONT Baru <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-lg">qr_code_scanner</span>
                                        <input type="text"
                                               wire:model.live.debounce.400ms="newSerialNumber"
                                               placeholder="Contoh: ZTEGC1234567 / HWTC12345678"
                                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/60 border @error('newSerialNumber') border-rose-500 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl font-mono text-sm font-bold uppercase text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                                    </div>
                                    @error('newSerialNumber')
                                        <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- MODEL & VENDOR DETECTED --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Model ONT Baru</label>
                                    <input type="text"
                                           wire:model="newModel"
                                           placeholder="Auto terdeteksi dari SN"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white">
                                </div>

                                {{-- REDAMAN OPTIK OPM --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Ukur Redaman Optik (OPM dBm) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               wire:model="opticalPower"
                                               placeholder="-20.5"
                                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border @error('opticalPower') border-rose-500 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white">
                                        <div class="absolute right-2 top-2 flex gap-1">
                                            <button type="button" wire:click="setRedaman('-18.5')" class="px-2 py-1 text-[10px] bg-slate-200 dark:bg-slate-700 rounded hover:bg-teal-100 font-mono font-bold">-18.5</button>
                                            <button type="button" wire:click="setRedaman('-20.5')" class="px-2 py-1 text-[10px] bg-slate-200 dark:bg-slate-700 rounded hover:bg-teal-100 font-mono font-bold">-20.5</button>
                                            <button type="button" wire:click="setRedaman('-23.0')" class="px-2 py-1 text-[10px] bg-slate-200 dark:bg-slate-700 rounded hover:bg-teal-100 font-mono font-bold">-23.0</button>
                                        </div>
                                    </div>
                                    @error('opticalPower')
                                        <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- SSID WI-FI BARU (DEFAULT SAMA DENGAN LAMA) --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        SSID Wi-Fi Baru <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           wire:model="newWifiSsid"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border @error('newWifiSsid') border-rose-500 @else border-slate-200 dark:border-slate-700 @enderror rounded-xl text-sm font-semibold text-slate-900 dark:text-white">
                                    @error('newWifiSsid')
                                        <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- PASSWORD WI-FI BARU --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Password Wi-Fi Baru
                                    </label>
                                    <input type="text"
                                           wire:model="newWifiPassword"
                                           placeholder="Minimal 8 karakter jika mode WPA2"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white">
                                </div>

                                {{-- ALASAN PENGGANTIAN --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alasan Penggantian</label>
                                    <select wire:model="replacementReason" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold">
                                        <option value="Modem Rusak / Tidak Menyala">Modem Rusak / Mati Total</option>
                                        <option value="Modem Tersambar Petir / Korsleting Listrik">Tersambar Petir / Korslet</option>
                                        <option value="Port LAN Rusak / Indikator LOS Berkedip">Port LAN Rusak / LOS Error</option>
                                        <option value="Wi-Fi Melemah / Sering Restart Sendiri">Wi-Fi Lemah / Sering Restart</option>
                                        <option value="Upgrade Perangkat Dual-Band (5GHz)">Upgrade Perangkat (Dual Band)</option>
                                    </select>
                                </div>

                                {{-- KONDISI PERANGKAT LAMA --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kondisi Modem Lama</label>
                                    <select wire:model="oldDeviceCondition" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-semibold">
                                        <option value="rusak">Rusak Total / Afkir</option>
                                        <option value="berfungsi">Masih Berfungsi (Refurbish / Lab)</option>
                                    </select>
                                </div>

                                {{-- CATATAN TAMBAHAN --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan Teknisi / Reseller</label>
                                    <textarea wire:model="notes" rows="2" placeholder="Catatan tambahan pekerjaan di lapangan..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white"></textarea>
                                </div>
                            </div>

                            {{-- TOMBOL SUBMIT EKSEKUSI --}}
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                                <span class="text-xs text-slate-400">Idempotent TX: <code class="font-mono text-[10px]">{{ substr($transaction_id, 0, 15) }}...</code></span>
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="px-6 py-3 bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                                    <span wire:loading.remove class="material-symbols-outlined text-lg">sync_alt</span>
                                    <span wire:loading class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                                    <span wire:loading.remove>Eksekusi Ganti ONT</span>
                                    <span wire:loading>Memproses Swap...</span>
                                </button>
                            </div>
                        @endif
                    @endif
                </form>
            </div>
        </div>
    @endif
</div>
