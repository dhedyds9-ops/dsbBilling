<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-3xl">category</span>
                <span>Master Barang (Katalog Material ISP)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kelola data material, perangkat aktif (ONU/Router), kabel dropcore, dan aksesoris jaringan.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Tambah Barang Baru</span>
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-4 shadow-xs">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <div class="relative flex-1 min-w-[280px]">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama barang, SKU, merek, model..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-900 dark:text-slate-100 placeholder:text-slate-400">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[220px] flex-1 sm:flex-initial">
                    <select wire:model.live="categoryFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-900 dark:text-slate-100 cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[220px] flex-1 sm:flex-initial">
                    <select wire:model.live="statusFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-900 dark:text-slate-100 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="active">Hanya Aktif</option>
                        <option value="serialized">Perangkat Berserial (SN)</option>
                        <option value="low_stock">Stok Menipis (Kritis)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5">SKU & Kode</th>
                        <th class="px-4 py-3.5">Nama Barang & Spek</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5 text-right">Stok Fisik</th>
                        <th class="px-4 py-3.5">Tipe Pelacakan</th>
                        <th class="px-4 py-3.5 text-right">Harga Beli</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($items as $item)
                        @php
                            $totalStock = $item->total_stock;
                            $minStock = (int) $item->min_stock;
                            $isLow = $totalStock > 0 && $totalStock <= $minStock;
                            $isOut = $totalStock <= 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $item->sku }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $item->name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $item->brand ? $item->brand . ' ' : '' }}{{ $item->model ?: '' }}
                                    @if($item->branch)
                                        <span class="text-indigo-500">• Cabang: {{ $item->branch->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="font-black text-sm {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900 dark:text-slate-100') }}">
                                    {{ number_format($totalStock, 0, ',', '.') }} {{ $item->unit }}
                                </div>
                                <div class="text-[10px] text-slate-400">Min: {{ $minStock }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($item->is_serialized)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                            <span class="material-symbols-outlined" style="font-size:12px">qr_code_scanner</span>
                                            <span>Serial Number</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                            Non-Serial
                                        </span>
                                    @endif

                                    @if($item->is_reusable)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                                            Reusable
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-medium">
                                {{ $item->cost_price ? 'Rp' . number_format($item->cost_price, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($item->is_serialized)
                                        <button wire:click="openSerialsModal({{ $item->id }})" type="button" title="Lihat Daftar Serial Number"
                                                class="p-1.5 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-950/50 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined" style="font-size:18px">devices</span>
                                        </button>
                                    @endif

                                    <button wire:click="openEditModal({{ $item->id }})" type="button" title="Edit Barang"
                                            class="p-1.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                                    </button>

                                    <button wire:click="deleteItem({{ $item->id }})" wire:confirm="Hapus barang '{{ $item->name }}' dari katalog?" type="button" title="Hapus Barang"
                                            class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <span class="material-symbols-outlined text-4xl mb-2">category</span>
                                <p class="text-xs font-semibold">Belum ada barang yang terdaftar di katalog.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $items->links() }}
        </div>
    </div>

    {{-- MODAL TAMBAH / EDIT BARANG --}}
    @if($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-lg w-full space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-600">{{ $itemId ? 'edit' : 'add_box' }}</span>
                        <span>{{ $itemId ? 'Edit Barang' : 'Tambah Barang Baru' }}</span>
                    </h3>
                    <button wire:click="$set('showFormModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">SKU / Kode Barang *</label>
                            <input type="text" wire:model="sku" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono uppercase bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            @error('sku') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Kategori *</label>
                            <select wire:model.live="category" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Nama Barang *</label>
                        <input type="text" wire:model="name" placeholder="Contoh: ONT GPON ZTE F609 V3 / Dropcore 1 Core 1000m"
                               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        @error('name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Satuan *</label>
                            <select wire:model="unit" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                <option value="unit">unit</option>
                                <option value="pcs">pcs</option>
                                <option value="meter">meter</option>
                                <option value="roll">roll</option>
                                <option value="set">set</option>
                                <option value="pack">pack</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Merek (Vendor)</label>
                            <select wire:model="brand" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Vendor / Merek --</option>
                                @foreach($brandList as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Model / Tipe</label>
                            <input type="text" wire:model="model" placeholder="F609, HG8245H..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Harga Beli / Perolehan (Rp)</label>
                            <input type="number" wire:model="cost_price" placeholder="0" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Min. Stok Kritis</label>
                            <input type="number" wire:model="min_stock" min="0" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        </div>
                    </div>

                    {{-- Toggle Options --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl space-y-2 border border-slate-100 dark:border-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_serialized" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Wajib Lacak Serial Number (Khusus Modem ONT / Router)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_reusable" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Barang Reusable (Dapat dibongkar/dismantle & digunakan ulang)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Status Aktif (Dapat dipilih dalam transaksi)</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan / Spesifikasi Tambahan</label>
                        <textarea wire:model="description" rows="2" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <div class="flex gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button wire:click="$set('showFormModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button wire:click="saveItem" type="button"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">
                        Simpan Barang
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DAFTAR SERIAL NUMBER UNIT --}}
    @if($showSerialsModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-2xl w-full space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-600">devices</span>
                            <span>Unit Serial: {{ $selectedItemNameForSerials }}</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Daftar fisik unit dan status pelacakan individual.</p>
                    </div>
                    <button wire:click="$set('showSerialsModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Search & Barcode Scanner Controls --}}
                <div x-data="serialScanner()" class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 notranslate" translate="no" style="font-size:16px">search</span>
                            <input type="text"
                                   id="serial-scanner-input"
                                   autofocus
                                   wire:model.live.debounce.150ms="serialSearch"
                                   @keydown.enter.prevent="$wire.set('serialSearch', $event.target.value.trim())"
                                   placeholder="Cari / scan serial number (alat scanner kasir / barcode)..."
                                   class="w-full pl-9 pr-8 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-mono focus:ring-2 focus:ring-purple-500 shadow-xs">
                            @if(!empty($serialSearch))
                                <button type="button" wire:click="$set('serialSearch', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <span class="material-symbols-outlined text-[15px] notranslate" translate="no">cancel</span>
                                </button>
                            @endif
                        </div>

                        {{-- Tombol Scan Kamera HP --}}
                        <button type="button" @click="openCamera()"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 rounded-xl text-xs font-bold transition-all shadow-xs shrink-0 cursor-pointer"
                                title="Pindai Barcode menggunakan Kamera HP">
                            <span class="material-symbols-outlined text-sm notranslate" translate="no">qr_code_scanner</span>
                            <span class="hidden sm:inline">Scan HP</span>
                        </button>
                    </div>

                    {{-- Camera Scanner Overlay --}}
                    <div x-show="cameraOpen" x-cloak class="p-3 bg-slate-900 rounded-2xl space-y-2 border border-purple-500/40">
                        <div class="flex items-center justify-between text-white text-xs">
                            <span class="flex items-center gap-1.5 font-bold text-purple-300">
                                <span class="material-symbols-outlined text-xs notranslate" translate="no">photo_camera</span>
                                Arahkan kamera ke Barcode / QR Serial Number
                            </span>
                            <button type="button" @click="closeCamera()" class="text-slate-400 hover:text-white">
                                <span class="material-symbols-outlined text-sm notranslate" translate="no">close</span>
                            </button>
                        </div>
                        <div id="sn-barcode-reader" class="rounded-xl overflow-hidden bg-black min-h-[160px] max-h-56"></div>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="px-3 py-2">Serial Number</th>
                                <th class="px-3 py-2">Lokasi / Gudang</th>
                                <th class="px-3 py-2">Pemegang / Pelanggan</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($serials as $sn)
                                <tr>
                                    <td class="px-3 py-2 font-mono font-bold text-slate-900 dark:text-slate-100">{{ $sn->serial_number }}</td>
                                    <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $sn->warehouse->name ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        @if($sn->customer)
                                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">Pelanggan: {{ $sn->customer->name }}</span>
                                        @elseif($sn->custodian)
                                            <span class="text-amber-600 dark:text-amber-400 font-bold">Pegang: {{ $sn->custodian->name }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold
                                            {{ $sn->status === 'installed' ? 'bg-emerald-100 text-emerald-700' : ($sn->status === 'in_warehouse' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ str_replace('_', ' ', strtoupper($sn->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">Belum ada unit serial terdaftar untuk barang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($serials instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div>{{ $serials->links() }}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- HTML5 QR/Barcode Scanner Script --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function serialScanner() {
            return {
                cameraOpen: false,
                html5QrCode: null,

                init() {
                    this.$nextTick(() => {
                        const input = document.getElementById('serial-scanner-input');
                        if (input) input.focus();
                    });
                },

                openCamera() {
                    this.cameraOpen = true;
                    this.$nextTick(() => {
                        if (!this.html5QrCode) {
                            this.html5QrCode = new Html5Qrcode("sn-barcode-reader");
                        }
                        this.html5QrCode.start(
                            { facingMode: "environment" },
                            { fps: 12, qrbox: { width: 280, height: 140 } },
                            (decodedText) => {
                                const clean = decodedText.trim();
                                @this.set('serialSearch', clean);
                                this.closeCamera();
                                if (navigator.vibrate) navigator.vibrate(100);
                            },
                            () => {}
                        ).catch((err) => {
                            alert("Kamera tidak dapat diakses atau izin ditolak: " + err);
                            this.closeCamera();
                        });
                    });
                },

                closeCamera() {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().catch(() => {}).finally(() => {
                            this.cameraOpen = false;
                        });
                    } else {
                        this.cameraOpen = false;
                    }
                }
            };
        }
    </script>
</div>
