<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-3xl">move_to_inbox</span>
                <span>Penerimaan Barang Masuk (Inbound)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catat penerimaan barang baru dari supplier, pengadaan pusat, atau saldo awal fisik gudang.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Catat Barang Masuk</span>
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
            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[280px]">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari no inbound, supplier, no faktur/PO..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Dropdown Gudang --}}
                <div class="min-w-[230px] flex-1 sm:flex-initial">
                    <select wire:model.live="warehouseFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name ?? 'Pusat' }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range Pill --}}
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 flex-wrap sm:flex-nowrap">
                    <span class="material-symbols-outlined text-slate-400 text-base">date_range</span>
                    <input wire:model.live="dateFrom" type="date"
                           class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 w-36"
                           title="Dari Tanggal">
                    <span class="text-xs text-slate-400 font-medium">s/d</span>
                    <input wire:model.live="dateTo" type="date"
                           class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 w-36"
                           title="Sampai Tanggal">
                </div>
            </div>
        </div>
    </div>

    {{-- Inbounds Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">No. Dokumen</th>
                        <th class="py-3 px-4">Tanggal Masuk</th>
                        <th class="py-3 px-4">Gudang Penerima</th>
                        <th class="py-3 px-4">Sumber / Supplier</th>
                        <th class="py-3 px-4">Referensi Faktur/PO</th>
                        <th class="py-3 px-4 text-center">Jml Macam Barang</th>
                        <th class="py-3 px-4">Penerima</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                    @forelse($inbounds as $inbound)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800 text-xs">
                                    {{ $inbound->inbound_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ \Carbon\Carbon::parse($inbound->received_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-semibold">{{ $inbound->warehouse?->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $inbound->warehouse?->branch?->name ?? 'Kantor Pusat' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-medium">{{ $inbound->supplier_name ?: 'Pengadaan Internal' }}</div>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    {{ $inbound->source_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400">
                                {{ $inbound->reference_doc ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs">
                                    <span class="material-symbols-outlined text-[13px]">inventory_2</span>
                                    {{ $inbound->items->count() }} macam
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $inbound->receiver?->name ?? 'System' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button wire:click="showDetail({{ $inbound->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                    <span>Detail</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">inbox</span>
                                    <p class="text-sm">Belum ada data barang masuk.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inbounds->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80">
                {{ $inbounds->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL FORM BARANG MASUK --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-3xl w-full p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined notranslate text-xl" translate="no">add_box</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Catat Penerimaan Barang Masuk</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Stok gudang akan bertambah dan otomatis dicatat di Kartu Stok.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        <span class="material-symbols-outlined notranslate text-lg" translate="no">close</span>
                    </button>
                </div>

                <form wire:submit="saveInbound" class="space-y-4">
                    {{-- Header Fields (Sleek & Compact) --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                            <div class="sm:col-span-5">
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Gudang Penerima *</label>
                                <select wire:model="warehouse_id" required
                                        class="w-full py-1.5 px-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                    <option value="">-- Pilih Gudang Penerima --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name ?? 'Pusat' }})</option>
                                    @endforeach
                                </select>
                                @error('warehouse_id') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Terima *</label>
                                <input wire:model="received_date" type="date" required
                                       class="w-full py-1.5 px-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                @error('received_date') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-4">
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Supplier / Vendor</label>
                                <input wire:model="supplier_name" type="text" placeholder="Contoh: Shopee, PT..."
                                       class="w-full py-1.5 px-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                            </div>
                        </div>

                        {{-- Detail Tambahan (Collapsible / Compact) --}}
                        <div x-data="{ showExtra: false }">
                            <button type="button" @click="showExtra = !showExtra"
                                    class="text-[11px] font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-xs notranslate" :class="showExtra ? 'rotate-180' : ''" translate="no">expand_more</span>
                                <span x-text="showExtra ? 'Sembunyikan No. Surat Jalan & Catatan' : '+ No. Faktur / Surat Jalan & Catatan Tambahan'"></span>
                            </button>

                            <div x-show="showExtra" x-cloak class="grid grid-cols-1 sm:grid-cols-12 gap-2 mt-2 pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Sumber Pengadaan</label>
                                    <select wire:model="source_type"
                                            class="w-full py-1.5 px-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                        <option value="supplier">Supplier / Vendor</option>
                                        <option value="purchase">Pembelian Langsung</option>
                                        <option value="initial_stock">Saldo Awal Fisik</option>
                                        <option value="other">Lain-lain</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">No. Faktur / Surat Jalan</label>
                                    <input wire:model="reference_doc" type="text" placeholder="Contoh: INV-2026/05/998"
                                           class="w-full py-1.5 px-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Catatan Tambahan</label>
                                    <input wire:model="notes" type="text" placeholder="Keterangan..."
                                           class="w-full py-1.5 px-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items Input Section --}}
                    <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-emerald-600 text-base notranslate" translate="no">inventory_2</span>
                                <span>Daftar Barang yang Diterima</span>
                            </h4>
                            <button wire:click="addItemRow" type="button"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 rounded-xl border border-emerald-200 dark:border-emerald-800 transition-colors">
                                <span class="material-symbols-outlined text-sm notranslate" translate="no">add</span>
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        @error('inboundItems')
                            <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                        @enderror

                        <div class="space-y-3">
                            @foreach($inboundItems as $idx => $row)
                                <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 space-y-3">
                                    {{-- Header Row: Badge & Delete Button --}}
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-bold">
                                                <span>Item #{{ $idx + 1 }}</span>
                                            </span>
                                            @if(!empty($row['unit']))
                                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Satuan: <strong>{{ $row['unit'] }}</strong></span>
                                            @endif
                                            @if(!empty($row['is_serialized']))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold border border-indigo-200 dark:border-indigo-800">
                                                    <span class="material-symbols-outlined text-[12px] notranslate" translate="no">qr_code</span>
                                                    <span>Wajib Lacak SN</span>
                                                </span>
                                            @endif
                                        </div>
                                        @if(count($inboundItems) > 1)
                                            <button wire:click="removeItemRow({{ $idx }})" type="button"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors"
                                                    title="Hapus baris ini">
                                                <span class="material-symbols-outlined text-sm notranslate" translate="no">delete</span>
                                                <span>Hapus</span>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Inputs Grid --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-start">
                                        <div class="sm:col-span-6">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Pilih Barang Katalog *</label>
                                            <select wire:model.live="inboundItems.{{ $idx }}.item_id" required
                                                    class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 shadow-xs">
                                                <option value="">-- Pilih Barang dari Katalog --</option>
                                                @foreach($availableItems as $it)
                                                    <option value="{{ $it->id }}">[{{ $it->sku }}] {{ $it->name }} ({{ $it->category }} - {{ $it->unit }})</option>
                                                @endforeach
                                            </select>
                                            @error("inboundItems.{$idx}.item_id") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah ({{ $row['unit'] ?? 'unit' }}) *</label>
                                            <input wire:model.live.debounce.300ms="inboundItems.{{ $idx }}.quantity" type="number" step="any" min="0.01" required placeholder="1"
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 font-bold">
                                            @error("inboundItems.{$idx}.quantity") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Harga Beli Satuan (Rp)</label>
                                            <input wire:model="inboundItems.{{ $idx }}.unit_cost" type="number" step="any" placeholder="0"
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                        </div>
                                    </div>

                                    {{-- Serial Number Input (HERO SECTION KHUSUS BARANG BERSERIAL) --}}
                                    @if(!empty($row['is_serialized']))
                                        <div x-data="{
                                            mode: 'scan',
                                            scanInput: '',
                                            serials: [],
                                            get count() { return this.serials.length; },
                                            targetQty: {{ (int)($row['quantity'] ?? 0) }},
                                            init() {
                                                this.parse();
                                                this.$watch('$wire.inboundItems.{{ $idx }}.serials_raw', () => this.parse());
                                                this.$watch('$wire.inboundItems.{{ $idx }}.quantity', (val) => { this.targetQty = parseInt(val) || 0; });
                                            },
                                            parse() {
                                                const raw = $wire.get('inboundItems.{{ $idx }}.serials_raw') || '';
                                                this.serials = raw.split(/[\r\n,]+/).map(s => s.trim().toUpperCase()).filter(s => s.length > 0);
                                            },
                                            updateWire() {
                                                $wire.set('inboundItems.{{ $idx }}.serials_raw', this.serials.join('\n'));
                                            },
                                            addScan() {
                                                const sn = this.scanInput.trim().toUpperCase();
                                                if (!sn) return;
                                                if (this.serials.includes(sn)) {
                                                    alert('Serial Number ' + sn + ' sudah terdaftar!');
                                                } else {
                                                    this.serials.push(sn);
                                                    this.updateWire();
                                                }
                                                this.scanInput = '';
                                                this.$nextTick(() => { this.$refs.scannerInput?.focus(); });
                                            },
                                            removeSn(index) {
                                                this.serials.splice(index, 1);
                                                this.updateWire();
                                            },
                                            clearAll() {
                                                if (confirm('Hapus semua Serial Number yang sudah diinput?')) {
                                                    this.serials = [];
                                                    this.updateWire();
                                                }
                                            },
                                            syncQty() {
                                                if (this.serials.length > 0) {
                                                    $wire.set('inboundItems.{{ $idx }}.quantity', this.serials.length);
                                                    this.targetQty = this.serials.length;
                                                }
                                            }
                                        }" class="mt-3 p-3.5 sm:p-4 rounded-2xl bg-slate-900 dark:bg-slate-950 text-white border-2 border-emerald-500/40 shadow-lg space-y-3">
                                            
                                            {{-- Header Bar SN: Title & Live Status Counter --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-800">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                                        <span class="material-symbols-outlined text-lg notranslate" translate="no">qr_code_scanner</span>
                                                    </div>
                                                    <div>
                                                        <h5 class="text-xs sm:text-sm font-bold text-white flex items-center gap-1.5">
                                                            <span>Input / Scan Serial Number (SN)</span>
                                                        </h5>
                                                        <p class="text-[10px] text-slate-400">Scan barcode kardus/perangkat atau paste langsung dari Excel</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    {{-- Live Count vs Target Pill --}}
                                                    <template x-if="count === 0">
                                                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-400 text-xs font-bold border border-slate-700">
                                                            0 / <span x-text="targetQty"></span> SN
                                                        </span>
                                                    </template>
                                                    <template x-if="count > 0 && count < targetQty">
                                                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 text-xs font-bold border border-amber-500/40 flex items-center gap-1 animate-pulse">
                                                            <span class="material-symbols-outlined text-xs notranslate" translate="no">pending</span>
                                                            <span>Kurang <strong x-text="targetQty - count"></strong> SN (<span x-text="count"></span>/<span x-text="targetQty"></span>)</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="count > 0 && count === targetQty">
                                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/40 flex items-center gap-1">
                                                            <span class="material-symbols-outlined text-xs notranslate" translate="no">check_circle</span>
                                                            <span>Lengkap (<span x-text="count"></span>/<span x-text="targetQty"></span> SN)</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="count > targetQty">
                                                        <span class="px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-300 text-xs font-bold border border-rose-500/40 flex items-center gap-1">
                                                            <span class="material-symbols-outlined text-xs notranslate" translate="no">warning</span>
                                                            <span>Kelebihan <strong x-text="count - targetQty"></strong> SN (<span x-text="count"></span>/<span x-text="targetQty"></span>)</span>
                                                        </span>
                                                    </template>

                                                    {{-- Tombol Samakan Qty --}}
                                                    <template x-if="count > 0 && count !== targetQty">
                                                        <button type="button" @click="syncQty"
                                                                class="px-2 py-1 rounded-lg bg-indigo-500/30 hover:bg-indigo-500/50 text-indigo-300 border border-indigo-500/40 text-[11px] font-bold transition flex items-center gap-1 shadow-xs"
                                                                title="Samakan jumlah target barang dengan total SN yang terinput">
                                                            <span class="material-symbols-outlined text-xs notranslate" translate="no">sync_alt</span>
                                                            <span>Set Qty = <span x-text="count"></span></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Mode Switcher Tabs --}}
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="mode = 'scan'; $nextTick(() => $refs.scannerInput?.focus());"
                                                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                                                        :class="mode === 'scan' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white'">
                                                    <span class="material-symbols-outlined text-sm notranslate" translate="no">barcode_scanner</span>
                                                    <span>Scan Barcode Cepat</span>
                                                </button>
                                                <button type="button" @click="mode = 'bulk'"
                                                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                                                        :class="mode === 'bulk' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white'">
                                                    <span class="material-symbols-outlined text-sm notranslate" translate="no">content_paste</span>
                                                    <span>Paste Daftar SN</span>
                                                </button>
                                                <template x-if="count > 0">
                                                    <button type="button" @click="clearAll"
                                                            class="ml-auto text-[11px] text-rose-400 hover:text-rose-300 font-semibold px-2 py-1 rounded hover:bg-rose-950/30 transition">
                                                        Bersihkan Semua
                                                    </button>
                                                </template>
                                            </div>

                                            {{-- MODE 1: SCAN BARCODE CEPAT DENGAN SCANNER GUN / ALAT PINDAI KASIR --}}
                                            <div x-show="mode === 'scan'" class="space-y-2.5">
                                                <div class="relative">
                                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-emerald-400 text-lg notranslate" translate="no">barcode_reader</span>
                                                    <input x-ref="scannerInput"
                                                           x-model="scanInput"
                                                           @keydown.enter.prevent="addScan"
                                                           type="text"
                                                           placeholder="Arahkan barcode scanner ke SN perangkat atau ketik lalu tekan Enter..."
                                                           class="w-full pl-10 pr-24 py-2.5 bg-slate-950 border-2 border-emerald-500/60 rounded-xl font-mono text-sm text-emerald-300 placeholder:text-slate-500 focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30 shadow-inner">
                                                    <button type="button" @click="addScan"
                                                            class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-xs notranslate" translate="no">add</span>
                                                        <span>Tambah</span>
                                                    </button>
                                                </div>

                                                {{-- Chip Tags Daftar SN Terinput --}}
                                                <div class="min-h-[90px] max-h-[180px] overflow-y-auto p-2.5 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                                                    <template x-if="serials.length === 0">
                                                        <div class="text-center py-6 text-xs text-slate-500 flex flex-col items-center justify-center gap-1">
                                                            <span class="material-symbols-outlined text-2xl text-slate-600 notranslate" translate="no">qr_code_scanner</span>
                                                            <span>Belum ada Serial Number yang discan. Tembakkan scanner ke barcode kardus.</span>
                                                        </div>
                                                    </template>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <template x-for="(sn, sIdx) in serials" :key="sIdx">
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-white font-mono text-xs shadow-xs hover:border-slate-600">
                                                                <span class="text-[10px] text-slate-400 font-sans" x-text="'#' + (sIdx + 1)"></span>
                                                                <span class="font-bold text-emerald-300" x-text="sn"></span>
                                                                <button type="button" @click="removeSn(sIdx)"
                                                                        class="ml-1 text-slate-400 hover:text-rose-400 transition" title="Hapus SN ini">
                                                                    <span class="material-symbols-outlined text-xs notranslate" translate="no">close</span>
                                                                </button>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- MODE 2: PASTE MULTILINE DARI EXCEL / DOKUMEN --}}
                                            <div x-show="mode === 'bulk'" class="space-y-1.5">
                                                <textarea wire:model.live.debounce.300ms="inboundItems.{{ $idx }}.serials_raw"
                                                          rows="5"
                                                          placeholder="Paste daftar Serial Number di sini (1 SN per baris atau pisahkan dengan koma):&#10;ZTEGC1234567&#10;ZTEGC1234568&#10;ZTEGC1234569"
                                                          class="w-full font-mono text-xs sm:text-sm py-2.5 px-3 bg-slate-950 border border-slate-700 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 text-emerald-300 placeholder:text-slate-500 leading-relaxed"></textarea>
                                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                                    <span>Dapat dicopy-paste langsung dari kolom Excel atau invoice supplier.</span>
                                                    <span>Terdeteksi: <strong class="text-emerald-400" x-text="count"></strong> SN</span>
                                                </div>
                                            </div>

                                            @error("inboundItems.{$idx}.serials_raw")
                                                <span class="text-xs text-rose-400 font-semibold block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3.5 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showCreateModal', false)" type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan & Bukukan ke Stok</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL INBOUND --}}
    @if($showDetailModal && $selectedInbound)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-3xl w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined">receipt_long</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">{{ $selectedInbound->inbound_number }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Detail Penerimaan Barang Masuk</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Summary Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tanggal Masuk</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($selectedInbound->received_date)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Gudang Penerima</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedInbound->warehouse?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Supplier / Vendor</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedInbound->supplier_name ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">No. Dokumen Ref</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $selectedInbound->reference_doc ?: '-' }}</span>
                    </div>
                </div>

                {{-- Detail Items List --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Item yang Diterima</h4>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                        @foreach($selectedInbound->items as $row)
                            <div class="p-3.5 bg-white dark:bg-slate-800 space-y-2">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <div>
                                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $row->item?->name ?? 'Item #' . $row->item_id }}</span>
                                        <span class="text-xs font-mono text-slate-400 ml-1">[{{ $row->item?->sku }}]</span>
                                    </div>
                                    <div class="font-bold text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($row->quantity, 0, ',', '.') }} {{ $row->item?->unit ?? 'unit' }}
                                    </div>
                                </div>

                                @if(!empty($row->serials) && is_array($row->serials))
                                    <div class="pt-2">
                                        <span class="text-[11px] font-semibold text-slate-500 block mb-1">Serial Numbers Terdaftar:</span>
                                        <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto">
                                            @foreach($row->serials as $sn)
                                                <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    {{ $sn }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button wire:click="closeDetailModal" type="button"
                            class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
