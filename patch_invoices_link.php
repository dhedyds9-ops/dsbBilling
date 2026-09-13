<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoices.blade.php';
$content = file_get_contents($file);

// Replace button with anchor tag
$searchDetail = "<button wire:click=\"viewDetail({{ \$row->id }})\" type=\"button\" class=\"inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors\" title=\"Detail\">
                                        <span class=\"material-symbols-outlined notranslate\" translate=\"no\" style=\"font-size:18px\">visibility</span>
                                    </button>";
                                    
$replaceDetail = "<a href=\"{{ route('reseller-portal.billing.invoices.show', \$row->id) }}\" class=\"inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors\" title=\"Detail\">
                                        <span class=\"material-symbols-outlined notranslate\" translate=\"no\" style=\"font-size:18px\">visibility</span>
                                    </a>";

$content = str_replace($searchDetail, $replaceDetail, $content);

file_put_contents($file, $content);
echo "Updated detail link in index.\n";
?>
