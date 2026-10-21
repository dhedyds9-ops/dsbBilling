<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-emerald-600 text-3xl" translate="no">savings</span>
                Laporan Komisi & Margin
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Estimasi keuntungan bersih Anda dari selisih harga jual dan harga modal pada periode {{ $currentMonthName }}.</p>
        </div>

        <!-- Filter Periode (Bulan & Tahun) & Cetak PDF -->
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
            <a href="{{ route('reseller-portal.reports.commission.print', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" target="_blank" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-1.5 transition-colors shadow-sm text-xs font-semibold">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">picture_as_pdf</span>
                Cetak PDF
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">account_balance_wallet</span>
                </div>
                <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Penjualan</h3>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">shopping_cart</span>
                </div>
                <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400">Harga Modal (Pusat)</h3>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($totalCost, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50/60 to-white dark:from-emerald-950/20 dark:to-slate-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">savings</span>
                </div>
                <h3 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Estimasi Laba Bersih</h3>
            </div>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($netMargin, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">percent</span>
                </div>
                <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400">Rasio Margin</h3>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($profitMarginPercent, 1) }}%</p>
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4 text-blue-800 dark:text-blue-300">
        <h4 class="font-bold mb-1 flex items-center gap-2 text-sm"><span class="material-symbols-outlined notranslate text-base" translate="no">info</span> Informasi Perhitungan Komisi</h4>
        <p class="text-xs leading-relaxed">Laporan komisi ini terintegrasi langsung dengan mesin perhitungan settlement ISP dsBilling. Dihitung dari seluruh invoice pelanggan bulanan lunas dan voucher hotspot yang aktif/terjual: <strong>(Total Harga Jual ke Pelanggan) - (Harga Modal/Settlement dari Pusat)</strong>.</p>
    </div>

    <!-- Details Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mt-6 mb-6">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 bg-slate-50 dark:bg-slate-900/50">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm sm:text-base">Rincian Transaksi Komisi</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">({{ $details->total() }} transaksi)</span>
            </div>
            <select wire:model.live="perPage" class="w-full sm:w-auto pl-3 pr-8 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-4 sm:px-6 py-3 text-left font-semibold">Tgl Transaksi</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-semibold">Pelanggan / No Tagihan</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-semibold">Profil Paket / Layanan</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-semibold">Harga Jual</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-semibold">Modal (Pusat)</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-semibold">Estimasi Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($details as $row)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 sm:px-6 py-3 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $row['paid_at'] }}
                            </td>
                            <td class="px-4 sm:px-6 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200 text-xs sm:text-sm">{{ $row['customer_name'] ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">Ref: {{ $row['invoice_number'] }}</div>
                            </td>
                            <td class="px-4 sm:px-6 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                    {{ $row['package_name'] ?? '-' }}
                                </span>
                                <span class="block text-[10px] text-slate-400 mt-0.5 font-semibold">{{ $row['service_type'] }}</span>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right font-mono text-xs sm:text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                Rp {{ number_format($row['harga_ppn'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right font-mono text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                Rp {{ number_format($row['profit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono text-xs sm:text-sm whitespace-nowrap">
                                + Rp {{ number_format($row['fee_seller'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">Belum ada rincian transaksi komisi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($details->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 overflow-x-auto">
                {{ $details->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
</div>
