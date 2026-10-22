<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-3xl" translate="no">account_balance_wallet</span>
                Pemasukan Harian
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Ringkasan Periode : <span class="text-emerald-600 font-bold dark:text-emerald-500">{{ $periodLabel }}</span>
            </p>
        </div>

        <!-- Filter Periode (Harian / Bulanan) -->
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <!-- Mode Toggle -->
            <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-700/60 p-1">
                <button type="button" wire:click="setPeriodMode('daily')" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $periodMode === 'daily' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                    Harian
                </button>
                <button type="button" wire:click="setPeriodMode('monthly')" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $periodMode === 'monthly' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                    Bulanan
                </button>
            </div>

            @if($periodMode === 'daily')
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="selectedDate" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                    <button type="button" wire:click="setToday" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm">
                        Hari Ini
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedMonth" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                    <select wire:model.live="selectedYear" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Cards (Breakdown Pelanggan vs Voucher) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pendapatan -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pendapatan</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                    {{ $transactionCount }} total transaksi sukses
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">payments</span>
            </div>
        </div>

        <!-- Pendapatan Pelanggan -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-indigo-100 dark:border-indigo-900/40 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
            <div>
                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">account_circle</span>
                    Pendapatan Pelanggan
                </p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-2">
                    Rp {{ number_format($pelangganRevenue, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                    {{ $pelangganCount }} tagihan PPPoE & Hotspot
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">supervised_user_circle</span>
            </div>
        </div>

        <!-- Pendapatan Voucher -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-teal-100 dark:border-teal-900/40 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-500 to-emerald-500"></div>
            <div>
                <p class="text-xs font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider flex items-center gap-1">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">confirmation_number</span>
                    Pendapatan Voucher
                </p>
                <p class="text-2xl font-black text-teal-600 dark:text-teal-400 mt-2">
                    Rp {{ number_format($voucherRevenue, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                    {{ $voucherCount }} voucher terjual / aktif
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center text-teal-600 dark:text-teal-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">confirmation_number</span>
            </div>
        </div>

        <!-- Rata-Rata Transaksi -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-Rata Transaksi</p>
                <p class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-2">
                    Rp {{ number_format($averagePayment, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                    Per transaksi diterima
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">analytics</span>
            </div>
        </div>
    </div>

    <!-- Filter Tab Navigation -->
    <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800/80 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 overflow-x-auto w-full sm:w-auto inline-flex shadow-sm">
        <button wire:click="setFilterType('all')" type="button"
                class="whitespace-nowrap px-3 sm:px-4 py-2 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5
                       {{ $filterType === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span>Semua Pendapatan</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filterType === 'all' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">{{ $transactionCount }}</span>
        </button>
        <button wire:click="setFilterType('pelanggan')" type="button"
                class="whitespace-nowrap px-3 sm:px-4 py-2 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5
                       {{ $filterType === 'pelanggan' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span class="material-symbols-outlined notranslate text-sm" translate="no">account_circle</span>
            <span>Pendapatan Pelanggan</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">{{ $pelangganCount }}</span>
        </button>
        <button wire:click="setFilterType('voucher')" type="button"
                class="whitespace-nowrap px-3 sm:px-4 py-2 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5
                       {{ $filterType === 'voucher' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span class="material-symbols-outlined notranslate text-sm" translate="no">confirmation_number</span>
            <span>Pendapatan Voucher</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300">{{ $voucherCount }}</span>
        </button>
    </div>

    <!-- Payments Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-lg" translate="no">history</span>
                Riwayat Transaksi Pendapatan {{ $filterType !== 'all' ? '(' . ucfirst($filterType) . ')' : '' }}
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Periode: {{ $currentMonthName }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-6 py-3 text-left font-semibold">Tgl Transaksi</th>
                        <th class="px-6 py-3 text-left font-semibold">Kategori</th>
                        <th class="px-6 py-3 text-left font-semibold">Referensi</th>
                        <th class="px-6 py-3 text-left font-semibold">Pelanggan / Keterangan</th>
                        <th class="px-6 py-3 text-left font-semibold">Layanan</th>
                        <th class="px-6 py-3 text-right font-semibold">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentTransactions as $row)
                        @php
                            $isVoucher = str_starts_with((string)$row['id'], 'VCR-');
                        @endphp
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-3.5 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $row['paid_at'] }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                @if($isVoucher)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">confirmation_number</span>
                                        Voucher
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">account_circle</span>
                                        Pelanggan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-700 dark:text-slate-300 text-xs">
                                {{ $row['invoice_number'] }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-800 dark:text-slate-200">
                                <div class="font-semibold text-xs">{{ $row['customer_name'] ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">
                                    {{ $row['customer_id'] ?? '' }}
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400 text-xs">
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $row['package_name'] ?? '-' }}</span>
                                <span class="block text-[10px] text-slate-400 font-semibold">{{ $row['service_type'] }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                Rp {{ number_format($row['harga_ppn'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-4xl text-slate-300 dark:text-slate-600 mb-2 block" translate="no">receipt_long</span>
                                Belum ada data pendapatan pada periode {{ $currentMonthName }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
