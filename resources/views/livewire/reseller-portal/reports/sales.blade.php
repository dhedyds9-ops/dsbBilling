<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Laporan Penjualan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ringkasan aktivitas penjualan layanan dan voucher Anda.</p>
        </div>
        <div>
            <a href="{{ route('reseller-portal.reports.sales.print') }}" target="_blank" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors shadow-sm text-sm font-medium">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">picture_as_pdf</span>
                Unduh PDF Laporan
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">group</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pelanggan Aktif</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $totalCustomers }}</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">confirmation_number</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Voucher Terjual</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $totalVouchers }}</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">receipt_long</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tagihan Bulan Ini</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $thisMonthInvoices }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Sales Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-200">5 Penjualan Terbaru (Lunas)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-6 py-3 text-left font-semibold">Tgl Lunas</th>
                        <th class="px-6 py-3 text-left font-semibold">No Tagihan</th>
                        <th class="px-6 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="px-6 py-3 text-right font-semibold">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentSales as $sale)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">{{ $sale->updated_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-3 font-medium text-blue-600 dark:text-blue-400">{{ $sale->invoice_number }}</td>
                            <td class="px-6 py-3 text-slate-700 dark:text-slate-300">{{ $sale->customer->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">Belum ada data penjualan terbaru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
