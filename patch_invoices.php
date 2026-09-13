<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/dashboard.blade.php';
$content = file_get_contents($file);

$invoicesHtml = <<<HTML
        <!-- Tagihan Terbaru Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-5 mt-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tagihan Terbaru</h2>
                <a href="{{ route('customer-portal.billing.invoice-list') }}" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua</a>
            </div>
            
            <div class="space-y-3">
                @forelse(\$recent_invoices->take(3) as \$invoice)
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ \$invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : (\$invoice->status === 'overdue' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400') }} flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">{{ \$invoice->status === 'paid' ? 'check_circle' : 'receipt_long' }}</span>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 dark:text-slate-100">Rp {{ number_format(\$invoice->total_amount, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ \$invoice->invoice_number }} &bull; {{ \$invoice->due_date?->format('d M Y') }}</div>
                        </div>
                    </div>
                    <div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ \$invoice->status === 'paid' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800' : (\$invoice->status === 'overdue' ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-900/30 dark:border-red-800' : 'bg-amber-50 text-amber-600 border border-amber-200 dark:bg-amber-900/30 dark:border-amber-800') }}">
                            {{ \$invoice->status === 'paid' ? 'Lunas' : (\$invoice->status === 'overdue' ? 'Jatuh Tempo' : 'Belum Lunas') }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                    <span class="material-symbols-outlined text-slate-400 mb-1" style="font-size: 24px;">task</span>
                    <p class="text-[11px] text-slate-500">Belum ada tagihan.</p>
                </div>
                @endforelse
            </div>
        </div>
HTML;

$searchPattern = '/\s*<!-- Layanan Aktif Card -->/s';
$replacement = "\n\n" . $invoicesHtml . "\n\n        <!-- Layanan Aktif Card -->";

$content = preg_replace($searchPattern, $replacement, $content);
file_put_contents($file, $content);
echo "Added Recent Invoices section.\n";
?>
