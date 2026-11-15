<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-3xl">local_shipping</span>
                <span>Pengeluaran Barang (Dispatches)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catat barang keluar dari gudang fisik ke tangan Reseller (Metode A) atau langsung ke Teknisi (Metode B).
            </p>
        </div>

        <button wire:click="openCreateModal" type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Keluarkan Barang Baru</span>
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

    {{-- Filter Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-4 shadow-xs">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative flex-1 min-w-[280px]">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari no dispatch, nama penerima, catatan..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[200px] flex-1 sm:flex-initial">
                    <select wire:model.live="warehouseFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Gudang Asal</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[130px] flex-1 sm:flex-initial">
                    <select wire:model.live="perPage"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 cursor-pointer font-medium">
                        <option value="20">20 / Hal</option>
                        <option value="50">50 / Hal</option>
                        <option value="100">100 / Hal</option>
                        <option value="all">All (Semua)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Dispatches Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">No. Pengeluaran</th>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Gudang Asal</th>
                        <th class="py-3 px-4">Barang Dikeluarkan</th>
                        <th class="py-3 px-4">Penerima Fisik (Custody)</th>
                        <th class="py-3 px-4">Metode Distribusi</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                    @forelse($dispatches as $dsp)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800 text-xs">
                                    {{ $dsp->dispatch_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $dsp->dispatched_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                {{ $dsp->warehouse?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @forelse($dsp->items->take(2) as $di)
                                        <div class="text-xs text-slate-800 dark:text-slate-200">
                                            <div class="flex items-center gap-1.5 font-semibold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
                                                <span class="font-bold truncate max-w-[200px]">{{ $di->item?->name ?? 'Item #' . $di->item_id }}</span>
                                                <span class="font-mono text-[11px] text-blue-700 dark:text-blue-400 font-bold shrink-0">
                                                    {{ (float)$di->quantity }} {{ $di->item?->unit ?? 'unit' }}
                                                </span>
                                            </div>
                                            @if(!empty($di->serials))
                                                <div class="flex items-center gap-1 flex-wrap mt-0.5 ml-3">
                                                    @foreach(array_slice($di->serials, 0, 2) as $sn)
                                                        <span class="px-1.5 py-0.2 rounded font-mono text-[10px] bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">SN: {{ $sn }}</span>
                                                    @endforeach
                                                    @if(count($di->serials) > 2)
                                                        <span class="text-[10px] text-slate-400">+{{ count($di->serials) - 2 }} SN</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Tidak ada item</span>
                                    @endforelse
                                    @if($dsp->items->count() > 2)
                                        <span class="inline-block text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">
                                            +{{ $dsp->items->count() - 2 }} barang lainnya
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $dsp->recipient?->name ?? '-' }}</div>
                                <span class="text-[10px] uppercase font-bold text-slate-500">
                                    {{ $dsp->recipient_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($dsp->distribution_method === 'pickup_reseller')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800">
                                        <span class="material-symbols-outlined text-xs">store</span>
                                        Metode A
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800">
                                        <span class="material-symbols-outlined text-xs">local_shipping</span>
                                        Metode B
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs">
                                    {{ $dsp->customer?->name ?? 'Stok Operasional' }}
                                </div>
                                <div class="text-[10px] text-slate-400">Petugas: {{ $dsp->dispatcher?->name ?? 'System' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button wire:click="showDetail({{ $dsp->id }})"
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
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">outbox</span>
                                    <p class="text-sm">Belum ada data pengeluaran barang.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $dispatches->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $dispatches->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $dispatches->total() }}</span> dokumen pengeluaran
            </div>
            <div>
                {{ $dispatches->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL PENGELUARAN BARANG --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl max-w-2xl w-full p-4 sm:p-5 space-y-4 my-8">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <span class="material-symbols-outlined">outbox</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">Keluarkan Barang dari Gudang (Dispatch)</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Barang tetap menjadi aset perusahaan dan dicatat sebagai titipan (Custody).</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- In-Modal Alert Banner --}}
                @if(session('error'))
                    <div class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded-2xl text-xs sm:text-sm font-semibold text-rose-800 dark:text-rose-200 flex items-start gap-2.5 animate-fadeIn">
                        <span class="material-symbols-outlined text-rose-600 dark:text-rose-400 shrink-0 text-xl">error</span>
                        <div class="flex-1">
                            <span class="font-bold block">Gagal Memproses Pengeluaran Barang:</span>
                            <span class="text-xs font-normal leading-relaxed text-rose-700 dark:text-rose-300">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <form wire:submit="saveDispatch" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Gudang Asal *</label>
                            <select wire:model.live="warehouse_id" required
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200">
                                <option value="">-- Pilih Gudang --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Metode Distribusi *</label>
                            <select wire:model.live="distribution_method" required
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 font-semibold">
                                <option value="pickup_reseller">Metode A: Reseller Mengambil Barang</option>
                                <option value="direct_technician">Metode B: Gudang ke Teknisi Langsung</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Penerima Fisik Barang *</label>
                            <select wire:model="recipient_id" required
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 font-bold">
                                <option value="">-- Pilih Penerima --</option>
                                @foreach($recipients as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roles->pluck('name')->first() ?? $u->role }})</option>
                                @endforeach
                            </select>
                            @error('recipient_id') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Pelanggan Tujuan</label>
                            <select wire:model="customer_id"
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200">
                                <option value="">-- Pilih Pelanggan (Opsional) --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Catatan Pengeluaran</label>
                            <input wire:model="notes" type="text" placeholder="Contoh: Dispatch untuk PSB Budi..."
                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    {{-- Items Input Section --}}
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">inventory</span>
                                <span>Barang yang Dikeluarkan</span>
                            </h4>
                            <button wire:click="addItemRow" type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 rounded-xl border border-blue-200 dark:border-blue-800 transition-colors">
                                <span class="material-symbols-outlined text-sm">add</span>
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        <div class="space-y-3">
                            @foreach($dispatchItems as $idx => $row)
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs space-y-3">
                                    {{-- Header Row: Badge & Delete Button --}}
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700/60">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-xs font-black">
                                                <span class="material-symbols-outlined text-xs notranslate" translate="no">local_shipping</span>
                                                <span>Barang #{{ $idx + 1 }}</span>
                                            </span>
                                            @if(!empty($row['unit']))
                                                <span class="text-[11px] text-slate-400 font-medium">Satuan: <strong>{{ $row['unit'] }}</strong></span>
                                            @endif
                                        </div>
                                        @if(count($dispatchItems) > 1)
                                            <button wire:click="removeItemRow({{ $idx }})" type="button"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors"
                                                    title="Hapus baris ini">
                                                <span class="material-symbols-outlined text-sm notranslate" translate="no">delete</span>
                                                <span>Hapus</span>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Inputs Grid --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                                        <div class="sm:col-span-6">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Pilih Barang Katalog *</label>
                                            <x-inventory.item-searchable-select
                                                :availableItems="$availableItems"
                                                :selectedId="$row['item_id'] ?? null"
                                                wireModel="dispatchItems.{{ $idx }}.item_id"
                                                placeholder="-- Cari / Pilih Barang dari Katalog --"
                                            />
                                            @error("dispatchItems.{$idx}.item_id") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">Jumlah Keluar ({{ $row['unit'] ?? 'unit' }}) *</label>
                                                @if(!empty($row['item_id']) && $warehouse_id)
                                                    @php $currentStock = $warehouseStocks[$row['item_id']] ?? 0; @endphp
                                                    <span class="text-[10px] font-bold {{ $currentStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                                        Stok: {{ $currentStock }} {{ $row['unit'] ?? 'unit' }}
                                                    </span>
                                                @endif
                                            </div>
                                            <input wire:model="dispatchItems.{{ $idx }}.quantity" type="number" step="any" min="0.01" required placeholder="1"
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 font-bold">
                                            @error("dispatchItems.{$idx}.quantity") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan (Opsional)</label>
                                            <input wire:model="dispatchItems.{{ $idx }}.notes" type="text" placeholder="opsional"
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200">
                                        </div>
                                    </div>

                                    {{-- Serial Number Selection & Search for Serialized items --}}
                                    @if(!empty($row['is_serialized']) && !empty($row['item_id']))
                                        <div class="pt-2.5 border-t border-dashed border-slate-200 dark:border-slate-700 space-y-2.5"
                                             x-data="{ localSearch: '' }">
                                            
                                            {{-- Header Status & Counter --}}
                                            <div class="flex items-center justify-between flex-wrap gap-1">
                                                <label class="text-[11px] font-bold text-blue-700 dark:text-blue-400 flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-xs">qr_code</span>
                                                    <span>Pilih / Input Serial Number Unit (Wajib {{ (int)$row['quantity'] }} SN):</span>
                                                </label>
                                                @php
                                                    $availCount = count($availableSerials[$row['item_id']] ?? []);
                                                    $selectedArr = (array) ($row['selected_serials'] ?? []);
                                                    $rawArr = !empty($row['serials_raw']) ? array_filter(array_map('trim', preg_split('/[\r\n,]+/', $row['serials_raw']))) : [];
                                                    $allSelected = array_values(array_unique(array_merge($selectedArr, $rawArr)));
                                                    $selectedCount = count($allSelected);
                                                    $isFulfilled = $selectedCount === (int)$row['quantity'];
                                                    $isExceeded = $selectedCount > (int)$row['quantity'];
                                                @endphp
                                                <div class="flex items-center gap-2 text-[10px]">
                                                    <span class="font-bold px-2 py-0.5 rounded-md {{ $isFulfilled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : ($isExceeded ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400') }}">
                                                        Terpilih: {{ $selectedCount }} / {{ (int)$row['quantity'] }} SN
                                                    </span>
                                                    <span class="text-slate-400">•</span>
                                                    <span class="text-slate-500 dark:text-slate-400">Tersedia di gudang ini: {{ $availCount }} unit</span>
                                                </div>
                                            </div>

                                            {{-- Flash notifications specific to this row --}}
                                            @if(session()->has("serial_error_{$idx}"))
                                                <div class="p-2 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-[11px] text-rose-700 dark:text-rose-300 flex items-center justify-between">
                                                    <span class="flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-xs">error</span>
                                                        {{ session("serial_error_{$idx}") }}
                                                    </span>
                                                </div>
                                            @endif
                                            @if(session()->has("serial_warning_{$idx}"))
                                                <div class="p-2 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-[11px] text-amber-700 dark:text-amber-300 flex items-center justify-between">
                                                    <span class="flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-xs">warning</span>
                                                        {{ session("serial_warning_{$idx}") }}
                                                    </span>
                                                </div>
                                            @endif
                                            @if(session()->has("serial_success_{$idx}"))
                                                <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-[11px] text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                                                    <span class="flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-xs">check_circle</span>
                                                        {{ session("serial_success_{$idx}") }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- Bar Pencarian SN & Scan Barcode --}}
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <div class="relative flex-1">
                                                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                                        <input type="text"
                                                               x-model="localSearch"
                                                               @keydown.enter.prevent="if (localSearch.trim()) { $wire.addScannedSerial({{ $idx }}, localSearch); localSearch = ''; }"
                                                               placeholder="Cari SN di daftar gudang atau scan barcode lalu Enter..."
                                                               class="w-full pl-8 pr-8 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-sm">
                                                        <button type="button"
                                                                x-show="localSearch.length > 0"
                                                                @click="localSearch = ''"
                                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                            <span class="material-symbols-outlined text-sm">close</span>
                                                        </button>
                                                    </div>
                                                    <button type="button"
                                                            @click="if (localSearch.trim()) { $wire.addScannedSerial({{ $idx }}, localSearch); localSearch = ''; }"
                                                            title="Tambahkan atau pilih nomor SN yang diketik"
                                                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1 shadow-sm transition-colors whitespace-nowrap">
                                                        <span class="material-symbols-outlined text-xs">add_circle</span>
                                                        <span>Pilih / Tambah</span>
                                                    </button>
                                                    <button type="button"
                                                            @click="if (localSearch.trim()) { $wire.lookupSerial(localSearch); }"
                                                            title="Lacak status atau riwayat SN ini di sistem"
                                                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-medium flex items-center gap-1 border border-slate-200 dark:border-slate-700 transition-colors whitespace-nowrap">
                                                        <span class="material-symbols-outlined text-xs">manage_search</span>
                                                        <span>Cek SN</span>
                                                    </button>
                                                </div>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                                    Ketik SN untuk menyaring daftar centang, atau scan barcode dan tekan <strong>Enter</strong> untuk langsung memilih.
                                                </p>
                                            </div>

                                            {{-- SN Lookup Result Box (Jika user mengecek SN) --}}
                                            @if(!empty($snLookupResult))
                                                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/80 border border-blue-200 dark:border-blue-800/60 rounded-xl text-xs space-y-1 relative">
                                                    <button type="button" wire:click="clearLookupResult" class="absolute top-2 right-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                        <span class="material-symbols-outlined text-sm">close</span>
                                                    </button>
                                                    <div class="font-bold flex items-center gap-1 text-blue-700 dark:text-blue-400">
                                                        <span class="material-symbols-outlined text-sm">info</span>
                                                        <span>Hasil Pengecekan SN: <span class="font-mono text-slate-900 dark:text-white">{{ $snLookupResult['serial_number'] }}</span></span>
                                                    </div>
                                                    @if($snLookupResult['found'])
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px] pt-1">
                                                            <div><span class="text-slate-500">Barang:</span> <span class="font-semibold">{{ $snLookupResult['item_name'] }}</span></div>
                                                            <div><span class="text-slate-500">Status Saat Ini:</span> <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $snLookupResult['status_label'] }}</span></div>
                                                            @if($snLookupResult['warehouse'] !== '-')
                                                                <div><span class="text-slate-500">Lokasi Gudang:</span> <span>{{ $snLookupResult['warehouse'] }}</span></div>
                                                            @endif
                                                            @if($snLookupResult['custodian'] !== '-')
                                                                <div><span class="text-slate-500">Pemegang Fisik:</span> <span>{{ $snLookupResult['custodian'] }}</span></div>
                                                            @endif
                                                            @if($snLookupResult['customer'] !== '-')
                                                                <div><span class="text-slate-500">Pelanggan:</span> <span>{{ $snLookupResult['customer'] }}</span></div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p class="text-[11px] text-amber-700 dark:text-amber-300">
                                                            {{ $snLookupResult['message'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Daftar SN yang Sedang Terpilih (Chips Tag) --}}
                                            @if(count($allSelected) > 0)
                                                <div class="p-2.5 bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/50 rounded-xl space-y-1.5">
                                                    <div class="text-[10px] font-bold text-blue-800 dark:text-blue-300 flex items-center justify-between">
                                                        <span>SN Terpilih ({{ count($allSelected) }} dari {{ (int)$row['quantity'] }} unit):</span>
                                                        <span class="text-[9px] text-slate-500">Klik tanda (×) untuk membatalkan SN</span>
                                                    </div>
                                                    <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                                                        @foreach($allSelected as $selectedSn)
                                                            @php
                                                                $isFromWarehouse = in_array($selectedSn, array_column(($availableSerials[$row['item_id']] ?? collect())->toArray(), 'serial_number'));
                                                            @endphp
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-mono font-medium {{ $isFromWarehouse ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-300 dark:border-blue-700' }}">
                                                                <span>{{ $selectedSn }}</span>
                                                                <span class="text-[9px] font-normal opacity-75">({{ $isFromWarehouse ? 'Gudang' : 'Scan' }})</span>
                                                                <button type="button" wire:click="removeSelectedSerial({{ $idx }}, '{{ $selectedSn }}')" title="Hapus SN ini"
                                                                        class="hover:text-rose-600 focus:outline-none ml-0.5 text-slate-400 hover:text-rose-600">
                                                                    <span class="material-symbols-outlined text-[13px] leading-none">close</span>
                                                                </button>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Checklist SN dari Gudang (jika ada unit di gudang) --}}
                                            @if($availCount > 0)
                                                <div class="space-y-1.5">
                                                    <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                                                        <span>Daftar SN di Gudang (Centang atau cari di atas):</span>
                                                        <span x-show="localSearch" class="text-blue-600 dark:text-blue-400 font-medium" x-text="'Menyaring: ' + localSearch"></span>
                                                    </div>
                                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 max-h-36 overflow-y-auto p-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700">
                                                        @foreach($availableSerials[$row['item_id']] as $sn)
                                                            <label x-show="!localSearch || '{{ strtolower($sn->serial_number) }}'.includes(localSearch.toLowerCase())"
                                                                   class="inline-flex items-center gap-1.5 text-xs font-mono cursor-pointer p-1 rounded hover:bg-white dark:hover:bg-slate-800 transition-colors">
                                                                <input type="checkbox" wire:model.live="dispatchItems.{{ $idx }}.selected_serials" value="{{ $sn->serial_number }}"
                                                                       class="rounded text-blue-600 focus:ring-blue-500">
                                                                <span class="truncate">{{ $sn->serial_number }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-[11px] text-amber-800 dark:text-amber-300 space-y-1">
                                                    <div class="font-bold flex items-center gap-1.5">
                                                        <span class="material-symbols-outlined text-sm">info</span>
                                                        <span>Stok nomor seri belum tersimpan di gudang ini (0 unit SN)</span>
                                                    </div>
                                                    <p class="text-[11px] leading-relaxed text-amber-700 dark:text-amber-400">
                                                        Gunakan kolom pencarian di atas untuk <strong>Scan Barcode atau ketik nomor SN unit fisik</strong> lalu tekan Enter. Sistem otomatis akan meregistrasikannya saat barang dikeluarkan.
                                                    </p>
                                                </div>
                                            @endif

                                            {{-- Mode Input Cepat / Paste Banyak SN Sekaligus (Collapsible) --}}
                                            <details class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 group">
                                                <summary class="cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 select-none flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-xs">edit_note</span>
                                                    <span>Mode Input Cepat / Paste Banyak SN Sekaligus</span>
                                                </summary>
                                                <div class="mt-1.5 space-y-1">
                                                    <textarea wire:model.live="dispatchItems.{{ $idx }}.serials_raw" rows="2" placeholder="Paste daftar nomor SN di sini (pisahkan baris baru atau koma)..."
                                                              class="w-full font-mono text-xs py-1.5 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500"></textarea>
                                                    <div class="text-[9px] text-slate-400">Cocok jika Anda meng-copy puluhan SN sekaligus dari file Excel atau rekapan nota.</div>
                                                </div>
                                            </details>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showCreateModal', false)" type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">local_shipping</span>
                            <span>Keluarkan & Serahkan Barang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL DISPATCH --}}
    @if($showDetailModal && $selectedDispatch)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-2xl w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <span class="material-symbols-outlined">receipt_long</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">{{ $selectedDispatch->dispatch_number }}</h3>
                            <p class="text-xs text-slate-500">Detail Pengeluaran Barang</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Gudang Asal</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedDispatch->warehouse?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Penerima Fisik</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedDispatch->recipient?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Metode Distribusi</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $selectedDispatch->distribution_method }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Status</span>
                        <span class="font-bold uppercase text-slate-800 dark:text-slate-200">{{ $selectedDispatch->status }}</span>
                    </div>
                </div>

                {{-- Detail Items --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Barang yang Dikeluarkan</h4>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                        @foreach($selectedDispatch->items as $it)
                            <div class="p-3 bg-white dark:bg-slate-800 space-y-1">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $it->item?->name }}</span>
                                    <span class="font-bold text-blue-600">{{ number_format($it->quantity, 0) }} {{ $it->item?->unit }}</span>
                                </div>
                                @if(!empty($it->serials) && is_array($it->serials))
                                    <div class="flex flex-wrap gap-1 pt-1">
                                        @foreach($it->serials as $sn)
                                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                {{ $sn }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button wire:click="closeDetailModal" type="button"
                            class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
