<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Sales/VoucherIndex.php';
$content = file_get_contents($file);

// Remove $resellers line
$content = preg_replace('/\$resellers = \$userQueryService->getResellers\(\);/', '', $content);
// Remove 'resellers' => $resellers from view
$content = preg_replace("/'resellers'\s*=>\s*\\\$resellers,/", "", $content);

file_put_contents($file, $content);
echo "Removed resellers variable from VoucherIndex.\n";
?>
