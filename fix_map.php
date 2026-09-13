<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Replace the iframe with the customer-map div using regex to handle whitespace
$pattern = '/<div class="h-72 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700">[\s\S]*?<iframe class="w-full h-full rounded-xl"[\s\S]*?<\/iframe>[\s\S]*?<\/div>/';

$newMap = '<div class="h-72 bg-slate-100 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 relative z-0" wire:ignore>
                      <div id="customer-map" class="w-full h-full rounded-xl"></div>
                  </div>';

$content = preg_replace($pattern, $newMap, $content);
file_put_contents($file, $content);
echo "Replaced iframe with map div.";
?>
