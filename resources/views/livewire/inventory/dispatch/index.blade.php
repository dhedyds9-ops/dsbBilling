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

            <div class="min-w-[230px]">
                <select wire:model.live="warehouseFilter"
                        class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                    <option value="">Semua Gudang Asal</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
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
                        <th class="py-3 px-4">Metode Distribusi</th>
                        <th class="py-3 px-4">Penerima Fisik (Custody)</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Petugas Gudang</th>
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
                                @if($dsp->distribution_method === 'pickup_reseller')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800">
                                        <span class="material-symbols-outlined text-xs">store</span>
                                        Metode A (Reseller)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800">
                                        <span class="material-symbols-outlined text-xs">local_shipping</span>
                                        Metode B (Teknisi)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $dsp->recipient?->name ?? '-' }}</div>
                                <span class="text-[10px] uppercase font-bold text-slate-500">
                                    {{ $dsp->recipient_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                {{ $dsp->customer?->name ?? 'Stok Operasional' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $dsp->dispatcher?->name ?? 'System' }}
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

        @if($dispatches->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80">
                {{ $dispatches->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL PENGELUARAN BARANG --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-4xl w-full p-6 space-y-6">
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
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Penerima Fisik Barang (Pemegang Sementara) *</label>
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
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                        <div class="md:col-span-6">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Pilih Barang Katalog *</label>
                                            <select wire:model.live="dispatchItems.{{ $idx }}.item_id" required
                                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200">
                                                <option value="">-- Pilih Barang --</option>
                                                @foreach($availableItems as $item)
                                                    <option value="{{ $item->id }}">[{{ $item->sku }}] {{ $item->name }} ({{ $item->category }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah Keluar ({{ $row['unit'] ?? 'unit' }}) *</label>
                                            <input wire:model="dispatchItems.{{ $idx }}.quantity" type="number" step="any" min="0.01" required placeholder="1"
                                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 font-bold">
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan (Opsional)</label>
                                            <input wire:model="dispatchItems.{{ $idx }}.notes" type="text" placeholder="opsional"
                                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200">
                                        </div>
                                    </div>

                                    {{-- Serial Number Selection for Serialized items --}}
                                    @if(!empty($row['is_serialized']) && !empty($row['item_id']))
                                        <div class="pt-2 border-t border-dashed border-slate-200 dark:border-slate-700 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <label class="text-[11px] font-bold text-blue-700 dark:text-blue-400 flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-xs">qr_code</span>
                                                    <span>Pilih Serial Number Unit (Wajib {{ (int)$row['quantity'] }} SN):</span>
                                                </label>
                                                <span class="text-[10px] text-slate-400">Tersedia di gudang ini: {{ count($availableSerials[$row['item_id']] ?? []) }} unit</span>
                                            </div>

                                            @if(!empty($availableSerials[$row['item_id']]) && count($availableSerials[$row['item_id']]) > 0)
                                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 max-h-32 overflow-y-auto p-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                                                    @foreach($availableSerials[$row['item_id']] as $sn)
                                                        <label class="inline-flex items-center gap-1.5 text-xs font-mono cursor-pointer p-1 rounded hover:bg-slate-50 dark:hover:bg-slate-700">
                                                            <input type="checkbox" wire:model="dispatchItems.{{ $idx }}.selected_serials" value="{{ $sn->serial_number }}"
                                                                   class="rounded text-blue-600 focus:ring-blue-500">
                                                            <span class="truncate">{{ $sn->serial_number }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="p-2 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-[11px] text-amber-700 dark:text-amber-300">
                                                    Peringatan: Tidak ada Serial Number berstatus 'in_warehouse' di gudang ini. Anda dapat memasukkan SN manual/scan di bawah:
                                                </div>
                                                <textarea wire:model="dispatchItems.{{ $idx }}.serials_raw" rows="2" placeholder="Masukkan 1 SN per baris..."
                                                          class="w-full font-mono text-xs py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200"></textarea>
                                            @endif
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
