<?php
$file = 'D:/dsBilling/app/Models/ISP/ServiceProfile.php';
$content = file_get_contents($file);

$search = "'reseller_price',";
$replace = "'reseller_price',\n        'owner_settlement_price',\n        'branch_settlement_price',\n        'reseller_settlement_price',";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added settlement prices to fillable.\n";
?>
