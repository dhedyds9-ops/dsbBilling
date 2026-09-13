<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Laporan Komisi & Margin</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Estimasi keuntungan bersih Anda dari selisih harga jual dan harga modal pada periode {{ $currentMonthName }}.</p>
        </div>
    </div>

    <div class="mb-4 flex justify-end">
    <a href="{{ route('reseller-portal.reports.commission.print') }}" target="_blank" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors shadow-sm text-sm font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">picture_as_pdf</span>
        Cetak PDF Laporan
    </a>
</div>
<!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
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

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/20 dark:to-slate-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">savings</span>
                </div>
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300">Estimasi Laba Bersih</h3>
            </div>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($netMargin, 0, ',', '.') }}</p>
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

    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl p-6 text-blue-800 dark:text-blue-300">
        <h4 class="font-bold mb-2 flex items-center gap-2"><span class="material-symbols-outlined notranslate text-lg" translate="no">info</span> Informasi Margin</h4>
        <p class="text-sm">Laporan komisi ini merupakan estimasi keuntungan bersih (*Net Profit*) yang didapatkan dari seluruh invoice yang telah berstatus LUNAS pada bulan berjalan. Perhitungan didapat dari: <strong>(Total Harga Jual ke Pelanggan) - (Harga Modal/Settlement dari Pusat)</strong>.</p>
    </div>
    <!-- Details Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mt-6 mb-6">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-200">Rincian Transaksi Komisi</h3>
            <select wire:model.live="perPage" class="pl-3 pr-8 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-6 py-3 text-left font-semibold">Tgl Lunas</th>
                        <th class="px-6 py-3 text-left font-semibold">Pelanggan / No Tagihan</th>
                        <th class="px-6 py-3 text-left font-semibold">Profil Paket</th>
                        <th class="px-6 py-3 text-right font-semibold">Harga Jual (Total)</th>
                        <th class="px-6 py-3 text-right font-semibold">Estimasi Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($details as $row)
                        @php
                            $subtotal = $row->items->sum('subtotal');
                            $cost = $row->items->sum('reseller_settlement_price');
                            if ($cost <= 0) {
                                $margin = $subtotal * 0.15; // fallback
                            } else {
                                $margin = $subtotal - $cost;
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                {{ $row->updated_at->format('d M Y') }}<br>
                                <span class="text-xs text-slate-400">{{ $row->updated_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $row->customer->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Inv: {{ $row->invoice_number }}</div>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $sp = $row->customer ? $row->customer->customerServices()->with('serviceProfile')->first() : null;
                                @endphp
                                @if($sp && $sp->serviceProfile)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $sp->serviceProfile->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                + Rp {{ number_format($margin, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">Belum ada rincian komisi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($details->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $details->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
</div>
