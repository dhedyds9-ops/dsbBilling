<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Komisi & Margin</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Estimasi keuntungan dari selisih harga jual ke pelanggan dan harga modal dari Pusat.</p>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
        <div class="flex-1 w-full relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pelanggan / voucher..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 text-left font-semibold">Tgl Penjualan</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan / Layanan</th>
                        <th class="px-4 py-3 text-left font-semibold">Harga Jual</th>
                        <th class="px-4 py-3 text-left font-semibold">Estimasi Margin</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($commissions as $row)
                        @php
                            $price = $row->serviceProfile?->price ?? 0;
                            $margin = $price * 0.15; // Placeholder 15% margin
                        @endphp
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                {{ $row->updated_at->format('d M Y') }}<br>
                                <span class="text-xs text-slate-400">{{ $row->updated_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    Voucher: {{ $row->username }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $row->serviceProfile?->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-mono">
                                Rp {{ number_format($price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                + Rp {{ number_format($margin, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Diterima
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">account_balance_wallet</span>
                                    <p>Belum ada data komisi penjualan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $commissions->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
</div>
