<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/reports/commission.blade.php';
$content = file_get_contents($file);

$tableHtml = <<<HTML
    <!-- Details Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mt-6 mb-6">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-200">Rincian Transaksi Komisi</h3>
            <select wire:model.live="perPage" class="pl-3 pr-8 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
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
                        <th class="px-6 py-3 text-right font-semibold">Harga Jual (Total)</th>
                        <th class="px-6 py-3 text-right font-semibold">Estimasi Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse(\$details as \$row)
                        @php
                            \$subtotal = \$row->items->sum('subtotal');
                            \$cost = \$row->items->sum('reseller_settlement_price');
                            if (\$cost <= 0) {
                                \$margin = \$subtotal * 0.15; // fallback
                            } else {
                                \$margin = \$subtotal - \$cost;
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                {{ \$row->updated_at->format('d M Y') }}<br>
                                <span class="text-xs text-slate-400">{{ \$row->updated_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ \$row->customer->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">Inv: {{ \$row->invoice_number }}</div>
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                Rp {{ number_format(\$subtotal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                + Rp {{ number_format(\$margin, 0, ',', '.') }}
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
        @if(\$details->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ \$details->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
</div>
HTML;

$content = str_replace("</div>\n</div>", "</div>\n" . $tableHtml, $content);
file_put_contents($file, $content);
echo "Updated Commission Blade.\n";
?>
