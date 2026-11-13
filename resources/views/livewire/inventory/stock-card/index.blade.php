<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-3xl">menu_book</span>
                <span>Kartu Stok Material (Stock Ledger)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Buku besar audit trail mutasi fisik barang, saldo awal, pergerakan keluar-masuk, dan saldo akhir.
            </p>
        </div>

        @if($selectedItem && $selectedWarehouse)
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500">Kondisi Stok:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $currentPhysicalStock <= ($selectedItem->min_stock ?? 5) ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                    <span class="w-2 h-2 rounded-full {{ $currentPhysicalStock <= ($selectedItem->min_stock ?? 5) ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                    {{ $currentPhysicalStock }} {{ $selectedItem->unit }}
                </span>
            </div>
        @endif
    </div>

    {{-- Filter & Selection Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-xs space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Gudang Penyimpanan *</label>
                <select wire:model.live="warehouseId"
                        class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 font-semibold cursor-pointer">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name ?? 'Pusat' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Pilih Barang / Material *</label>
                <select wire:model.live="itemId"
                        class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 font-semibold cursor-pointer">
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">[{{ $item->sku }}] {{ $item->name }} ({{ $item->category }})</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Periode Tanggal Mutasi</label>
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 sm:gap-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5">
                    <span class="material-symbols-outlined text-slate-400 text-base shrink-0">calendar_month</span>
                    <input wire:model.live="dateFrom" type="date"
                           class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 min-w-0 flex-1"
                           title="Dari Tanggal">
                    <span class="text-xs text-slate-400 font-medium shrink-0">s/d</span>
                    <input wire:model.live="dateTo" type="date"
                           class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 min-w-0 flex-1"
                           title="Sampai Tanggal">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Per Halaman</label>
                <select wire:model.live="perPage"
                        class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-slate-100 cursor-pointer font-medium">
                    <option value="20">20 / Hal</option>
                    <option value="50">50 / Hal</option>
                    <option value="100">100 / Hal</option>
                    <option value="all">All (Semua)</option>
                </select>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 overflow-x-auto text-xs">
            <span class="text-slate-400 font-semibold shrink-0">Filter Mutasi:</span>
            <button wire:click="$set('typeFilter', '')" type="button"
                    class="px-2.5 py-1 rounded-lg font-bold transition-colors {{ empty($typeFilter) ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                Semua Tipe
            </button>
            <button wire:click="$set('typeFilter', 'inbound')" type="button"
                    class="px-2.5 py-1 rounded-lg font-bold transition-colors {{ $typeFilter === 'inbound' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                Masuk (Inbound)
            </button>
            <button wire:click="$set('typeFilter', 'outbound_dispatch')" type="button"
                    class="px-2.5 py-1 rounded-lg font-bold transition-colors {{ $typeFilter === 'outbound_dispatch' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                Pengeluaran / Dispatch
            </button>
            <button wire:click="$set('typeFilter', 'usage')" type="button"
                    class="px-2.5 py-1 rounded-lg font-bold transition-colors {{ $typeFilter === 'usage' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                Pemakaian Aktual
            </button>
            <button wire:click="$set('typeFilter', 'return_good')" type="button"
                    class="px-2.5 py-1 rounded-lg font-bold transition-colors {{ $typeFilter === 'return_good' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                Retur Barang Baik
            </button>
            <button wire:click="$set('typeFilter', 'adjustment_in')" type="button"
                    class="px-2.5 py-1 rounded-lg font-bold transition-colors {{ $typeFilter === 'adjustment_in' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                Penyesuaian Opname
            </button>
        </div>
    </div>

    {{-- Metric Cards (Saldo Awal, Masuk, Keluar, Saldo Akhir) --}}
    @if($selectedItem && $selectedWarehouse)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Saldo Awal Periode</span>
                <div class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100">
                    {{ number_format($openingBalance, 0, ',', '.') }}
                    <span class="text-xs font-semibold text-slate-400 font-normal">{{ $selectedItem->unit }}</span>
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Sebelum {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</span>
            </div>

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">arrow_downward</span> Total Masuk
                </span>
                <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400">
                    +{{ number_format($totalIn, 0, ',', '.') }}
                    <span class="text-xs font-semibold text-emerald-500 font-normal">{{ $selectedItem->unit }}</span>
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Debet dalam periode</span>
            </div>

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block mb-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">arrow_upward</span> Total Keluar
                </span>
                <div class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400">
                    -{{ number_format($totalOut, 0, ',', '.') }}
                    <span class="text-xs font-semibold text-rose-500 font-normal">{{ $selectedItem->unit }}</span>
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Kredit dalam periode</span>
            </div>

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 shadow-xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">Saldo Fisik Gudang</span>
                <div class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400">
                    {{ number_format($currentPhysicalStock, 0, ',', '.') }}
                    <span class="text-xs font-semibold text-blue-500 font-normal">{{ $selectedItem->unit }}</span>
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Stok aktual saat ini</span>
            </div>
        </div>
    @endif

    {{-- Mutations Ledger Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Waktu & No. Mutasi</th>
                        <th class="py-3 px-4">Jenis Transaksi</th>
                        <th class="py-3 px-4">No. Dokumen / Ref</th>
                        <th class="py-3 px-4">Serial Number</th>
                        <th class="py-3 px-4 text-right">Masuk (+)</th>
                        <th class="py-3 px-4 text-right">Keluar (-)</th>
                        <th class="py-3 px-4 text-right">Saldo Sesudah</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                    @forelse($mutations as $mut)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-slate-800 dark:text-slate-200 text-xs">
                                    {{ $mut->mutation_number }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $mut->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $badgeClasses = match($mut->type) {
                                        'inbound' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                        'outbound_dispatch' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                        'usage' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                                        'return_good' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                                        'return_damaged' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                        default => 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700'
                                    };
                                    $label = match($mut->type) {
                                        'inbound' => 'Barang Masuk',
                                        'outbound_dispatch' => 'Pengeluaran/Dispatch',
                                        'usage' => 'Pemakaian Terpasang',
                                        'return_good' => 'Retur Sisa (Baik)',
                                        'return_damaged' => 'Retur Rusak',
                                        'adjustment_in' => 'Penyesuaian (+)',
                                        'adjustment_out' => 'Penyesuaian (-)',
                                        default => $mut->type
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-md border text-[11px] {{ $badgeClasses }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400 text-xs">
                                {{ $mut->reference_type ? $mut->reference_type . ' #' . $mut->reference_id : '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400 text-xs">
                                {{ $mut->itemSerial?->serial_number ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $mut->qty_in > 0 ? '+' . number_format($mut->qty_in, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-rose-600 dark:text-rose-400">
                                {{ $mut->qty_out > 0 ? '-' . number_format($mut->qty_out, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-800 dark:text-slate-100">
                                {{ number_format($mut->balance_after, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $mut->actor?->name ?? 'System' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 text-xs max-w-xs truncate" title="{{ $mut->notes }}">
                                {{ $mut->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">receipt_long</span>
                                    <p class="text-sm">Tidak ada catatan mutasi untuk kriteria barang dan periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($mutations, 'total'))
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $mutations->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $mutations->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $mutations->total() }}</span> catatan mutasi
                </div>
                <div>
                    {{ $mutations->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
