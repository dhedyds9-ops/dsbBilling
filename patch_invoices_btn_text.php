<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoices.blade.php';
$content = file_get_contents($file);

$searchBtn = "<button wire:click=\"submitPayment\" class=\"px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors\">Simpan Pembayaran</button>";
$replaceBtn = "<button wire:click=\"submitPayment\" class=\"px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors\">Perpanjang</button>";

$content = str_replace($searchBtn, $replaceBtn, $content);
file_put_contents($file, $content);
echo "Updated button text to Perpanjang.\n";
?>
