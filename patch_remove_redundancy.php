<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$searchRoute = "Route::get('/commission', \App\Livewire\ResellerPortal\Finance\Commission::class)->name('commission');";
$content = str_replace($searchRoute, "", $content);

file_put_contents($file, $content);

$menuFile = 'D:/dsBilling/app/Navigation/MenuRegistry.php';
$menuContent = file_get_contents($menuFile);

$searchMenu = "                            ['label' => 'Komisi / Margin', 'route' => 'reseller-portal.finance.commission', 'active' => 'reseller-portal.finance.commission'],\n";
$menuContent = str_replace($searchMenu, "", $menuContent);

file_put_contents($menuFile, $menuContent);
echo "Cleaned up routes and menus.\n";
?>
