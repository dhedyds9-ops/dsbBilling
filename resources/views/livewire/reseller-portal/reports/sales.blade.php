<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-3xl" translate="no">point_of_sale</span>
                Laporan Penjualan
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ringkasan aktivitas penjualan layanan dan voucher hotspot pada periode {{ $currentMonthName }}.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <select wire:model.live="selectedMonth" class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <select wire:model.live="selectedYear" class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('reseller-portal.reports.sales.print', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" target="_blank" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-1.5 transition-colors shadow-sm text-xs font-semibold">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">picture_as_pdf</span>
                Unduh PDF
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">group</span>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pelanggan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $totalCustomers }}</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">confirmation_number</span>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Voucher Terjual</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $totalVouchers }}</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">receipt_long</span>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tagihan Lunas</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $thisMonthInvoices }}</p>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50/50 to-white dark:from-amber-950/20 dark:to-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">payments</span>
            </div>
            <div>
                <p class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Total Penjualan</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400">Rp {{ number_format($totalSalesMonth, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Sales Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm sm:text-base">Penjualan Terbaru (Lunas / Terjual)</h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Periode: {{ $currentMonthName }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-6 py-3 text-left font-semibold">Tgl Transaksi</th>
                        <th class="px-6 py-3 text-left font-semibold">No Tagihan / Ref</th>
                        <th class="px-6 py-3 text-left font-semibold">Pelanggan / Keterangan</th>
                        <th class="px-6 py-3 text-left font-semibold">Layanan</th>
                        <th class="px-6 py-3 text-right font-semibold">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentSales as $sale)
                        @php
                            $isVoucher = str_starts_with((string)$sale['id'], 'VCR-');
                        @endphp
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-3 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $sale['paid_at'] }}</td>
                            <td class="px-6 py-3 font-medium text-blue-600 dark:text-blue-400 font-mono text-xs">{{ $sale['invoice_number'] }}</td>
                            <td class="px-6 py-3 text-slate-700 dark:text-slate-300 font-medium text-xs sm:text-sm">{{ $sale['customer_name'] ?? '-' }}</td>
                            <td class="px-6 py-3 text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                    {{ $sale['package_name'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm whitespace-nowrap">
                                Rp {{ number_format($sale['harga_ppn'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">Belum ada data penjualan terbaru pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
