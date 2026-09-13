<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoices.blade.php';
$content = file_get_contents($file);

$thSearch = '<th class="px-6 py-3 text-left font-semibold">Pelanggan</th>';
$thReplace = '<th class="px-6 py-3 text-left font-semibold">Pelanggan</th>' . "\n" . '                        <th class="px-6 py-3 text-left font-semibold">Profil Paket</th>';
$content = str_replace($thSearch, $thReplace, $content);

$tdSearch = <<<PHP
                            <td class="px-6 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ \$invoice->customer->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ \$invoice->customer->customer_id ?? '-' }}</div>
                            </td>
PHP;

$tdReplace = <<<PHP
                            <td class="px-6 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ \$invoice->customer->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ \$invoice->customer->customer_id ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    \$sp = \$invoice->customer ? \$invoice->customer->customerServices()->with('serviceProfile')->first() : null;
                                @endphp
                                @if(\$sp && \$sp->serviceProfile)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        {{ \$sp->serviceProfile->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
PHP;

$content = str_replace($tdSearch, $tdReplace, $content);
file_put_contents($file, $content);
echo "Invoices blade updated.\n";
?>
