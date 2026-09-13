<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Sales/VoucherIndex.php';
$content = file_get_contents($file);

$content = str_replace(
    'class VoucherIndex extends \App\Livewire\AdminComponent',
    'class VoucherIndex extends \App\Livewire\ISP\BaseNetworkComponent',
    $content
);

file_put_contents($file, $content);
echo "Extended BaseNetworkComponent.\n";
?>
