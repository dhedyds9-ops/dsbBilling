<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-3xl" translate="no">account_balance_wallet</span>
                Laporan Pendapatan
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Rincian penerimaan uang dari langganan pelanggan bulanan dan penjualan voucher hotspot pada periode {{ $currentMonthName }}.
            </p>
        </div>

        <!-- Filter Periode (Bulan & Tahun) -->
        <div class="flex items-center gap-2">
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
    <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800/80 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 inline-flex shadow-sm">
        <button wire:click="setFilterType('all')" type="button"
                class="px-4 py-2 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5
                       {{ $filterType === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span>Semua Pendapatan</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filterType === 'all' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">{{ $transactionCount }}</span>
        </button>
        <button wire:click="setFilterType('pelanggan')" type="button"
                class="px-4 py-2 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5
                       {{ $filterType === 'pelanggan' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span class="material-symbols-outlined notranslate text-sm" translate="no">account_circle</span>
            <span>Pendapatan Pelanggan</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">{{ $pelangganCount }}</span>
        </button>
        <button wire:click="setFilterType('voucher')" type="button"
                class="px-4 py-2 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5
                       {{ $filterType === 'voucher' ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span class="material-symbols-outlined notranslate text-sm" translate="no">confirmation_number</span>
            <span>Pendapatan Voucher</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300">{{ $voucherCount }}</span>
        </button>
    </div>

    <!-- Payments Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
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
                        <th class="px-6 py-3 text-left font-semibold">Metode</th>
                        <th class="px-6 py-3 text-right font-semibold">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    {{-- 1. Customer Payments --}}
                    @if($filterType !== 'voucher')
                        @foreach($recentPayments as $payment)
                            @php
                                $isVoucher = in_array($payment->id, $voucherPaymentIds ?? []);
                            @endphp
                            @if($filterType === 'pelanggan' && $isVoucher)
                                @continue
                            @endif
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3.5 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $payment->paid_at?->format('d M Y H:i') ?? $payment->created_at->format('d M Y H:i') }}
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
                                    {{ $payment->reference_number ?? '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-800 dark:text-slate-200">
                                    <div class="font-semibold text-xs">{{ $payment->customer?->name ?? ($isVoucher ? 'Voucher Hotspot Order' : '-') }}</div>
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                        {{ $payment->customer?->phone ?? ($payment->customer?->customer_id ?? 'Invoice Langganan') }}
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400 uppercase text-xs">
                                    {{ str_replace('_', ' ', $payment->method ?? 'cash') }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- 2. Vouchers Sold / Activated --}}
                    @if($filterType === 'voucher' || $filterType === 'all')
                        @foreach($recentVouchers as $voucher)
                            @php
                                $vPrice = $voucher->fee_seller > 0 ? $voucher->fee_seller : ($voucher->serviceProfile?->reseller_price ?: ($voucher->serviceProfile?->base_price ?: 0));
                            @endphp
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3.5 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $voucher->activated_at?->format('d M Y H:i') ?? $voucher->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">confirmation_number</span>
                                        Voucher
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-mono text-slate-700 dark:text-slate-300 text-xs">
                                    {{ $voucher->code }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-800 dark:text-slate-200">
                                    <div class="font-semibold text-xs">{{ $voucher->serviceProfile?->name ?? 'Voucher Hotspot' }}</div>
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                        Status: <span class="capitalize">{{ $voucher->status }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400 uppercase text-xs">
                                    Voucher Fisik
                                </td>
                                <td class="px-6 py-3.5 text-right font-mono font-bold text-sm text-teal-600 dark:text-teal-400 whitespace-nowrap">
                                    Rp {{ number_format($vPrice, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if($recentPayments->isEmpty() && $recentVouchers->isEmpty())
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-4xl text-slate-300 dark:text-slate-600 mb-2 block" translate="no">receipt_long</span>
                                Belum ada data pendapatan pada periode {{ $currentMonthName }}.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
