@section('header_title', 'Ganti / Swap ONT')

<div class="space-y-4 p-4 pb-24" x-data="swapOntManager()">
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
         style="background: linear-gradient(135deg, #4f46e5 0%, #2563eb 50%, #3730a3 100%) !important; color: #ffffff !important;">
        <div class="absolute -right-2 -bottom-2 opacity-15 pointer-events-none">
            <span class="material-symbols-outlined text-8xl text-white notranslate" translate="no">sync_alt</span>
        </div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase tracking-wider text-white">Field Service Tool</span>
                <h2 class="text-lg font-black mt-1 text-white">Ganti ONT Pelanggan</h2>
                <p class="text-xs text-indigo-100">1-Klik Transfer Akun PPPoE, Wi-Fi & Provisioning ke ONT Baru</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shrink-0">
                <span class="material-symbols-outlined text-2xl text-white notranslate" translate="no">router</span>
            </div>
        </div>
    </div>

    @if($swapCompleted)
        {{-- LAYAR SUKSES SWAP --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-emerald-300 dark:border-emerald-700 shadow-xl text-center space-y-4 animate-fadeIn">
            <div class="w-16 h-16 rounded-3xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center shadow-inner">
                <span class="material-symbols-outlined text-3xl">task_alt</span>
            </div>

            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Swap ONT Berhasil!</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $completedSummary }}</p>
            </div>

            <div class="p-3.5 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 text-left space-y-2 text-xs">
                <div class="flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-800">
                    <span class="text-slate-500">Pelanggan:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $customer?->name }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-800">
                    <span class="text-slate-500">ONT Baru:</span>
                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $newSerialNumber }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-800">
                    <span class="text-slate-500">SSID Wi-Fi:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $newWifiSsid }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Redaman Optik (OPM):</span>
                    <span class="font-bold text-emerald-600">{{ $opticalPower }} dBm</span>
                </div>
            </div>

            <div class="flex flex-col gap-2 pt-2">
                <button wire:click="resetForm" type="button"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">sync_alt</span>
                    <span>Ganti ONT Lainnya</span>
                </button>
                <a href="{{ route('technician.dashboard') }}"
                   class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-colors">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @else
        {{-- STEP 1: CARI / PILIH PELANGGAN & ONT LAMA --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px]">1</span>
                    <span>Pelanggan & ONT Lama</span>
                </span>
                @if($selectedCustomerId)
                    <button wire:click="$set('selectedCustomerId', null)" type="button"
                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Ganti Pelanggan
                    </button>
                @endif
            </div>

            @if(!$selectedCustomerId)
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input type="text" wire:model.live.debounce.300ms="searchQuery"
                           placeholder="Ketik Nama Pelanggan / Username PPPoE / SN Lama..."
                           class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                @if(!empty($searchResults))
                    <div class="divide-y divide-slate-100 dark:divide-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-slate-900 max-h-60 overflow-y-auto">
                        @foreach($searchResults as $res)
                            <div wire:click="selectCustomer({{ $res['id'] }})"
                                 class="p-3 hover:bg-indigo-50/70 dark:hover:bg-slate-800 cursor-pointer transition-colors flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $res['name'] }}</h4>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $res['code'] }} • PPPoE: {{ $res['pppoe_username'] }}</p>
                                    <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-mono font-medium truncate">ONT: {{ $res['onu_sn'] }} ({{ $res['onu_model'] }})</p>
                                </div>
                                <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg text-[10px] font-bold shrink-0">Pilih</span>
                            </div>
                        @endforeach
                    </div>
                @elseif(strlen(trim($searchQuery)) >= 2)
                    <p class="text-[11px] text-slate-400 text-center py-2">Tidak ditemukan pelanggan aktif yang sesuai.</p>
                @endif
            @else
                {{-- DETAIL PELANGGAN & ONT LAMA TERPILIH --}}
                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-700/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $customer?->name }}</h4>
                            <p class="text-[11px] text-slate-500">{{ $customerService?->serviceProfile?->name ?? 'Paket Internet' }} • {{ $customer?->phone }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            Aktif
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200 dark:border-slate-800 text-[11px]">
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">Username PPPoE:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $oldPppoeUsername }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">Password PPPoE:</span>
                            <span class="font-mono text-slate-600 dark:text-slate-400">••••••••</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">SN ONT Lama:</span>
                            <span class="font-mono font-black text-rose-600 dark:text-rose-400">{{ $oldSerialNumber }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">Model / Tipe:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $oldModel }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">SSID Wi-Fi Lama:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $oldWifiSsid }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase font-bold">Sandi Wi-Fi Lama:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $oldWifiPassword ?: '12345678' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- STEP 2: INPUT ONT BARU DENGAN SCANNER --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-4">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px]">2</span>
                <span>Perangkat ONT Baru</span>
            </span>

            {{-- Input Serial Number + Scan Kamera --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Serial Number (SN) ONT Baru <span class="text-rose-500">*</span>
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live.debounce.300ms="newSerialNumber"
                               placeholder="Contoh: ZTEGC85E2D31 / HWTC..."
                               class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="button" @click="openScanner()"
                            class="px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold border border-indigo-200 dark:border-indigo-800 flex items-center gap-1 shrink-0 active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                        <span>Scan</span>
                    </button>
                </div>
                @error('newSerialNumber') <span class="text-rose-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Model / Tipe Baru</label>
                    <input type="text" wire:model="newModel" placeholder="Contoh: ZTE F609"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">MAC Address (Opsional)</label>
                    <input type="text" wire:model="newMacAddress" placeholder="F4:CA:12:90:0F:AA"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono uppercase bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                </div>
            </div>

            {{-- Ambil dari Stok Saya --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Ambil dari Stok Saya (Potong Inventory)
                </label>
                <select wire:model="selectedMaterialId"
                        class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-medium">
                    <option value="">-- Pilih dari Stok Teknisi (Opsional) --</option>
                    @foreach($ontMaterials as $ontMat)
                        <option value="{{ $ontMat->id }}">
                            {{ $ontMat->name }} (Sisa: {{ $ontMat->stock }} {{ $ontMat->unit }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Stok ONT teknisi akan otomatis terpotong 1 unit saat swap berhasil.</p>
            </div>

            {{-- Konfigurasi Wi-Fi Baru (Otomatis mewarisi Wi-Fi lama) --}}
            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-xl border border-indigo-100 dark:border-indigo-900/40 space-y-2.5">
                <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-900 dark:text-indigo-300">
                    <span class="material-symbols-outlined text-sm text-indigo-500">wifi</span>
                    <span>Konfigurasi Wi-Fi Baru (Auto-Transfer)</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Nama Wi-Fi (SSID)</label>
                        <input type="text" wire:model="newWifiSsid"
                               class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Password Wi-Fi</label>
                        <input type="text" wire:model="newWifiPassword"
                               class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono font-bold bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">Wi-Fi lama otomatis dipasang ke ONT baru sehingga HP pelanggan tidak perlu memasukkan sandi ulang.</p>
            </div>

            {{-- Pengukuran Redaman Baru (OPM) --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Redaman Optik (OPM) ONT Baru <span class="text-rose-500">*</span>
                </label>
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach(['-18.5', '-19.0', '-20.5', '-21.0', '-22.0', '-23.5'] as $preset)
                        <button type="button" wire:click="setRedaman('{{ $preset }}')"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-mono font-bold border transition-colors
                                       {{ $opticalPower == $preset ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                            {{ $preset }} dBm
                        </button>
                    @endforeach
                </div>
                <div class="relative">
                    <input type="text" wire:model="opticalPower" placeholder="-20.5"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">dBm</span>
                </div>
                @error('opticalPower') <span class="text-rose-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Alasan Penggantian & Kondisi ONT Lama --}}
            <div class="space-y-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alasan Penggantian ONT</label>
                    <select wire:model="replacementReason"
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                        <option value="Modem Tersambar Petir / Korsleting Listrik">Modem Tersambar Petir / Korsleting Listrik</option>
                        <option value="Mati Total (Power IC Terbakar)">Mati Total (Power IC Terbakar)</option>
                        <option value="Port LAN / WLAN Rusak / Tidak Konek">Port LAN / WLAN Rusak / Tidak Konek</option>
                        <option value="Sinyal Wi-Fi Drop / Sangat Lemah">Sinyal Wi-Fi Drop / Sangat Lemah</option>
                        <option value="Port Optik / BOSA Rusak (LOS Merah)">Port Optik / BOSA Rusak (LOS Merah)</option>
                        <option value="Upgrade Perangkat ONT (Gigabit/Dual-Band)">Upgrade Perangkat ONT (Gigabit/Dual-Band)</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status ONT Lama yang Ditarik</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" wire:click="$set('oldDeviceCondition', 'rusak')"
                                class="py-2 px-3 rounded-xl text-xs font-bold border transition-colors flex items-center justify-center gap-1.5
                                       {{ $oldDeviceCondition === 'rusak' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700' }}">
                            <span class="material-symbols-outlined text-sm">dangerous</span>
                            <span>Rusak / Afkir</span>
                        </button>
                        <button type="button" wire:click="$set('oldDeviceCondition', 'berfungsi')"
                                class="py-2 px-3 rounded-xl text-xs font-bold border transition-colors flex items-center justify-center gap-1.5
                                       {{ $oldDeviceCondition === 'berfungsi' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-700' }}">
                            <span class="material-symbols-outlined text-sm">build</span>
                            <span>Perlu Cek Lab</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 3: BUKTI PEKERJAAN (FOTO & TANDA TANGAN) --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-4">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 uppercase tracking-wider">
                <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px]">3</span>
                <span>Bukti Pekerjaan Lapangan</span>
            </span>

            {{-- Foto Before & After --}}
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Foto ONT Lama (Before)</label>
                    <input type="file" wire:model="photoBefore" accept="image/*" capture="environment"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @if($photoBefore)
                        <div class="mt-1.5 h-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                            <img src="{{ $photoBefore->temporaryUrl() }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Foto ONT Baru (After)</label>
                    <input type="file" wire:model="photoAfter" accept="image/*" capture="environment"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @if($photoAfter)
                        <div class="mt-1.5 h-20 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                            <img src="{{ $photoAfter->temporaryUrl() }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Digital Signature Pad Pelanggan --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-indigo-500">draw</span>
                        <span>Tanda Tangan Pelanggan</span>
                    </label>
                    <button type="button" @click="clearSignature()" class="text-[10px] font-bold text-rose-500 hover:underline">
                        Hapus / Reset
                    </button>
                </div>
                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl bg-white dark:bg-slate-100 relative overflow-hidden h-28 touch-none">
                    <canvas id="swap-signature-pad" class="w-full h-full"></canvas>
                    <div x-show="!hasSignature" class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-400 text-xs">
                        Sentuh & goreskan tanda tangan pelanggan di sini
                    </div>
                </div>
            </div>

            {{-- Catatan Teknisi --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea wire:model="notes" rows="2" placeholder="Contoh: Kabel dropcore sempat ditarik ulang 5 meter, konektor SC diganti baru..."
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100"></textarea>
            </div>
        </div>

        {{-- TOMBOL EKSEKUSI TRANSFER & AKTIVASI --}}
        <div class="pt-2">
            <button type="button" @click="submitSwap()"
                    class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white rounded-2xl text-sm font-black shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 active:scale-98 transition-all">
                <span wire:loading.remove wire:target="executeSwap" class="material-symbols-outlined text-lg">bolt</span>
                <span wire:loading wire:target="executeSwap" class="material-symbols-outlined animate-spin text-lg">autorenew</span>
                <span wire:loading.remove wire:target="executeSwap">⚡ 1-Klik Transfer & Aktivasi ONT Baru</span>
                <span wire:loading wire:target="executeSwap">Memproses Konfigurasi & Provisioning...</span>
            </button>
        </div>
    @endif

    {{-- MODAL KAMERA SCANNER BARCODE / QR --}}
    <div x-show="scannerOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Scan Barcode / SN ONT</h3>
                </div>
                <button type="button" @click="closeScanner()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            <div class="rounded-2xl overflow-hidden border-2 border-indigo-500 relative bg-black aspect-video flex items-center justify-center">
                <div id="swap-camera-reader" class="w-full"></div>
            </div>

            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center">
                Arahkan kamera ke stiker barcode atau QR Code pada kardus / bagian belakang modem.
            </p>

            <button type="button" @click="closeScanner()"
                    class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                Tutup Kamera
            </button>
        </div>
    </div>

    {{-- Script HTML5 QR Code & Signature Pad --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function swapOntManager() {
            return {
                scannerOpen: false,
                html5Scanner: null,
                hasSignature: false,
                signaturePad: null,

                init() {
                    this.$nextTick(() => {
                        this.initSignaturePad();
                    });
                },

                initSignaturePad() {
                    const canvas = document.getElementById('swap-signature-pad');
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
                    const canvas = document.getElementById('swap-signature-pad');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    this.hasSignature = false;
                    @this.set('digitalSignature', '');
                },

                openScanner() {
                    this.scannerOpen = true;
                    this.$nextTick(() => {
                        if (!this.html5Scanner) {
                            this.html5Scanner = new Html5Qrcode("swap-camera-reader");
                        }
                        this.html5Scanner.start(
                            { facingMode: "environment" },
                            { fps: 10, qrbox: { width: 250, height: 120 } },
                            (decodedText) => {
                                this.closeScanner();
                                @this.set('newSerialNumber', decodedText.trim());
                                @this.call('updatedNewSerialNumber', decodedText.trim());
                            },
                            (err) => {}
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

                submitSwap() {
                    @this.call('executeSwap');
                }
            };
        }
    </script>
</div>
