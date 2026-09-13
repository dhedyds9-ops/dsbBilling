<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/dashboard.blade.php';
$content = file_get_contents($file);
$content = str_replace('Login Voucher Hari Ini</p>', 'Voucher Aktif (Online)</p>', $content);
file_put_contents($file, $content);
echo "Fixed voucher online label.\n";
?>
