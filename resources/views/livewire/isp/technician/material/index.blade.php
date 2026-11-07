@section('header_title', 'Stok Saya (Inventory)')

<div class="space-y-4 p-4 pb-24" x-data="materialManager()">
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

    {{-- Tabs Navigasi: Stok Saya, Gudang Kantor, Riwayat Pemakaian --}}
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-2xl flex items-center shadow-inner gap-1">
        <button wire:click="switchTab('stok')" type="button"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'stok' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">work</span>
            <span>Stok Saya</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $activeTab === 'stok' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300' : 'bg-slate-300 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $materials->count() }}
            </span>
        </button>
        <button wire:click="switchTab('gudang')" type="button"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'gudang' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">warehouse</span>
            <span>Gudang Kantor</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ $activeTab === 'gudang' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300' : 'bg-slate-300 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $warehouseMaterials->count() }}
            </span>
        </button>
        <button wire:click="switchTab('pemakaian')" type="button"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'pemakaian' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Riwayat</span>
        </button>
    </div>

    {{-- Action Bar & Search --}}
    <div class="flex items-center gap-2">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kabel, ONT, konektor..."
                   class="w-full pl-10 pr-9 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm">
            <button type="button" @click="openScanner()" title="Scan Barcode / SN Perangkat"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 p-1">
                <span class="material-symbols-outlined" style="font-size:18px">photo_camera</span>
            </button>
        </div>

        <button wire:click="openTakeModal()" type="button"
                class="px-3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors shrink-0">
            <span class="material-symbols-outlined text-sm">move_to_inbox</span>
            <span>Ambil Barang</span>
        </button>

        <button wire:click="openRequestModal" type="button" title="Minta pengadaan barang tambahan ke kantor"
                class="px-2.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold flex items-center gap-1 transition-colors shrink-0">
            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
            <span class="hidden sm:inline">Minta Tambahan</span>
        </button>
    </div>

    {{-- TAB 1: STOK SAYA (BAGASI TEKNISI) --}}
    @if($activeTab === 'stok')
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Barang fisik yang saat ini dibawa di bagasi/motor Anda.
                </p>
                <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
                    Total: {{ $materials->count() }} item
                </span>
            </div>

            @forelse($materials as $item)
                @php
                    $stock = (int) ($item->stock ?? 0);
                    $min = (int) ($item->min_stock ?? 5);
                    $isLow = $stock > 0 && $stock <= $min;
                    $isOut = $stock <= 0;

                    $catIcon = match(strtolower($item->category ?? '')) {
                        'kabel' => 'cable',
                        'konektor' => 'settings_ethernet',
                        'perangkat aktif' => 'router',
                        default => 'category',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm flex items-center justify-between gap-3 hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl {{ $isOut ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/40' : ($isLow ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/40' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40') }} flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:22px">{{ $catIcon }}</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug truncate">
                                {{ $item->name }}
                            </h4>
                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] font-mono">{{ $item->sku ?? 'MAT' }}</span>
                                <span>•</span>
                                <span>{{ $item->category ?? 'Material' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <div class="text-right">
                            <span class="text-base sm:text-lg font-black {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900 dark:text-slate-100') }}">
                                {{ number_format($stock, 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $item->unit ?? 'pcs' }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($isOut)
                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">Habis</span>
                            @elseif($isLow)
                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">Menipis</span>
                            @else
                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">Tersedia</span>
                            @endif

                            <button wire:click="openRecordUsageModal({{ $item->id }})" type="button"
                                    class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg text-[10px] font-bold border border-indigo-200 dark:border-indigo-800 transition-colors">
                                Pakai
                            </button>

                            <button wire:click="openReturnModal({{ $item->id }}, 'retur')" type="button" title="Kembalikan ke Gudang atau Lapor Rusak"
                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-[10px] font-bold transition-colors">
                                Retur
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
                            Anda belum mengambil kabel, fast connector, atau ONT dari Gudang Kantor. Klik tombol di bawah untuk mengambil stok yang dibutuhkan hari ini.
                        </p>
                    </div>
                    <div class="pt-2 flex justify-center gap-2">
                        <button wire:click="openTakeModal()" type="button"
                                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md transition-colors">
                            <span class="material-symbols-outlined text-sm">move_to_inbox</span>
                            <span>Ambil Material dari Gudang</span>
                        </button>
                        <button wire:click="switchTab('gudang')" type="button"
                                class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-colors">
                            Lihat Gudang Kantor
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    {{-- TAB 2: GUDANG KANTOR (MASTER INVENTORY) --}}
    @if($activeTab === 'gudang')
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Ketersediaan stok material di Gudang Kantor pusat yang siap diambil.
                </p>
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $warehouseMaterials->count() }} Jenis Barang
                </span>
            </div>

            @forelse($warehouseMaterials as $wh)
                @php
                    $stock = (int) ($wh->stock ?? 0);
                    $catIcon = match(strtolower($wh->category ?? '')) {
                        'kabel' => 'cable',
                        'konektor' => 'settings_ethernet',
                        'perangkat aktif' => 'router',
                        default => 'category',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm flex items-center justify-between gap-3 hover:border-emerald-400 dark:hover:border-emerald-500 transition-colors">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:22px">{{ $catIcon }}</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug truncate">
                                {{ $wh->name }}
                            </h4>
                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] font-mono">{{ $wh->sku ?? 'MAT' }}</span>
                                <span>•</span>
                                <span>{{ $wh->category ?? 'Material' }}</span>
                                <span>•</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $wh->location ?? 'Gudang Utama' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <div class="text-right">
                            <span class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400">
                                {{ number_format($stock, 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $wh->unit ?? 'pcs' }}</span>
                        </div>

                        <button wire:click="openTakeModal({{ $wh->id }})" type="button"
                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold shadow-sm transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">add</span>
                            <span>Ambil</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">warehouse</span>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tidak ada material yang tersedia di gudang kantor.</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- TAB 3: RIWAYAT PEMAKAIAN & MUTASI --}}
    @if($activeTab === 'pemakaian')
        <div class="space-y-3">
            @forelse($myUsages as $usage)
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined" style="font-size:20px">history_toggle_drop</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug">
                                {{ $usage->material_name }}
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">
                                {{ $usage->notes ?: 'Pemakaian lapangan oleh teknisi' }}
                            </p>
                            <div class="flex items-center gap-2 mt-1.5 text-[10px] text-slate-400">
                                <span>{{ \Carbon\Carbon::parse($usage->created_at ?? $usage->used_date)->format('d M Y, H:i') }}</span>
                                @if(!empty($usage->ticket_id))
                                    <span>•</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">Tiket #{{ $usage->ticket_id }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-sm sm:text-base font-black text-slate-900 dark:text-slate-100">
                            {{ $usage->quantity }} {{ $usage->unit ?? 'pcs' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">history</span>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum ada riwayat mutasi atau pemakaian material.</p>
                </div>
            @endforelse

            <div class="pt-2">
                {{ $myUsages->links() }}
            </div>
        </div>
    @endif

    {{-- MODAL AMBIL BARANG DARI GUDANG KANTOR --}}
    @if($showTakeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn"
             x-data="{
                query: '',
                openDropdown: false,
                selectedId: @entangle('takeWarehouseMaterialId'),
                items: {{ json_encode($warehouseMaterials->map(fn($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                    'category' => $w->category ?? 'Material',
                    'stock' => (float)$w->stock,
                    'unit' => $w->unit ?? 'pcs',
                ])->values()) }},
                get selectedItem() {
                    return this.items.find(i => i.id == this.selectedId) || null;
                },
                get filteredItems() {
                    if (!this.query.trim()) return this.items;
                    const q = this.query.toLowerCase();
                    return this.items.filter(i => 
                        (i.name && i.name.toLowerCase().includes(q)) ||
                        (i.category && i.category.toLowerCase().includes(q))
                    );
                },
                selectItem(item) {
                    this.selectedId = item.id;
                    this.openDropdown = false;
                    this.query = '';
                    $wire.set('takeWarehouseMaterialId', item.id);
                }
             }">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 max-w-md w-full space-y-4 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-lg notranslate" translate="no">move_to_inbox</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Ambil Barang dari Gudang</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Pindahkan stok gudang kantor ke bagasi Anda</p>
                        </div>
                    </div>
                    <button wire:click="$set('showTakeModal', false)" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                        <span class="material-symbols-outlined text-sm notranslate" translate="no">close</span>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- 1. SEARCHABLE DROPDOWN --}}
                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                            <span>Pilih Material di Gudang *</span>
                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold" x-show="selectedItem" x-text="'Stok: ' + selectedItem?.stock + ' ' + selectedItem?.unit"></span>
                        </label>

                        {{-- Trigger Button --}}
                        <div @click="openDropdown = !openDropdown"
                             class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer flex items-center justify-between text-xs hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors shadow-xs">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <span class="material-symbols-outlined text-slate-400 text-base shrink-0 notranslate" translate="no">inventory_2</span>
                                <template x-if="selectedItem">
                                    <span class="font-bold text-slate-900 dark:text-slate-100 truncate" x-text="selectedItem.name"></span>
                                </template>
                                <template x-if="!selectedItem">
                                    <span class="text-slate-400 font-medium">-- Ketik atau Cari Nama Barang --</span>
                                </template>
                            </div>
                            <span class="material-symbols-outlined text-slate-400 text-sm transition-transform notranslate" translate="no" :class="{'rotate-180': openDropdown}">expand_more</span>
                        </div>

                        {{-- Dropdown Panel with Search Field --}}
                        <div x-show="openDropdown" @click.away="openDropdown = false" x-cloak
                             class="absolute left-0 right-0 mt-1 z-30 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl p-2.5 space-y-2 animate-fadeIn">
                            {{-- Live search input --}}
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm notranslate" translate="no">search</span>
                                <input type="text" x-model="query" placeholder="Ketik kata kunci (Dropcore, ONT, Fast...)"
                                       class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                            </div>

                            {{-- Filtered list --}}
                            <div class="max-h-48 overflow-y-auto space-y-1 pr-1">
                                <template x-for="item in filteredItems" :key="item.id">
                                    <button type="button" @click="selectItem(item)"
                                            class="w-full px-3 py-2 rounded-xl text-left flex items-center justify-between text-xs transition-colors hover:bg-indigo-50 dark:hover:bg-indigo-950/60"
                                            :class="{'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold': selectedId == item.id, 'text-slate-800 dark:text-slate-200': selectedId != item.id}">
                                        <div class="min-w-0 pr-2">
                                            <p class="font-bold truncate" x-text="item.name"></p>
                                            <p class="text-[10px] text-slate-400" x-text="item.category"></p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black shrink-0"
                                              :class="item.stock > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'"
                                              x-text="item.stock + ' ' + item.unit"></span>
                                    </button>
                                </template>
                                <template x-if="filteredItems.length === 0">
                                    <p class="text-center text-xs text-slate-400 py-3 font-medium">Barang tidak ditemukan dalam katalog gudang.</p>
                                </template>
                            </div>
                        </div>

                        {{-- Hidden Native Select for complete form compatibility --}}
                        <select wire:model.live="takeWarehouseMaterialId" class="hidden">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($warehouseMaterials as $wItem)
                                <option value="{{ $wItem->id }}">{{ $wItem->name }}</option>
                            @endforeach
                        </select>
                        @error('takeWarehouseMaterialId') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    @php
                        $selectedWh = $warehouseMaterials->firstWhere('id', $takeWarehouseMaterialId);
                    @endphp

                    @if($selectedWh)
                        <div class="p-3 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-2xl border border-indigo-100 dark:border-indigo-900/40 text-xs text-indigo-900 dark:text-indigo-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-base notranslate" translate="no">verified</span>
                                <div>
                                    <p class="font-bold text-[11px]">Stok Tersedia di Gudang Kantor:</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $selectedWh->category ?? 'Material' }}</p>
                                </div>
                            </div>
                            <span class="font-black text-sm text-indigo-700 dark:text-indigo-300 bg-white dark:bg-slate-800 px-2.5 py-1 rounded-xl shadow-xs border border-indigo-100 dark:border-indigo-900/60">
                                {{ $selectedWh->stock }} {{ $selectedWh->unit }}
                            </span>
                        </div>
                    @endif

                    {{-- 2. JUMLAH YANG DIAMBIL DENGAN STEPPER --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Jumlah yang Diambil ({{ $selectedWh->unit ?? 'unit' }}) *
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="$set('takeQuantity', Math.max(1, {{ (int)$takeQuantity }} - 1))"
                                    class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-lg flex items-center justify-center transition-colors">
                                -
                            </button>
                            <input type="number" wire:model="takeQuantity" min="1" max="{{ $selectedWh->stock ?? 9999 }}"
                                   class="flex-1 py-2 px-3 border border-slate-200 dark:border-slate-700 rounded-xl text-base font-black text-center bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                            <button type="button" wire:click="$set('takeQuantity', {{ (int)$takeQuantity }} + 1)"
                                    class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-lg flex items-center justify-center transition-colors">
                                +
                            </button>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="text-[10px] text-slate-400 font-semibold">Cepat:</span>
                            <button type="button" wire:click="$set('takeQuantity', 1)" class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] font-bold hover:bg-indigo-50 hover:text-indigo-600">1</button>
                            <button type="button" wire:click="$set('takeQuantity', 5)" class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] font-bold hover:bg-indigo-50 hover:text-indigo-600">5</button>
                            <button type="button" wire:click="$set('takeQuantity', 10)" class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] font-bold hover:bg-indigo-50 hover:text-indigo-600">10</button>
                            @if($selectedWh && (int)$selectedWh->stock > 0)
                                <button type="button" wire:click="$set('takeQuantity', {{ (int)$selectedWh->stock }})" class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold hover:bg-indigo-100">Semua ({{ (int)$selectedWh->stock }})</button>
                            @endif
                        </div>
                        @error('takeQuantity') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- 3. CATATAN PENGAMBILAN --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Pengambilan (Opsional)</label>
                        <input type="text" wire:model="takeNotes" placeholder="Contoh: Kebutuhan PSB 2 lokasi atau cadangan bagasi..."
                               class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button wire:click="$set('showTakeModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button wire:click="submitTake" type="button"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-sm notranslate" translate="no">check</span>
                        <span>Ambil ke Bagasi</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CATAT PEMAKAIAN CEPAT --}}
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

                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 block text-[10px] uppercase font-bold">Material:</span>
                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $usageMaterialName }}</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah yang Digunakan ({{ $usageUnit }})</label>
                        <input type="number" wire:model="usageQuantity" min="1"
                               class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-center bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Kaitkan ke Tiket / Pekerjaan</label>
                        <select wire:model="usageTicketId" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">-- Pemakaian Umum (Tanpa Tiket) --</option>
                            @foreach($activeTickets as $t)
                                <option value="{{ $t->id }}">#{{ $t->id }} - {{ $t->title }} ({{ $t->customer->name ?? 'Pelanggan' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Catatan Pemakaian</label>
                        <input type="text" wire:model="usageNotes" placeholder="Contoh: Digunakan untuk penarikan kabel pelanggan Bpk Joko..."
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
                        Simpan & Potong Stok
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL PERMINTAAN BARANG TAMBAHAN (REQUISITION) --}}
    @if($showRequestModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 max-w-md w-full space-y-4 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-lg notranslate" translate="no">add_shopping_cart</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Permintaan Barang ke Gudang</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Diajukan ke Pengurus Gudang Pusat untuk persetujuan</p>
                        </div>
                    </div>
                    <button wire:click="$set('showRequestModal', false)" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                        <span class="material-symbols-outlined text-sm notranslate" translate="no">close</span>
                    </button>
                </div>

                {{-- Penjelasan Alur Permintaan --}}
                <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-100 dark:border-emerald-900/40 text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-base shrink-0 mt-0.5 notranslate" translate="no">info</span>
                    <p class="text-[11px] leading-relaxed">
                        Permintaan ini akan masuk ke daftar <strong>Requisition</strong> pengurus gudang. Setelah disetujui & diserahkan fisik, barang akan otomatis masuk ke <strong>Stok Bagasi</strong> Anda.
                    </p>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Barang / Material yang Diminta *</label>
                        <input type="text" wire:model="reqMaterialName" list="warehouseCatalogList"
                               placeholder="Ketik atau pilih dari katalog gudang..."
                               class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        <datalist id="warehouseCatalogList">
                            @foreach($warehouseMaterials as $w)
                                <option value="{{ $w->name }}">{{ $w->category }} (Stok Gudang: {{ $w->stock }} {{ $w->unit }})</option>
                            @endforeach
                        </datalist>
                        @error('reqMaterialName') <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jumlah yang Dibutuhkan *</label>
                            <input type="number" wire:model="reqQuantity" min="1"
                                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-center bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan *</label>
                            <select wire:model="reqUnit" class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 font-bold">
                                <option value="pcs">pcs</option>
                                <option value="meter">meter</option>
                                <option value="unit">unit</option>
                                <option value="roll">roll</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alasan / Catatan Kebutuhan</label>
                        <textarea wire:model="reqReason" rows="2" placeholder="Contoh: Stok fast connector habis di lapangan, butuh restock untuk agenda instalasi besok..."
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
                        <span class="material-symbols-outlined text-sm notranslate" translate="no">send</span>
                        <span>Kirim Permintaan ke Gudang</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL RETUR & LAPOR RUSAK / HILANG --}}
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

                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 block text-[10px] uppercase font-bold">Barang:</span>
                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $returnMaterialName }}</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah ({{ $returnUnit }})</label>
                        <input type="number" wire:model="returnQuantity" min="1"
                               class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-center bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Serial Number (Opsional / Perangkat)</label>
                        <input type="text" wire:model="returnSerial" placeholder="Contoh: ZTEGC1234567..."
                               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs uppercase font-mono bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan / Alasan</label>
                        <textarea wire:model="returnReason" rows="2" placeholder="Contoh: Sisa PSB dikembalikan ke gudang / Port LAN mati terkena petir..."
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
                        Konfirmasi Simpan
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
