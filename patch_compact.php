<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Sales/VoucherIndex.php';
$content = file_get_contents($file);

$content = str_replace("'resellers', ", "", $content);

file_put_contents($file, $content);
echo "Removed resellers from compact.\n";
?>
