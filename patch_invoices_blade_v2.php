<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoices.blade.php';
$content = file_get_contents($file);

$modalHtml = <<<HTML
    <!-- Modal Detail Invoice -->
    <div x-data="{ show: @entangle('showDetailModal') }" x-show="show" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="show" x-transition.scale.origin.bottom sm.origin.center class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-slate-700">
                    
                    @if(\$selectedInvoice)
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100" id="modal-title">Detail Tagihan: {{ \$selectedInvoice->invoice_number }}</h3>
                        <button type="button" x-on:click="show = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 24px">close</span>
                        </button>
                    </div>
                    <div class="px-6 py-6">
                        <div class="flex justify-between mb-6">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold mb-1">Kepada</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ \$selectedInvoice->customer->name ?? '-' }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ \$selectedInvoice->customer->username ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold mb-1">Status</p>
                                @if(\$selectedInvoice->status === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Lunas</span>
                                @elseif(\$selectedInvoice->status === 'unpaid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Belum Lunas</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">{{ ucfirst(\$selectedInvoice->status) }}</span>
                                @endif
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Jatuh Tempo: {{ \$selectedInvoice->due_date ? \$selectedInvoice->due_date->format('d M Y') : '-' }}</p>
                            </div>
                        </div>

                        <table class="w-full text-sm mb-4">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                                    <th class="py-2 text-left font-semibold">Deskripsi Layanan</th>
                                    <th class="py-2 text-center font-semibold">Qty</th>
                                    <th class="py-2 text-right font-semibold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @forelse(\$selectedInvoice->items as \$item)
                                    <tr>
                                        <td class="py-3 text-slate-700 dark:text-slate-300">{{ \$item->description }}</td>
                                        <td class="py-3 text-center text-slate-700 dark:text-slate-300">{{ \$item->quantity }}</td>
                                        <td class="py-3 text-right font-mono text-slate-700 dark:text-slate-300">Rp {{ number_format(\$item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 text-center text-slate-500">Tidak ada rincian item.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-slate-200 dark:border-slate-700">
                                    <th colspan="2" class="py-3 text-right font-bold text-slate-800 dark:text-slate-200">Total Tagihan:</th>
                                    <th class="py-3 text-right font-bold font-mono text-indigo-600 dark:text-indigo-400 text-lg">Rp {{ number_format(\$selectedInvoice->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button" x-on:click="show = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 font-medium transition-colors">Tutup</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
HTML;

// Remove the last </div> and append the modal + closing div
$content = preg_replace('/<\/div>\s*$/', $modalHtml, $content);
file_put_contents($file, $content);
echo "Added modal properly.\n";
?>
