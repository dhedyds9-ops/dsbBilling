<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/sales/voucher.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    'fetch(`/isp/vouchers/preview-template/${templateId}`)',
    'fetch(`/reseller-portal/sales/voucher/preview-template/${templateId}`)',
    $content
);

file_put_contents($file, $content);
echo "Fixed fetch URL in blade.\n";
?>
