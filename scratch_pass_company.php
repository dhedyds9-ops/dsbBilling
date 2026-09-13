<?php
$file = 'resources/views/livewire/billing/invoice/show.blade.php';
$content = file_get_contents($file);

$content = str_replace('<x-billing.invoice-document :invoice="$invoice" />', '<x-billing.invoice-document :invoice="$invoice" :company="$company" />', $content);

file_put_contents($file, $content);
echo "Updated show.blade.php to pass company\n";
