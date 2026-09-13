<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Sales/Voucher.php';
$newFile = 'D:/dsBilling/app/Livewire/ResellerPortal/Sales/VoucherIndex.php';
$content = file_get_contents($file);

$content = str_replace('class Voucher extends \App\Livewire\AdminComponent', 'class VoucherIndex extends \App\Livewire\AdminComponent', $content);

file_put_contents($newFile, $content);
unlink($file);
echo "Renamed class and file to VoucherIndex.\n";
?>
