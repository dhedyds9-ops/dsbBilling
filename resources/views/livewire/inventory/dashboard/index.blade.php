<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-1">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-xs border border-blue-100 dark:border-blue-800/50">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">inventory_2</span>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        Dashboard Inventaris & Gudang
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Ringkasan eksekutif persediaan material, stok gudang, dan custody tim operasional ISP.
                    </p>
                </div>
            </div>
        </div>

        <!-- Header Toolbar (Filters & Panduan Button) -->
        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            @if(count($branches) > 1)
            <div class="relative min-w-[140px] sm:min-w-[160px]">
                <select wire:model.live="branchId" class="w-full h-10 pl-3 pr-8 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer shadow-xs">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="relative min-w-[140px] sm:min-w-[160px]">
                <select wire:model.live="warehouseId" class="w-full h-10 pl-3 pr-8 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer shadow-xs">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <a href="{{ route('inventory.guide.index') }}" class="h-10 px-3.5 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition-all flex items-center gap-1.5 shadow-sm hover:shadow shrink-0">
                <span class="material-symbols-outlined notranslate text-base" translate="no">menu_book</span>
                <span>Buku Panduan & SOP</span>
            </a>
        </div>
    </div>

    <!-- Quick Navigation Toolbar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar text-xs">
        <span class="text-slate-400 font-medium whitespace-nowrap mr-1 flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-sm notranslate" translate="no">bolt</span>
            Akses Cepat:
        </span>
        <a href="{{ route('inventory.inbounds.index') }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center gap-1.5 shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-sm text-emerald-500 notranslate" translate="no">move_to_inbox</span>
            Inbound Barang
        </a>
        <a href="{{ route('inventory.requisitions.index') }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center gap-1.5 shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-sm text-amber-500 notranslate" translate="no">assignment</span>
            Permintaan Barang
        </a>
        <a href="{{ route('inventory.custodies.index') }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center gap-1.5 shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-sm text-indigo-500 notranslate" translate="no">engineering</span>
            Custody Teknisi
        </a>
        <a href="{{ route('inventory.stock-card.index') }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center gap-1.5 shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-sm text-blue-500 notranslate" translate="no">receipt_long</span>
            Kartu Stok
        </a>
        <a href="{{ route('inventory.opnames.index') }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center gap-1.5 shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-sm text-purple-500 notranslate" translate="no">checklist</span>
            Stock Opname
        </a>
        <a href="{{ route('inventory.items.index') }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center gap-1.5 shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-sm text-slate-500 notranslate" translate="no">category</span>
            Master Barang
        </a>
    </div>

    <!-- Stat KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total SKU -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Katalog SKU</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white mt-1.5 tracking-tight">{{ number_format($totalItems) }}</h3>
                <span class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Material & Perangkat
                </span>
            </div>
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">category</span>
            </div>
        </div>

        <!-- Stok Fisik Gudang -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stok Fisik Gudang</p>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">{{ number_format($warehouseStockUnits) }}</h3>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Unit / Pcs</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1 flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>{{ number_format($warehouseStockMeters) }} Meter Kabel</span>
                </div>
            </div>
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">warehouse</span>
            </div>
        </div>

        <!-- Stok di Tangan Tim (Custody) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Custody Lapangan</p>
                <div class="flex items-baseline gap-1.5 mt-1.5">
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-amber-600 dark:text-amber-400 tracking-tight">{{ number_format($custodyStockUnits) }}</h3>
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300">Unit / Pcs</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1 flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>{{ number_format($custodyStockMeters) }} Meter Kabel</span>
                </div>
            </div>
            <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">engineering</span>
            </div>
        </div>

        <!-- Estimasi Nilai Stok -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estimasi Nilai Aset</p>
                <h3 class="text-xl sm:text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1.5 tracking-tight">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1 block">Berdasarkan HPP / Cost Price</span>
            </div>
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">payments</span>
            </div>
        </div>
    </div>

    <!-- Action Banners (Permintaan Pending, Retur Pending, Low Stock) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('inventory.requisitions.index') }}" class="p-4 rounded-2xl border border-amber-200/80 dark:border-amber-900/50 bg-amber-50/60 dark:bg-amber-950/20 flex items-center justify-between hover:shadow-md hover:border-amber-300 transition-all duration-200 group">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">pending_actions</span>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">{{ $pendingReqCount }} Permintaan Barang</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Menunggu persetujuan & pengeluaran gudang</p>
                </div>
            </div>
            <span class="material-symbols-outlined text-slate-400 group-hover:text-amber-600 group-hover:translate-x-0.5 transition-all text-xl notranslate" translate="no">chevron_right</span>
        </a>

        <a href="{{ route('inventory.returns.index') }}" class="p-4 rounded-2xl border border-purple-200/80 dark:border-purple-900/50 bg-purple-50/60 dark:bg-purple-950/20 flex items-center justify-between hover:shadow-md hover:border-purple-300 transition-all duration-200 group">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">fact_check</span>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">{{ $pendingReturnCount }} Retur Menunggu QC</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemeriksaan fisik teknisi & restock barang sisa</p>
                </div>
            </div>
            <span class="material-symbols-outlined text-slate-400 group-hover:text-purple-600 group-hover:translate-x-0.5 transition-all text-xl notranslate" translate="no">chevron_right</span>
        </a>

        <div class="p-4 rounded-2xl border {{ $lowStockCount > 0 ? 'border-rose-200/80 dark:border-rose-900/50 bg-rose-50/60 dark:bg-rose-950/20' : 'border-emerald-200/80 dark:border-emerald-900/50 bg-emerald-50/60 dark:bg-emerald-950/20' }} flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl {{ $lowStockCount > 0 ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">
                        {{ $lowStockCount > 0 ? 'warning' : 'check_circle' }}
                    </span>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">{{ $lowStockCount }} Item Stok Menipis</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $lowStockCount > 0 ? 'Segera lakukan inbound / PO restock' : 'Persediaan stok aman di atas buffer' }}</p>
                </div>
            </div>
            @if($lowStockCount > 0)
            <a href="{{ route('inventory.items.index') }}" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-600 text-white hover:bg-rose-700 transition shrink-0">
                Lihat
            </a>
            @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                Aman
            </span>
            @endif
        </div>
    </div>

    <!-- 2 Column Section: Low Stock Warning & Recent Mutations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Items List -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-500 notranslate text-xl" translate="no">production_quantity_limits</span>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            Peringatan Stok Minimum (Buffer Alert)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Monitoring barang yang mendekati batas habis</p>
                    </div>
                </div>
                <a href="{{ route('inventory.items.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5">
                    <span>Lihat Semua</span>
                    <span class="material-symbols-outlined text-sm notranslate" translate="no">arrow_forward</span>
                </a>
            </div>

            <div class="p-5 flex-1 flex flex-col justify-center">
                @if(count($lowStockItems) > 0)
                <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @foreach($lowStockItems as $it)
                    <div class="py-3.5 flex items-center justify-between first:pt-0 last:pb-0">
                        <div class="pr-3">
                            <span class="font-semibold text-sm text-slate-800 dark:text-white block">{{ $it->name }}</span>
                            <span class="text-xs text-slate-400 block font-mono mt-0.5">{{ $it->sku }} • Satuan: {{ $it->unit }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs rounded-lg font-bold bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">
                                Sisa: {{ (float) ($it->stocks_sum_quantity ?? 0) }} / Min: {{ (float) $it->min_stock }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-10 px-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3 shadow-xs">
                        <span class="material-symbols-outlined text-3xl notranslate" translate="no">verified</span>
                    </div>
                    <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Stok Barang Terkendali</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                        Semua persediaan barang berada dalam kondisi aman di atas batas minimum buffer stok.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('inventory.items.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-colors">
                            <span class="material-symbols-outlined text-sm notranslate" translate="no">search</span>
                            Cek Master Barang
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Mutations Ledger -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 notranslate text-xl" translate="no">receipt_long</span>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            Mutasi Persediaan Terkini (Ledger)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">10 catatan mutasi masuk, keluar, dan retur</p>
                    </div>
                </div>
                <a href="{{ route('inventory.stock-card.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5">
                    <span>Buka Kartu Stok</span>
                    <span class="material-symbols-outlined text-sm notranslate" translate="no">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/75 dark:bg-slate-900/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Waktu</th>
                            <th class="px-4 py-3 font-semibold">Barang</th>
                            <th class="px-4 py-3 font-semibold">Tipe</th>
                            <th class="px-4 py-3 text-right font-semibold">Mutasi</th>
                            <th class="px-4 py-3 text-right font-semibold">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($recentMutations as $mut)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $mut->created_at?->format('d/m H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-100 max-w-[180px] truncate" title="{{ $mut->item?->name ?? '-' }}">
                                {{ $mut->item?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                @php
                                    $isInbound = in_array($mut->type, ['inbound', 'return_good', 'adjustment_in']);
                                    $isReturn = str_contains($mut->type, 'return');
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                    @if($isReturn) bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300
                                    @elseif($isInbound) bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                                    @else bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 @endif">
                                    {{ strtoupper(str_replace('_', ' ', $mut->type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold whitespace-nowrap {{ (float)($mut->qty_in ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ (float)($mut->qty_in ?? 0) > 0 ? '+'.(float)$mut->qty_in : '-'.(float)($mut->qty_out ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ number_format((float)($mut->balance_after ?? 0)) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                <span class="material-symbols-outlined text-3xl notranslate block mb-1" translate="no">receipt_long</span>
                                Belum ada transaksi mutasi tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan 10 mutasi stok terakhir</span>
                <a href="{{ route('inventory.stock-card.index') }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                    Selengkapnya &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
