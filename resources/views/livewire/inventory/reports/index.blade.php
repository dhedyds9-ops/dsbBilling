<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="material-symbols-outlined text-blue-600 text-3xl">summarize</span>
                <span>Laporan & Rekonsiliasi Inventaris</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan komprehensif posisi persediaan, arus mutasi logistik, dan custody material ISP.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="exportExcel" type="button"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 shadow-sm transition cursor-pointer"
                    title="Download Laporan ke Format Excel (.xlsx)">
                <span wire:loading.remove wire:target="exportExcel" class="material-symbols-outlined text-sm">download</span>
                <span wire:loading wire:target="exportExcel" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span>Download Excel</span>
            </button>
            <button onclick="window.print()" class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 shadow-sm transition">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Print PDF</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 dark:border-slate-700 space-x-4 overflow-x-auto whitespace-nowrap">
        <button wire:click="setTab('stock_position')" class="pb-3 text-xs sm:text-sm font-bold flex items-center gap-2 border-b-2 transition shrink-0 {{ $activeTab === 'stock_position' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-base">inventory_2</span>
            <span>Posisi Stok Persediaan</span>
        </button>
        <button wire:click="setTab('mutations')" class="pb-3 text-xs sm:text-sm font-bold flex items-center gap-2 border-b-2 transition shrink-0 {{ $activeTab === 'mutations' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-base">swap_horiz</span>
            <span>Mutasi Barang Masuk & Keluar</span>
        </button>
        <button wire:click="setTab('custody')" class="pb-3 text-xs sm:text-sm font-bold flex items-center gap-2 border-b-2 transition shrink-0 {{ $activeTab === 'custody' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-base">badge</span>
            <span>Barang di Tangan Tim (Custody)</span>
        </button>
        <button wire:click="setTab('reseller_usage')" class="pb-3 text-xs sm:text-sm font-bold flex items-center gap-2 border-b-2 transition shrink-0 {{ $activeTab === 'reseller_usage' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-base">handshake</span>
            <span>Pemakaian & Finansial Reseller</span>
        </button>
    </div>

    <!-- Tab 1: Posisi Stok Persediaan -->
    @if($activeTab === 'stock_position')
    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-auto sm:min-w-[200px]">
                    <select wire:model.live="warehouseId" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto sm:min-w-[200px]">
                    <select wire:model.live="categoryFilter" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[130px] flex-1 sm:flex-initial">
                    <select wire:model.live="perPage"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100 cursor-pointer font-medium">
                        <option value="20">20 / Hal</option>
                        <option value="50">50 / Hal</option>
                        <option value="100">100 / Hal</option>
                        <option value="all">All (Semua)</option>
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
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $stockData->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $stockData->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $stockData->total() }}</span> data
                </div>
                <div>
                    {{ $stockData->links() }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 2: Mutasi Masuk & Keluar -->
    @if($activeTab === 'mutations')
    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 w-full">
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 sm:gap-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 w-full sm:w-auto">
                    <span class="material-symbols-outlined text-slate-400 text-sm shrink-0">calendar_month</span>
                    <input type="date" wire:model.live="startDate" class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 min-w-0 flex-1 sm:w-32" title="Dari Tanggal">
                    <span class="text-xs text-slate-400 font-medium shrink-0">s/d</span>
                    <input type="date" wire:model.live="endDate" class="bg-transparent border-0 text-xs sm:text-sm text-slate-700 dark:text-slate-200 focus:ring-0 p-1 min-w-0 flex-1 sm:w-32" title="Sampai Tanggal">
                </div>

                <div class="w-full sm:w-auto sm:min-w-[190px]">
                    <select wire:model.live="mutationWarehouseId" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-auto sm:min-w-[190px]">
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

                <div class="min-w-[130px] flex-1 sm:flex-initial">
                    <select wire:model.live="perPage"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100 cursor-pointer font-medium">
                        <option value="20">20 / Hal</option>
                        <option value="50">50 / Hal</option>
                        <option value="100">100 / Hal</option>
                        <option value="all">All (Semua)</option>
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
                                @php
                                    $mutTypeLabel = match($mut->type) {
                                        'inbound' => 'Barang Masuk',
                                        'outbound_dispatch' => 'Pengeluaran',
                                        'return_good' => 'Retur Baik',
                                        'return_damaged' => 'Retur Rusak',
                                        'adjustment_in' => 'Penyesuaian (+)',
                                        'adjustment_out' => 'Penyesuaian (-)',
                                        'usage' => 'Terpakai',
                                        default => str_replace('_', ' ', ucfirst($mut->type)),
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                    {{ in_array($mut->type, ['inbound', 'return_good', 'adjustment_in']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                                    {{ $mutTypeLabel }}
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
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $mutationData->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $mutationData->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $mutationData->total() }}</span> data
                </div>
                <div>
                    {{ $mutationData->links() }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 3: Custody Material Lapangan -->
    @if($activeTab === 'custody')
    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-auto sm:min-w-[190px]">
                    <select wire:model.live="custodyHolderType" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Pemegang</option>
                        <option value="technician">Teknisi</option>
                        <option value="reseller">Reseller</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto sm:min-w-[190px]">
                    <select wire:model.live="custodyStatus" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="in_custody">Di Tangan (Aktif)</option>
                        <option value="used">Habis Terpakai</option>
                        <option value="returned">Sudah Dikembalikan</option>
                    </select>
                </div>
                <div class="min-w-[130px] flex-1 sm:flex-initial">
                    <select wire:model.live="perPage"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100 cursor-pointer font-medium">
                        <option value="20">20 / Hal</option>
                        <option value="50">50 / Hal</option>
                        <option value="100">100 / Hal</option>
                        <option value="all">All (Semua)</option>
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
                            <td class="px-5 py-3 text-slate-500">{{ $cust->holder_type === 'reseller' ? 'Reseller' : 'Teknisi' }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800 dark:text-white">{{ $cust->item?->name }}</td>
                            <td class="px-5 py-3 text-right font-mono">{{ (float)$cust->quantity }}</td>
                            <td class="px-5 py-3 text-right font-mono text-blue-600 font-bold">{{ (float)$cust->used_quantity }}</td>
                            <td class="px-5 py-3 text-right font-mono text-purple-600 font-bold">{{ (float)$cust->returned_quantity }}</td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400">
                                {{ (float)$cust->available_quantity }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                    {{ $cust->status === 'in_custody' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : ($cust->status === 'used' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300') }}">
                                    {{ match($cust->status) {
                                        'in_custody' => 'Di Tangan',
                                        'used' => 'Terpasang',
                                        'returned' => 'Diretur',
                                        default => ucfirst($cust->status)
                                    } }}
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
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $custodyData->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $custodyData->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $custodyData->total() }}</span> data
                </div>
                <div>
                    {{ $custodyData->links() }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 4: Pemakaian Material Reseller & Finansial -->
    @if($activeTab === 'reseller_usage')
    <div class="space-y-5">
        <!-- KPI Cards Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">outbox</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block uppercase tracking-wider">Total Diambil</span>
                    <div class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white mt-0.5">
                        {{ number_format($resellerSummary['total_diambil'] ?? 0, 0, ',', '.') }}
                        <span class="text-xs font-medium text-slate-400 font-normal">item</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono mt-0.5 block">Rp {{ number_format($resellerSummary['nominal_total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-emerald-200 dark:border-emerald-800/60 bg-gradient-to-br from-white to-emerald-50/20 dark:from-slate-800 dark:to-emerald-950/20 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 block uppercase tracking-wider">Terpasang (Used)</span>
                    <div class="text-xl sm:text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-0.5">
                        {{ number_format($resellerSummary['total_terpakai'] ?? 0, 0, ',', '.') }}
                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 font-normal">item</span>
                    </div>
                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold font-mono mt-0.5 block">
                        Rp {{ number_format($resellerSummary['nominal_terpakai'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-amber-200 dark:border-amber-800/60 bg-gradient-to-br from-white to-amber-50/20 dark:from-slate-800 dark:to-amber-950/20 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">inventory</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-300 block uppercase tracking-wider">Sisa di Tangan</span>
                    <div class="text-xl sm:text-2xl font-black text-amber-700 dark:text-amber-300 mt-0.5">
                        {{ number_format($resellerSummary['total_sisa'] ?? 0, 0, ',', '.') }}
                        <span class="text-xs font-medium text-amber-600 dark:text-amber-400 font-normal">item</span>
                    </div>
                    <span class="text-[11px] text-amber-600 dark:text-amber-400 font-bold font-mono mt-0.5 block">
                        Rp {{ number_format($resellerSummary['nominal_sisa'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">replay</span>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block uppercase tracking-wider">Total Diretur</span>
                    <div class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white mt-0.5">
                        {{ number_format($resellerSummary['total_diretur'] ?? 0, 0, ',', '.') }}
                        <span class="text-xs font-medium text-slate-400 font-normal">item</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-normal mt-0.5 block">Kembali ke gudang</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-xs flex flex-wrap gap-3 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto flex-1">
                <!-- Dropdown Reseller -->
                <div class="w-full sm:w-56">
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1 block">Mitra / Reseller</label>
                    <select wire:model.live="resellerIdFilter"
                            class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 font-medium cursor-pointer">
                        <option value="">Semua Reseller</option>
                        @foreach($resellers as $reseller)
                            <option value="{{ $reseller->id }}">{{ $reseller->name }} (ID #{{ $reseller->id }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Periode Tanggal -->
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1 block">Dari Tgl</label>
                        <input type="date" wire:model.live="resellerStartDate"
                               class="py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 font-medium">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1 block">Sampai Tgl</label>
                        <input type="date" wire:model.live="resellerEndDate"
                               class="py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 font-medium">
                    </div>
                </div>

                <!-- Acuan Harga -->
                <div class="w-full sm:w-52">
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1 block">Acuan Harga Nominal</label>
                    <select wire:model.live="priceBasis"
                            class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 font-medium cursor-pointer">
                        <option value="cost_price">HPP / Harga Beli Modal</option>
                        <option value="selling_price">Harga Jual / Tagihan Mitra</option>
                    </select>
                </div>

                <!-- Kategori -->
                <div class="w-full sm:w-44">
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1 block">Kategori</label>
                    <select wire:model.live="resellerCategoryFilter"
                            class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 font-medium cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full lg:w-auto">
                <div class="relative flex-1 sm:w-56">
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1 block">Cari Barang / SN</label>
                    <input type="text" wire:model.live.debounce.300ms="resellerSearch"
                           placeholder="Cari barang, SN, reseller..."
                           class="w-full py-2 pl-8 pr-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400">
                    <span class="material-symbols-outlined text-slate-400 text-sm absolute left-2.5 bottom-2.5">search</span>
                </div>

                <div class="w-24">
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1 block">Tampil</label>
                    <select wire:model.live="perPage"
                            class="w-full py-2 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 cursor-pointer">
                        <option value="20">20 / Hal</option>
                        <option value="50">50 / Hal</option>
                        <option value="100">100 / Hal</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabel Data Pemakaian Reseller -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700 text-xs">
                        <tr>
                            <th class="px-5 py-3.5">Nama Reseller (Mitra)</th>
                            <th class="px-5 py-3.5">Nama Barang & Spek</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5 text-right">Diambil</th>
                            <th class="px-5 py-3.5 text-right font-bold text-emerald-600">Terpakai (Used)</th>
                            <th class="px-5 py-3.5 text-right text-purple-600">Retur</th>
                            <th class="px-5 py-3.5 text-right text-amber-600">Sisa di Tangan</th>
                            <th class="px-5 py-3.5 text-right">Harga Satuan ({{ $priceBasis === 'selling_price' ? 'Jual' : 'HPP' }})</th>
                            <th class="px-5 py-3.5 text-right font-bold text-emerald-600">Total Terpakai (Rp)</th>
                            <th class="px-5 py-3.5 text-right font-bold text-amber-600">Total Sisa (Rp)</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($resellerUsageData as $row)
                            @php
                                $price = (float) ($priceBasis === 'selling_price' ? ($row->item?->selling_price ?? 0) : ($row->item?->cost_price ?? 0));
                                $qty = (float) $row->quantity;
                                $used = (float) $row->used_quantity;
                                $ret = (float) $row->returned_quantity;
                                $avail = (float) $row->available_quantity;
                                $nominalUsed = $used * $price;
                                $nominalAvail = $avail * $price;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-750/50 transition">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-indigo-500">storefront</span>
                                        <span>{{ $row->holder?->name ?? 'Reseller #' . $row->holder_id }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block font-mono">Tgl: {{ $row->created_at ? $row->created_at->format('d/m/Y') : '-' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $row->item?->name ?? '-' }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                            {{ $row->item?->sku ?? 'NO-SKU' }}
                                        </span>
                                        @if($row->itemSerial)
                                            <span class="text-[10px] font-mono text-blue-600 dark:text-blue-400 font-bold">
                                                SN: {{ $row->itemSerial->serial_number }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 uppercase">
                                        {{ $row->item?->category ?? 'Lainnya' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-medium text-slate-700 dark:text-slate-300">
                                    {{ number_format($qty, 0, ',', '.') }} {{ $row->item?->unit ?? 'unit' }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($used, 0, ',', '.') }} {{ $row->item?->unit ?? 'unit' }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-purple-600 dark:text-purple-400">
                                    {{ number_format($ret, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400">
                                    {{ number_format($avail, 0, ',', '.') }} {{ $row->item?->unit ?? 'unit' }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-slate-600 dark:text-slate-300">
                                    Rp {{ number_format($price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/20">
                                    Rp {{ number_format($nominalUsed, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400 bg-amber-50/30 dark:bg-amber-950/20">
                                    Rp {{ number_format($nominalAvail, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($avail == 0 && $used > 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                            Habis Terpasang
                                        </span>
                                    @elseif($used > 0 && $avail > 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                            Sebagian Terpakai
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            Di Tangan Reseller
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-5 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2">assignment_late</span>
                                        <p class="text-sm font-medium">Tidak ada data pengambilan / pemakaian barang reseller pada periode ini.</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Silakan sesuaikan rentang tanggal atau pilih reseller lain.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($resellerUsageData) > 0)
                    <tfoot class="bg-slate-50 dark:bg-slate-900/80 font-bold border-t border-slate-200 dark:border-slate-700 text-xs">
                        <tr>
                            <td colspan="4" class="px-5 py-3 text-right uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Total Keseluruhan (Sesuai Filter):
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                {{ number_format($resellerSummary['total_terpakai'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-purple-600">
                                {{ number_format($resellerSummary['total_diretur'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-amber-600 dark:text-amber-400 font-bold">
                                {{ number_format($resellerSummary['total_sisa'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td></td>
                            <td class="px-5 py-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-black text-sm bg-emerald-100/50 dark:bg-emerald-950/40">
                                Rp {{ number_format($resellerSummary['nominal_terpakai'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-amber-600 dark:text-amber-400 font-black text-sm bg-amber-100/50 dark:bg-amber-950/40">
                                Rp {{ number_format($resellerSummary['nominal_sisa'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if($resellerUsageData instanceof \Illuminate\Pagination\LengthAwarePaginator && $resellerUsageData->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $resellerUsageData->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $resellerUsageData->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $resellerUsageData->total() }}</span> data
                </div>
                <div>
                    {{ $resellerUsageData->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
