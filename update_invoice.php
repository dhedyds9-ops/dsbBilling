<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/billing/invoice-list.blade.php';

$content = <<<HTML
@section('header_title', 'Tagihan Saya')

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)]">
    
    <div class="mb-4">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Riwayat Tagihan</h1>
        <p class="text-xs text-slate-500 mt-1">Daftar semua invoice dan status pembayaran Anda.</p>
    </div>

    <div class="space-y-4">
        @forelse(\$invoices as \$invoice)
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/60 relative overflow-hidden">
            @if(\$invoice->status === 'paid')
                <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500/10 rounded-bl-full flex items-start justify-end p-2">
                    <span class="material-symbols-outlined text-emerald-500 text-lg">check_circle</span>
                </div>
            @elseif(\$invoice->status === 'overdue')
                <div class="absolute top-0 right-0 w-16 h-16 bg-red-500/10 rounded-bl-full flex items-start justify-end p-2">
                    <span class="material-symbols-outlined text-red-500 text-lg">warning</span>
                </div>
            @endif

            <div class="flex justify-between items-start mb-3 pr-10">
                <div>
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-0.5">INV-{{ \$invoice->invoice_number }}</p>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100">Rp {{ number_format(\$invoice->total_amount, 0, ',', '.') }}</h3>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3">
                <div>
                    <p class="text-[10px] text-slate-500">Tanggal Terbit</p>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ \$invoice->issue_date?->format('d/m/Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500">Jatuh Tempo</p>
                    <p class="text-xs font-semibold {{ \$invoice->status === 'overdue' ? 'text-red-600' : 'text-slate-700 dark:text-slate-300' }}">{{ \$invoice->due_date?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(\$invoice->status !== 'paid')
                    <a href="#" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Bayar Sekarang
                    </a>
                @endif
                <a href="#" class="flex-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-center py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    Lihat Detail
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm">
            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 mb-3 block">receipt_long</span>
            <p class="text-slate-500 text-sm">Belum ada tagihan.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ \$invoices->links('pagination::tailwind') }}
    </div>
</div>
HTML;

file_put_contents($file, $content);
echo "Updated invoice list.\n";
?>
