<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoice-show.blade.php';
$content = file_get_contents($file);

// Replace billing.invoices.index with reseller-portal.billing.invoices
$content = str_replace("route('billing.invoices.index')", "route('reseller-portal.billing.invoices')", $content);

file_put_contents($file, $content);
echo "Copied and updated invoice-show blade.\n";
?>
