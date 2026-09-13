<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/reports/sales.blade.php';
$content = file_get_contents($file);

$thSearch = '<th class="px-6 py-3 text-left font-semibold">Tipe Layanan</th>';
$thReplace = '<th class="px-6 py-3 text-left font-semibold">Tipe Layanan</th>' . "\n" . '                        <th class="px-6 py-3 text-left font-semibold">Profil Paket</th>';
$content = str_replace($thSearch, $thReplace, $content);

$tdSearch = <<<PHP
                            <td class="px-6 py-3">
                                @if(\$sale->contract_id)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">router</span>
                                        Internet/PPPoE
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">confirmation_number</span>
                                        Voucher/Hotspot
                                    </span>
                                @endif
                            </td>
PHP;

$tdReplace = <<<PHP
                            <td class="px-6 py-3">
                                @if(\$sale->contract_id)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">router</span>
                                        Internet/PPPoE
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">confirmation_number</span>
                                        Voucher/Hotspot
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    \$sp = \$sale->customer ? \$sale->customer->customerServices()->with('serviceProfile')->first() : null;
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
echo "Sales blade updated.\n";
?>
