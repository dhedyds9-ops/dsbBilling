<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-icons-outlined text-blue-600">dashboard</span>
                Dashboard Inventaris & Gudang
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Ringkasan eksekutif persediaan material, stok gudang, dan custody tim operasional ISP.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(count($branches) > 1)
            <div class="min-w-[190px]">
                <select wire:model.live="branchId" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="min-w-[190px]">
                <select wire:model.live="warehouseId" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Stat KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total SKU -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Katalog SKU</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($totalItems) }}</h3>
                <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Material & Perangkat</span>
            </div>
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center">
                <span class="material-icons-outlined">category</span>
            </div>
        </div>

        <!-- Stok Fisik Gudang -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stok Fisik Gudang</p>
                <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($totalWarehouseStock, 2) }}</h3>
                <span class="text-xs text-slate-500 font-medium">Unit / Meter persediaan</span>
            </div>
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center">
                <span class="material-icons-outlined">warehouse</span>
            </div>
        </div>

        <!-- Stok di Tangan Tim (Custody) -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Custody Lapangan</p>
                <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totalInCustody, 2) }}</h3>
                <span class="text-xs text-amber-600 font-medium">Di Tangan Teknisi / Reseller</span>
            </div>
            <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center">
                <span class="material-icons-outlined">engineering</span>
            </div>
        </div>

        <!-- Estimasi Nilai Stok -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estimasi Nilai Aset</p>
                <h3 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</h3>
                <span class="text-xs text-slate-500 font-medium">Berdasarkan HPP / Cost Price</span>
            </div>
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center">
                <span class="material-icons-outlined">payments</span>
            </div>
        </div>
    </div>

    <!-- Alert Bar (Permintaan Pending, Retur Pending, Low Stock) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('inventory.requisitions.index') }}" class="p-4 rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50 dark:bg-amber-950/20 flex items-center gap-4 hover:shadow-md transition">
            <span class="material-icons-outlined text-amber-600 text-3xl">pending_actions</span>
            <div>
                <h4 class="font-bold text-slate-800 dark:text-white">{{ $pendingReqCount }} Permintaan Barang</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Menunggu persetujuan & pengeluaran gudang</p>
            </div>
        </a>

        <a href="{{ route('inventory.returns.index') }}" class="p-4 rounded-xl border border-purple-200 dark:border-purple-900/60 bg-purple-50 dark:bg-purple-950/20 flex items-center gap-4 hover:shadow-md transition">
            <span class="material-icons-outlined text-purple-600 text-3xl">fact_check</span>
            <div>
                <h4 class="font-bold text-slate-800 dark:text-white">{{ $pendingReturnCount }} Retur Menunggu QC</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pemeriksaan fisik teknisi & restock barang sisa</p>
            </div>
        </a>

        <div class="p-4 rounded-xl border {{ $lowStockCount > 0 ? 'border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/20' : 'border-emerald-200 dark:border-emerald-900/60 bg-emerald-50 dark:bg-emerald-950/20' }} flex items-center gap-4">
            <span class="material-icons-outlined {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-emerald-600' }} text-3xl">
                {{ $lowStockCount > 0 ? 'warning' : 'check_circle' }}
            </span>
            <div>
                <h4 class="font-bold text-slate-800 dark:text-white">{{ $lowStockCount }} Item Stok Menipis</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $lowStockCount > 0 ? 'Segera lakukan inbound / PO restock' : 'Persediaan stok aman di atas buffer' }}</p>
            </div>
        </div>
    </div>

    <!-- 2 Column Section: Low Stock Warning & Recent Mutations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Items List -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-icons-outlined text-rose-500">production_quantity_limits</span>
                    Peringatan Stok Minimum (Buffer Alert)
                </h3>
                <a href="{{ route('inventory.items.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="p-5">
                @if(count($lowStockItems) > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($lowStockItems as $it)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-sm text-slate-800 dark:text-white">{{ $it->name }}</span>
                            <span class="text-xs text-slate-400 block font-mono">{{ $it->sku }} ({{ $it->unit }})</span>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 text-xs rounded-full font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                Sisa: {{ (float) ($it->stocks_sum_quantity ?? 0) }} / Min: {{ (float) $it->min_stock }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-slate-400 text-sm">
                    <span class="material-icons-outlined text-emerald-500 text-4xl block mb-2">thumb_up</span>
                    Semua stok barang berada dalam kondisi aman di atas batas minimum.
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Mutations Ledger -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-icons-outlined text-blue-600">receipt_long</span>
                    Mutasi Persediaan Terkini (Ledger)
                </h3>
                <a href="{{ route('inventory.stock-card.index') }}" class="text-xs text-blue-600 hover:underline">Buka Kartu Stok</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-2.5">Waktu</th>
                            <th class="px-4 py-2.5">Barang</th>
                            <th class="px-4 py-2.5">Tipe</th>
                            <th class="px-4 py-2.5 text-right">Mutasi</th>
                            <th class="px-4 py-2.5 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($recentMutations as $mut)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-750">
                            <td class="px-4 py-2 text-slate-500">{{ $mut->created_at->format('d/m H:i') }}</td>
                            <td class="px-4 py-2 font-medium text-slate-800 dark:text-white">{{ $mut->item?->name ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                    {{ in_array($mut->type, ['inbound', 'return_good', 'adjustment_in']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                                    {{ strtoupper(str_replace('_', ' ', $mut->type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right font-mono font-bold {{ $mut->qty_in > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $mut->qty_in > 0 ? '+'.(float)$mut->qty_in : '-'.(float)$mut->qty_out }}
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-slate-600 dark:text-slate-300">
                                {{ (float)$mut->balance_after }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada transaksi mutasi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
