<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace public function save(ProvisioningService $provisioningService)
    // with public function save()
    $content = preg_replace('/public function save\(ProvisioningService \$provisioningService\)/', 'public function save()', $content);
    
    // Add $provisioningService = app(ProvisioningService::class);
    $content = preg_replace('/public function save\(\)\s*\{/', "public function save()\n    {\n        \$provisioningService = app(ProvisioningService::class);", $content);
    
    file_put_contents($file, $content);
}
echo "Removed DI from save().\n";
?>
