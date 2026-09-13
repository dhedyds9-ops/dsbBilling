<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoices.blade.php';
$content = file_get_contents($file);

// Replace action buttons in table
$searchActions = "<div class=\"flex items-center justify-center gap-3\">
                                    <button type=\"button\" class=\"text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium\" wire:click=\"viewDetail({{ \$row->id }})\">
                                        Detail
                                    </button>
                                    @if(\$row->status !== 'paid')
                                        <button type=\"button\" class=\"text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 text-sm font-medium flex items-center\" wire:click=\"markAsPaid({{ \$row->id }})\" wire:confirm=\"Apakah Anda yakin ingin menandai tagihan ini sebagai lunas? (Pembayaran Tunai)\">
                                            <span class=\"material-symbols-outlined notranslate text-sm mr-1\" translate=\"no\">payments</span> Bayar
                                        </button>
                                    @endif
                                </div>";
                                
$replaceActions = "<div class=\"flex items-center justify-center gap-2\">
                                    <button wire:click=\"viewDetail({{ \$row->id }})\" type=\"button\" class=\"inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors\" title=\"Detail\">
                                        <span class=\"material-symbols-outlined notranslate\" translate=\"no\" style=\"font-size:18px\">visibility</span>
                                    </button>
                                    @if(in_array(\$row->status, ['unpaid', 'partial', 'overdue', 'pending']))
                                        <button wire:click=\"openPaymentModal({{ \$row->id }})\" type=\"button\" class=\"inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 dark:text-emerald-400 transition-colors\" title=\"Proses Bayar\"> <span class=\"material-symbols-outlined notranslate\" translate=\"no\" style=\"font-size:18px\">payments</span> </button>
                                    @endif
                                </div>";
                                
$content = str_replace($searchActions, $replaceActions, $content);

// Replace detail modal action buttons
$searchDetailActions = "<button type=\"button\" x-on:click=\"show = false\" class=\"px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 font-medium transition-colors\">Tutup</button>
                        @if(\$selectedInvoice->status !== 'paid')
                            <button type=\"button\" wire:click=\"markAsPaid({{ \$selectedInvoice->id }})\" class=\"px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-colors flex items-center gap-1.5\" wire:confirm=\"Tandai tagihan ini sebagai lunas?\">
                                <span class=\"material-symbols-outlined notranslate text-sm\" translate=\"no\">payments</span> Tandai Lunas (Tunai)
                            </button>
                        @endif";
                        
$replaceDetailActions = "<button type=\"button\" x-on:click=\"show = false\" class=\"px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors\">Tutup</button>
                        @if(in_array(\$selectedInvoice->status, ['unpaid', 'partial', 'overdue', 'pending']))
                            <button type=\"button\" wire:click=\"openPaymentModal({{ \$selectedInvoice->id }})\" class=\"px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5\">
                                <span class=\"material-symbols-outlined notranslate\" translate=\"no\" style=\"font-size:18px\">payments</span> Proses Bayar
                            </button>
                        @endif";

$content = str_replace($searchDetailActions, $replaceDetailActions, $content);

// Insert Payment Modal before the script tag
$paymentModalHtml = <<<HTML
    {{-- PAYMENT MODAL --}}
    @if(\$showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-md p-4">
                <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Proses Pembayaran Cepat</h3>
                        <button wire:click="closePaymentModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                            <span class="material-symbols-outlined notranslate" translate="no">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 p-3 rounded-lg text-sm border border-blue-100 dark:border-blue-800">
                            Sisa tagihan yang harus dibayar: <strong>Rp {{ number_format(\$paymentInvoiceTotal, 0, ',', '.') }}</strong>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah Bayar (Rp)</label>
                            <input type="number" wire:model="paymentAmount" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode Pembayaran</label>
                            <select wire:model="paymentMethod" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                                <option value="cash">Tunai / Cash</option>
                                <option value="bank_transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-2">
                        <button wire:click="closePaymentModal" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">Batal</button>
                        <button wire:click="submitPayment" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">Simpan Pembayaran</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
HTML;

$content = str_replace("    <script>", $paymentModalHtml, $content);
file_put_contents($file, $content);
echo "Updated invoices.blade.php UI.\n";
?>
