<?php
$file = 'D:/dsBilling/app/Navigation/MenuRegistry.php';
$content = file_get_contents($file);

$search = "                              ['label' => 'PPPoE', 'route' => 'reseller-portal.customers.pppoe', 'active' => 'reseller-portal.customers.pppoe'],";
$replace = "                              ['label' => 'Semua Pelanggan', 'route' => 'reseller-portal.customers.index', 'active' => 'reseller-portal.customers.index'],\n" . $search;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added Semua Pelanggan properly.\n";
?>
