<div class="space-y-3.5 pb-6" x-data="materialManager()">
    @section('header_title', 'Stok & Material Teknisi')
    
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

    {{-- Tabs Navigasi: Stok Saya, Gudang Kantor, Riwayat Pemakaian, Status Permintaan --}}
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-2xl flex items-center shadow-inner gap-1 overflow-x-auto">
        <button wire:click="switchTab('stok')" type="button"
            class="flex-1 min-w-[110px] py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'stok' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">work</span>
            <span>Stok Saya</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $activeTab === 'stok' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300' : 'bg-slate-300 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $materials->count() }}
            </span>
        </button>

        <button wire:click="switchTab('gudang')" type="button"
            class="flex-1 min-w-[110px] py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'gudang' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">warehouse</span>
            <span>Gudang Kantor</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $activeTab === 'gudang' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300' : 'bg-slate-300 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $warehouseMaterials->count() }}
            </span>
        </button>

        <button wire:click="switchTab('pemakaian')" type="button"
            class="flex-1 min-w-[110px] py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'pemakaian' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Pemakaian</span>
        </button>

        <button wire:click="switchTab('permintaan')" type="button"
            class="flex-1 min-w-[110px] py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'permintaan' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">receipt_long</span>
            <span>Permintaan</span>
        </button>
    </div>

    {{-- Action Bar & Search --}}
    <div class="flex items-center gap-2">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama barang, SN, atau SKU..."
                   class="w-full pl-10 pr-9 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm">
            <button type="button" @click="openScanner()" title="Scan Barcode / SN Perangkat"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 p-1">
                <span class="material-symbols-outlined" style="font-size:18px">photo_camera</span>
            </button>
        </div>

        <button wire:click="openRequestModal()" type="button"
                class="px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors shrink-0">
            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
            <span>Minta Barang</span>
        </button>
    </div>

    {{-- TAB 1: STOK SAYA (BAGASI TEKNISI - REAL CUSTODY) --}}
    @if($activeTab === 'stok')
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Barang fisik resmi perusahaan yang saat ini berada dalam penguasaan (Custody) Anda.
                </p>
                <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
                    Total: {{ $materials->count() }} unit/posisi
                </span>
            </div>

            @forelse($materials as $custody)
                @php
                    $item = $custody->item;
                    $availQty = (float) $custody->available_quantity;
                    $serial = $custody->itemSerial?->serial_number;

                    $catIcon = match(strtolower($item->category ?? '')) {
                        'kabel' => 'cable',
                        'konektor' => 'settings_ethernet',
                        'perangkat aktif' => 'router',
                        default => 'category',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm flex items-center justify-between gap-3 hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:22px">{{ $catIcon }}</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug truncate">
                                {{ $item->name ?? 'Material #' . $custody->item_id }}
                            </h4>
                            <div class="flex items-center flex-wrap gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] font-mono">{{ $item->sku ?? 'MAT' }}</span>
                                <span>•</span>
                                <span>{{ $item->category ?? 'Material' }}</span>
                                @if($serial)
                                    <span>•</span>
                                    <span class="bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded font-mono font-bold text-[10px] border border-indigo-200 dark:border-indigo-800">
                                        SN: {{ $serial }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <div class="text-right">
                            <span class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100">
                                {{ number_format($availQty, $availQty == (int)$availQty ? 0 : 2, ',', '.') }}
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $item->unit ?? 'pcs' }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button wire:click="openRecordUsageModal({{ $custody->id }})" type="button"
                                    class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-bold shadow-xs transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">build</span>
                                <span>Pakai</span>
                            </button>

                            <button wire:click="openReturnModal({{ $custody->id }}, 'retur')" type="button" title="Kembalikan ke Gudang atau Lapor Rusak"
                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-[10px] font-bold transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">keyboard_return</span>
                                <span>Retur</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 px-6 text-center bg-white dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700 space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl">inventory_2</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 dark:text-slate-200">Bagasi Teknisi Anda Masih Kosong</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                            Belum ada material atau perangkat aktif yang di-dispatch ke penguasaan Anda. Ajukan permintaan barang ke pengurus gudang untuk memulai tugas hari ini.
                        </p>
                    </div>
                    <div class="pt-2 flex justify-center gap-2">
                        <button wire:click="openRequestModal()" type="button"
                                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md transition-colors">
                            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                            <span>Minta Barang</span>
                        </button>
                        <button wire:click="switchTab('gudang')" type="button"
                                class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-colors">
                            Stok Gudang
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    {{-- TAB 2: GUDANG KANTOR (REAL MASTER INVENTORY & WAREHOUSE STOCKS) --}}
    @if($activeTab === 'gudang')
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Ketersediaan fisik barang di <strong>{{ $activeWarehouse?->name ?? 'Gudang Pusat' }}</strong>.
                </p>
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $warehouseMaterials->count() }} Jenis Barang Terdaftar
                </span>
            </div>

            @forelse($warehouseMaterials as $wh)
                @php
                    $stock = (float) ($wh->stock ?? 0);
                    $catIcon = match(strtolower($wh->category ?? '')) {
                        'kabel' => 'cable',
                        'konektor' => 'settings_ethernet',
                        'perangkat aktif' => 'router',
                        default => 'category',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm flex items-center justify-between gap-3 hover:border-emerald-400 dark:hover:border-emerald-500 transition-colors">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl {{ $stock > 0 ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40' : 'bg-slate-100 text-slate-400 dark:bg-slate-700' }} flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:22px">{{ $catIcon }}</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug truncate">
                                {{ $wh->name }}
                            </h4>
                            <div class="flex items-center flex-wrap gap-2 mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] font-mono">{{ $wh->sku ?? 'MAT' }}</span>
                                <span>•</span>
                                <span>{{ $wh->category ?? 'Material' }}</span>
                                <span>•</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $wh->location }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <div class="text-right">
                            <span class="text-base sm:text-lg font-black {{ $stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }}">
                                {{ number_format($stock, $stock == (int)$stock ? 0 : 2, ',', '.') }}
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $wh->unit }}</span>
                        </div>

                        <button wire:click="openRequestModal({{ $wh->id }})" type="button"
                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold shadow-sm transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">add_shopping_cart</span>
                            <span>Minta</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">warehouse</span>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tidak ada material yang terdaftar di katalog gudang.</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- TAB 3: RIWAYAT PEMAKAIAN MATERIAL --}}
    @if($activeTab === 'pemakaian')
        <div class="space-y-3">
            @forelse($myUsages as $usage)
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined" style="font-size:20px">history_toggle_drop</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">
                                        {{ $usage->usage_number }}
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                        {{ $usage->work_order_type }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $usage->notes ?: 'Pemakaian operasional lapangan' }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-[10px] text-slate-400 block font-medium">
                                {{ \Carbon\Carbon::parse($usage->usage_date ?? $usage->created_at)->format('d M Y') }}
                            </span>
                            @if($usage->ticket_id)
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                    Tiket #{{ $usage->ticket_id }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Items yang dipakai --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 space-y-1">
                        @foreach($usage->items as $it)
                            <div class="flex items-center justify-between text-xs text-slate-700 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-xs text-slate-400">check_small</span>
                                    <span class="font-semibold">{{ $it->item?->name ?? 'Material #' . $it->item_id }}</span>
                                    @if($it->itemSerial)
                                        <span class="font-mono text-[10px] bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 px-1.5 py-0.2 rounded border border-indigo-200 dark:border-indigo-800">
                                            SN: {{ $it->itemSerial->serial_number }}
                                        </span>
                                    @endif
                                </div>
                                <span class="font-bold font-mono">
                                    {{ (float) $it->quantity }} {{ $it->item?->unit ?? 'unit' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">history</span>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum ada riwayat pemakaian material resmi.</p>
                </div>
            @endforelse

            <div class="pt-2">
                {{ $myUsages->links() }}
            </div>
        </div>
    @endif

    {{-- TAB 4: STATUS PERMINTAAN BARANG (REQUISITIONS) --}}
    @if($activeTab === 'permintaan')
        <div class="space-y-3">
            @forelse($myRequisitions as $req)
                @php
                    $statusBadge = match($req->status) {
                        'pending' => ['bg' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400', 'label' => 'Menunggu Otorisasi Gudang'],
                        'approved' => ['bg' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400', 'label' => 'Disetujui Gudang'],
                        'dispatched' => ['bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400', 'label' => 'Sudah Dikeluarkan / Selesai'],
                        'rejected' => ['bg' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400', 'label' => 'Ditolak'],
                        default => ['bg' => 'bg-slate-100 text-slate-700', 'label' => $req->status],
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined" style="font-size:20px">receipt_long</span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">
                                    {{ $req->requisition_number }}
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Ke: <strong>{{ $req->warehouse?->name ?? 'Gudang Pusat' }}</strong>
                                    @if($req->notes)
                                        • <span>{{ $req->notes }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $statusBadge['bg'] }} block">
                                {{ $statusBadge['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 mt-1 block">
                                {{ $req->created_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                    </div>

                    {{-- Items yang diminta --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 space-y-1">
                        @foreach($req->items as $ri)
                            <div class="flex items-center justify-between text-xs text-slate-700 dark:text-slate-300">
                                <span class="font-semibold">{{ $ri->item?->name ?? 'Material #' . $ri->item_id }}</span>
                                <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                    Diminta: {{ (float) $ri->quantity_requested }} {{ $ri->item?->unit ?? 'unit' }}
                                    @if((float)$ri->quantity_dispatched > 0)
                                        <span class="text-slate-400 font-normal">| Dikeluarkan: {{ (float)$ri->quantity_dispatched }}</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">receipt_long</span>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum ada dokumen permintaan barang yang diajukan.</p>
                </div>
            @endforelse

            <div class="pt-2">
                {{ $myRequisitions->links() }}
            </div>
        </div>
    @endif

    {{-- MODAL PERMINTAAN BARANG RESMI KE GUDANG (REQUISITION) --}}
    @if($showRequestModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 max-w-md w-full space-y-4 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Permintaan Barang ke Gudang</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Diajukan ke Admin Gudang untuk diverifikasi & diserahkan</p>
                        </div>
                    </div>
                    <button wire:click="$set('showRequestModal', false)" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-100 dark:border-emerald-900/40 text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-base shrink-0 mt-0.5">verified_user</span>
                    <p class="text-[11px] leading-relaxed">
                        Permintaan ini resmi tercatat di <strong>Requisitions</strong> gudang. Setelah Admin Gudang menyetujui & menyerahkan fisik barang (beserta SN perangkat), barang otomatis masuk ke <strong>Stok Bagasi</strong> Anda.
                    </p>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Barang dari Katalog Gudang *</label>
                        <select wire:model.live="reqItemId" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-bold focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Barang Katalog --</option>
                            @foreach($warehouseMaterials as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} (Stok Tersedia: {{ (float)$w->stock }} {{ $w->unit }})</option>
                            @endforeach
                        </select>
                        @error('reqItemId') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jumlah Diminta *</label>
                            <input type="number" wire:model="reqQuantity" min="1" required
                                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-center bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            @error('reqQuantity') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Keperluan / Kategori *</label>
                            <select wire:model="reqWorkOrderType" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 font-bold">
                                <option value="stock_topup">Stok Cadangan Bagasi</option>
                                <option value="psb">Pemasangan Baru (PSB)</option>
                                <option value="maintenance">Perbaikan / Gangguan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alasan / Catatan Permintaan</label>
                        <textarea wire:model="reqReason" rows="2" placeholder="Contoh: Stok fast connector habis di lapangan, butuh restock untuk agenda besok..."
                                  class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <div class="flex gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button wire:click="$set('showRequestModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button wire:click="submitMaterialRequest" type="button"
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">send</span>
                        <span>Kirim Permintaan</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CATAT PEMAKAIAN CEPAT (USAGE) --}}
    @if($showUsageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">build</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Catat Pemakaian Material</h3>
                    </div>
                    <button wire:click="$set('showUsageModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 text-[10px] uppercase font-bold">Material:</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold text-[10px]">Tersedia: {{ $usageMaxQuantity }} {{ $usageUnit }}</span>
                    </div>
                    <span class="font-bold text-slate-900 dark:text-slate-100 block">{{ $usageMaterialName }}</span>
                    @if($usageSerial)
                        <span class="font-mono text-[10px] text-indigo-700 dark:text-indigo-300 font-bold block">SN: {{ $usageSerial }}</span>
                    @endif
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah yang Digunakan ({{ $usageUnit }}) *</label>
                        <input type="number" step="any" wire:model="usageQuantity" min="0.01" max="{{ $usageMaxQuantity }}"
                               class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-center bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        @error('usageQuantity') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Kaitkan ke Tiket / Pelanggan (Opsional)</label>
                        <select wire:model="usageTicketId" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">-- Pemakaian Umum (Tanpa Tiket) --</option>
                            @foreach($activeTickets as $t)
                                <option value="{{ $t->id }}">#{{ $t->id }} - {{ $t->title }} ({{ $t->customer->name ?? 'Pelanggan' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Catatan Pemakaian</label>
                        <input type="text" wire:model="usageNotes" placeholder="Contoh: Digunakan untuk penarikan kabel pelanggan..."
                               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button wire:click="$set('showUsageModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button wire:click="saveUsage" type="button"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL RETUR & LAPOR RUSAK / HILANG (RETURN) --}}
    @if($showReturnModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">assignment_return</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Retur / Lapor Rusak</h3>
                    </div>
                    <button wire:click="$set('showReturnModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                {{-- Pilih Tipe Aksi --}}
                <div class="grid grid-cols-3 gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-[10px] font-bold text-center">
                    <button type="button" wire:click="$set('returnAction', 'retur')"
                            class="py-1.5 rounded-lg transition-colors {{ $returnAction === 'retur' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500' }}">
                        Retur Gudang
                    </button>
                    <button type="button" wire:click="$set('returnAction', 'rusak')"
                            class="py-1.5 rounded-lg transition-colors {{ $returnAction === 'rusak' ? 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-slate-500' }}">
                        Barang Rusak
                    </button>
                    <button type="button" wire:click="$set('returnAction', 'hilang')"
                            class="py-1.5 rounded-lg transition-colors {{ $returnAction === 'hilang' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
                        Hilang
                    </button>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 text-[10px] uppercase font-bold">Barang:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-bold text-[10px]">Maks: {{ $returnMaxQuantity }} {{ $returnUnit }}</span>
                    </div>
                    <span class="font-bold text-slate-900 dark:text-slate-100 block">{{ $returnMaterialName }}</span>
                    @if($returnSerial)
                        <span class="font-mono text-[10px] text-amber-700 dark:text-amber-300 font-bold block">SN: {{ $returnSerial }}</span>
                    @endif
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah ({{ $returnUnit }}) *</label>
                        <input type="number" step="any" wire:model="returnQuantity" min="0.01" max="{{ $returnMaxQuantity }}"
                               class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-center bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        @error('returnQuantity') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan / Alasan</label>
                        <textarea wire:model="returnReason" rows="2" placeholder="Contoh: Sisa pekerjaan dikembalikan ke gudang..."
                                  class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button wire:click="$set('showReturnModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button wire:click="submitReturn" type="button"
                            class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL KAMERA SCANNER BARCODE PADA STOK SAYA --}}
    <div x-show="scannerOpen"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fadeIn">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Scan Barcode / SN Material</h3>
                </div>
                <button type="button" @click="closeScanner()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            <div class="rounded-2xl overflow-hidden border-2 border-indigo-500 relative bg-black aspect-video flex items-center justify-center">
                <div id="mat-camera-reader" class="w-full"></div>
            </div>

            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center">
                Arahkan kamera ke barcode material atau Serial Number modem.
            </p>

            <button type="button" @click="closeScanner()"
                    class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                Tutup Kamera
            </button>
        </div>
    </div>

    {{-- Script HTML5 QR Code Scanner --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function materialManager() {
            return {
                scannerOpen: false,
                html5Scanner: null,

                openScanner() {
                    this.scannerOpen = true;
                    this.$nextTick(() => {
                        if (!this.html5Scanner) {
                            this.html5Scanner = new Html5Qrcode("mat-camera-reader");
                        }
                        this.html5Scanner.start(
                            { facingMode: "environment" },
                            { fps: 10, qrbox: { width: 250, height: 120 } },
                            (decodedText) => {
                                this.closeScanner();
                                @this.set('search', decodedText.trim());
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
                }
            };
        }
    </script>
</div>
