<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">summarize</span>
                Laporan & Rekonsiliasi Inventaris
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan komprehensif posisi persediaan, arus mutasi logistik, dan custody material ISP.
            </p>
        </div>
        <div>
            <button onclick="window.print()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition">
                <span class="material-symbols-outlined text-sm">print</span>
                Cetak / Ekspor Laporan
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 dark:border-slate-700 space-x-4">
        <button wire:click="setTab('stock_position')" class="pb-3 text-sm font-medium flex items-center gap-2 border-b-2 transition {{ $activeTab === 'stock_position' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-base">inventory_2</span>
            Posisi Stok Persediaan
        </button>
        <button wire:click="setTab('mutations')" class="pb-3 text-sm font-medium flex items-center gap-2 border-b-2 transition {{ $activeTab === 'mutations' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-base">swap_horiz</span>
            Mutasi Barang Masuk & Keluar
        </button>
        <button wire:click="setTab('custody')" class="pb-3 text-sm font-medium flex items-center gap-2 border-b-2 transition {{ $activeTab === 'custody' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-base">badge</span>
            Barang di Tangan Tim (Custody)
        </button>
    </div>

    <!-- Tab 1: Posisi Stok Persediaan -->
    @if($activeTab === 'stock_position')
    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[220px]">
                    <select wire:model.live="warehouseId" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[220px]">
                    <select wire:model.live="categoryFilter" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="text-xs text-slate-500">
                Menampilkan posisi fisik stok per gudang.
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-5 py-3.5">Gudang</th>
                            <th class="px-5 py-3.5">Kode SKU</th>
                            <th class="px-5 py-3.5">Nama Barang</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5 text-right">Stok Fisik</th>
                            <th class="px-5 py-3.5 text-right">HPP Satuan</th>
                            <th class="px-5 py-3.5 text-right">Nilai Total Aset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($stockData as $row)
                        @php
                            $cost = (float) ($row->item?->cost_price ?? 0);
                            $totalVal = (float) $row->quantity * $cost;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-750">
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $row->warehouse?->name }}</td>
                            <td class="px-5 py-3 font-mono text-xs font-bold text-slate-700 dark:text-slate-200">{{ $row->item?->sku }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">{{ $row->item?->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $row->item?->category ?? '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                {{ (float) $row->quantity }} {{ $row->item?->unit }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-slate-600 dark:text-slate-400">
                                Rp {{ number_format($cost, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-slate-800 dark:text-white">
                                Rp {{ number_format($totalVal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada data posisi stok.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $stockData->links() }}
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 2: Mutasi Masuk & Keluar -->
    @if($activeTab === 'mutations')
    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5">
                    <span class="material-symbols-outlined text-slate-400 text-sm">calendar_month</span>
                    <input type="date" wire:model.live="startDate" class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 w-32" title="Dari Tanggal">
                    <span class="text-xs text-slate-400 font-medium">s/d</span>
                    <input type="date" wire:model.live="endDate" class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 w-32" title="Sampai Tanggal">
                </div>

                <div class="min-w-[190px]">
                    <select wire:model.live="mutationWarehouseId" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[190px]">
                    <select wire:model.live="mutationType" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Tipe Mutasi</option>
                        <option value="inbound">Barang Masuk (Inbound)</option>
                        <option value="outbound_dispatch">Pengeluaran (Dispatch)</option>
                        <option value="return_good">Retur Baik (Restock)</option>
                        <option value="return_damaged">Retur Rusak (QC)</option>
                        <option value="adjustment_in">Adjustment Masuk</option>
                        <option value="adjustment_out">Adjustment Keluar</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal / Jam</th>
                            <th class="px-5 py-3.5">Gudang</th>
                            <th class="px-5 py-3.5">Barang</th>
                            <th class="px-5 py-3.5">Tipe Mutasi</th>
                            <th class="px-5 py-3.5 text-right">Masuk</th>
                            <th class="px-5 py-3.5 text-right">Keluar</th>
                            <th class="px-5 py-3.5 text-right">Saldo Akhir</th>
                            <th class="px-5 py-3.5">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($mutationData as $mut)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-750">
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ $mut->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">{{ $mut->warehouse?->name }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">{{ $mut->item?->name }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                    {{ in_array($mut->type, ['inbound', 'return_good', 'adjustment_in']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                                    {{ strtoupper(str_replace('_', ' ', $mut->type)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-emerald-600">
                                {{ $mut->qty_in > 0 ? (float)$mut->qty_in : '-' }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-rose-600">
                                {{ $mut->qty_out > 0 ? (float)$mut->qty_out : '-' }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-slate-800 dark:text-white">
                                {{ (float)$mut->balance_after }}
                            </td>
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ $mut->actor?->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-slate-400">Tidak ada data mutasi pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $mutationData->links() }}
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 3: Custody Material Lapangan -->
    @if($activeTab === 'custody')
    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[190px]">
                    <select wire:model.live="custodyHolderType" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Pemegang</option>
                        <option value="technician">Teknisi</option>
                        <option value="reseller">Reseller</option>
                    </select>
                </div>
                <div class="min-w-[190px]">
                    <select wire:model.live="custodyStatus" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="in_custody">Di Tangan (Aktif)</option>
                        <option value="used">Habis Terpakai</option>
                        <option value="returned">Sudah Dikembalikan</option>
                    </select>
                </div>
            </div>
            <div class="text-xs text-slate-500">
                Laporan audit tracking kepemilikan aset perusahaan di lapangan.
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-5 py-3.5">Pemegang (Custodian)</th>
                            <th class="px-5 py-3.5">Tipe</th>
                            <th class="px-5 py-3.5">Barang</th>
                            <th class="px-5 py-3.5 text-right">Diterima</th>
                            <th class="px-5 py-3.5 text-right">Terpasang (Used)</th>
                            <th class="px-5 py-3.5 text-right">Diretur</th>
                            <th class="px-5 py-3.5 text-right">Sisa di Tangan</th>
                            <th class="px-5 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($custodyData as $cust)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-750">
                            <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">{{ $cust->holder?->name }}</td>
                            <td class="px-5 py-3 text-slate-500 capitalize">{{ $cust->holder_type }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">{{ $cust->item?->name }}</td>
                            <td class="px-5 py-3 text-right font-mono">{{ (float)$cust->quantity }}</td>
                            <td class="px-5 py-3 text-right font-mono text-blue-600 font-bold">{{ (float)$cust->used_quantity }}</td>
                            <td class="px-5 py-3 text-right font-mono text-purple-600 font-bold">{{ (float)$cust->returned_quantity }}</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400">
                                {{ (float)$cust->available_quantity }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                    {{ $cust->status === 'in_custody' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : ($cust->status === 'used' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ strtoupper(str_replace('_', ' ', $cust->status)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-slate-400">Tidak ada catatan custody lapangan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $custodyData->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
