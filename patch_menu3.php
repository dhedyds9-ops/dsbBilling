<?php
$file = 'D:/dsBilling/app/Navigation/MenuRegistry.php';
$content = file_get_contents($file);

$search = <<<'PHP'
                      [
                          'label' => 'Pelanggan Saya',
                          'icon' => 'people',
                          'items' => [
                              ['label' => 'PPPoE', 'route' => 'reseller-portal.customers.pppoe', 'active' => 'reseller-portal.customers.pppoe'],
PHP;

$replace = <<<'PHP'
                      [
                          'label' => 'Pelanggan Saya',
                          'icon' => 'people',
                          'items' => [
                              ['label' => 'Semua Pelanggan', 'route' => 'reseller-portal.customers.index', 'active' => 'reseller-portal.customers.index'],
                              ['label' => 'PPPoE', 'route' => 'reseller-portal.customers.pppoe', 'active' => 'reseller-portal.customers.pppoe'],
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added Semua Pelanggan to MenuRegistry.\n";
?>
