<?php
$file = 'D:/dsBilling/app/Navigation/MenuRegistry.php';
$content = file_get_contents($file);

$search = "['label' => 'Hotspot', 'route' => 'reseller-portal.customers.hotspot', 'active' => 'reseller-portal.customers.hotspot'],";
$replace = "['label' => 'Hotspot', 'route' => 'reseller-portal.customers.hotspot', 'active' => 'reseller-portal.customers.hotspot'],\n                              ['label' => 'Sesi Online', 'route' => 'reseller-portal.customers.user-online', 'active' => 'reseller-portal.customers.user-online'],";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added Sesi Online to MenuRegistry.\n";
?>
