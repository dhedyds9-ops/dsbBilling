<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoices.blade.php';
$content = file_get_contents($file);

$searchBtn = "<button type=\"button\" x-on:click=\"show = false\" class=\"px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 font-medium transition-colors\">Tutup</button>";
$replaceBtn = "<button type=\"button\" x-on:click=\"show = false\" class=\"px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 font-medium transition-colors\">Tutup</button>
                        @if(\$selectedInvoice->status !== 'paid')
                            <button type=\"button\" wire:click=\"markAsPaid({{ \$selectedInvoice->id }})\" class=\"px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-colors flex items-center gap-1.5\" wire:confirm=\"Tandai tagihan ini sebagai lunas?\">
                                <span class=\"material-symbols-outlined notranslate text-sm\" translate=\"no\">payments</span> Tandai Lunas (Tunai)
                            </button>
                        @endif";

$content = str_replace($searchBtn, $replaceBtn, $content);
file_put_contents($file, $content);
echo "Added pay button to modal.\n";
?>
