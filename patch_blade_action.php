<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/sales/voucher.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    'action="{{ route(\'reseller-portal.sales.voucher\') }}"',
    'action="{{ route(\'reseller-portal.sales.voucher.print\') }}"',
    $content
);

file_put_contents($file, $content);
echo "Fixed action in blade.\n";
?>
