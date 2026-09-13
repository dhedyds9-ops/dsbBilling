<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Sales/VoucherIndex.php';
$content = file_get_contents($file);

$content = str_replace(
    "Voucher::where('type'", 
    "Voucher::where('reseller_id', auth()->id())->where('type'", 
    $content
);

file_put_contents($file, $content);
echo "Isolated stats to reseller_id.\n";
?>
