<?php
$file = 'D:/dsBilling/app/Navigation/MenuRegistry.php';
$content = file_get_contents($file);

// Replace inside buildResellerNavigation specifically
$pattern = "/('label' => 'Pelanggan Saya',\s*'icon' => 'people',\s*'items' => \[)/";
$replacement = "$1\n                              ['label' => 'Semua Pelanggan', 'route' => 'reseller-portal.customers.index', 'active' => 'reseller-portal.customers.index'],";

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "Added Semua Pelanggan properly with regex.\n";
?>
