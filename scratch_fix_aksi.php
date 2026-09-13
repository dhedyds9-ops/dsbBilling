<?php
$file = 'resources/views/livewire/billing/payment/index.blade.php';
$content = file_get_contents($file);

// Find the Aksi td and replace the contents
$pattern = '/<td class="px-4 py-3 text-center whitespace-nowrap">.*?<\/td>/s';
$replacement = <<<BLADE
<td class="px-4 py-3 text-center whitespace-nowrap">
    @if(\$payment->invoices->count() > 0)
        <a href="{{ route('billing.invoices.show', \$payment->invoices->first()->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors" title="Print Invoice">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
        </a>
    @else
        <a href="{{ route('billing.payments.show', \$payment->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-slate-50 hover:bg-slate-100 text-slate-600 dark:bg-slate-900/20 dark:hover:bg-slate-900/40 dark:text-slate-400 transition-colors" title="Detail">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
        </a>
    @endif
    @if(auth()->user()->hasRole('superadmin'))
        <button wire:click="delete({{ \$payment->id }})" onclick="confirm('Yakin ingin menghapus data pembayaran ini?') || event.stopImmediatePropagation()" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/20 dark:hover:bg-red-900/40 dark:text-red-400 transition-colors" title="Hapus">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
        </button>
    @endif
</td>
BLADE;

$content = preg_replace($pattern, $replacement, $content);

// Also fix the invoice column text to show the first invoice properly
$invPattern = '/<td class="px-4 py-3 text-slate-600 dark:text-slate-400">\s*@if\(\$payment->invoice\).*?<\/td>/s';
$invReplacement = <<<BLADE
<td class="px-4 py-3 text-slate-600 dark:text-slate-400">
    @if(\$payment->invoices->count() > 0)
        <a href="{{ route('billing.invoices.show', \$payment->invoices->first()->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
            {{ \$payment->invoices->first()->invoice_number }}
        </a>
    @else
        -
    @endif
</td>
BLADE;
$content = preg_replace($invPattern, $invReplacement, $content);

file_put_contents($file, $content);
echo "Updated Aksi column\n";
