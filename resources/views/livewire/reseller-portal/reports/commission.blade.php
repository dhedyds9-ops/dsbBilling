<div class="space-y-6">
    <!-- Header & Filter Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-emerald-600 text-3xl" translate="no">savings</span>
                Komisi & Margin
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Ringkasan komisi dan keuntungan bersih pada periode <span class="text-emerald-600 font-bold dark:text-emerald-500">{{ $currentMonthName }}</span>.
            </p>
        </div>

        <!-- Filter Periode (Bulan & Tahun) & Cetak PDF -->
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <select wire:model.live="selectedMonth" class="pl-3 pr-8 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm cursor-pointer">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <select wire:model.live="selectedYear" class="pl-3 pr-8 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm cursor-pointer">
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

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Penjualan -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Penjualan</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                    Rp {{ number_format($grossRevenue, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Total harga jual ke pelanggan</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">account_balance_wallet</span>
            </div>
        </div>

        <!-- 2. Komisi Reseller -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-emerald-200 dark:border-emerald-800/60 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div>
                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">savings</span>
                    Komisi Reseller
                </p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                    Rp {{ number_format($netMargin, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Laba bersih hak reseller</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">savings</span>
            </div>
        </div>

        <!-- 3. Harga Modal (Pusat) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-amber-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Harga Modal (Pusat)</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                    Rp {{ number_format($totalCost, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Setoran / settlement ke pusat</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">shopping_cart</span>
            </div>
        </div>

        <!-- 4. Rasio Margin -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rasio Margin</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    {{ number_format($profitMarginPercent, 1) }}%
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Persentase keuntungan bersih</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">percent</span>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <!-- Tabs & Toolbar -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/30">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto">
                <button type="button" wire:click="setFilterType('all')" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $filterType === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 border border-slate-200 dark:border-slate-700' }}">
                    Semua Transaksi
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterType === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $allCount }}</span>
                </button>
                <button type="button" wire:click="setFilterType('pelanggan')" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $filterType === 'pelanggan' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 border border-slate-200 dark:border-slate-700' }}">
                    Tagihan Pelanggan
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterType === 'pelanggan' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $pelangganCount }}</span>
                </button>
                <button type="button" wire:click="setFilterType('voucher')" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $filterType === 'voucher' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 border border-slate-200 dark:border-slate-700' }}">
                    Voucher Hotspot
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $filterType === 'voucher' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $voucherCount }}</span>
                </button>
            </div>

            <!-- Per Page Selector -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Tampilkan:</span>
                <select wire:model.live="perPage" class="pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm cursor-pointer">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">No Tagihan / Ref</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Pelanggan / Keterangan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tipe & Layanan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Harga Jual (Bruto)</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Komisi Reseller</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Setor Pusat (Modal)</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-center">Tgl Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($details as $row)
                        @php
                            $isVoucher = str_starts_with((string)$row['id'], 'VCR-');
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-xs font-semibold {{ $isVoucher ? 'text-teal-600 dark:text-teal-400' : 'text-blue-600 dark:text-blue-400' }}">
                                {{ $row['invoice_number'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm">
                                    {{ $row['customer_name'] }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    ID: {{ $row['customer_id'] }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide {{ $isVoucher ? 'bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-900/30 dark:text-teal-400' : 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                    {{ $row['service_type'] ?? ($isVoucher ? 'VOUCHER' : 'PELANGGAN') }}
                                </span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 ml-1">
                                    {{ $row['package_name'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-slate-900 dark:text-slate-100 text-xs sm:text-sm font-mono">
                                Rp {{ number_format($row['harga_ppn'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm font-mono">
                                Rp {{ number_format($row['fee_seller'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-mono">
                                Rp {{ number_format($row['profit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-xs text-slate-500 dark:text-slate-400">
                                {{ $row['paid_at'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-4xl text-slate-300 dark:text-slate-600 mb-2 block" translate="no">savings</span>
                                Tidak ada rincian komisi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($details->hasPages())
            <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
                {{ $details->links() }}
            </div>
        @endif
    </div>
</div>
