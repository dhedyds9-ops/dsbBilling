<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    $search = "public function save()\n    {\n        \$provisioningService = app(ProvisioningService::class);\n        try {\n            \$this->validate";
    $replace = "public function save()\n    {\n        if (\$this->login_method === 'username_only') {\n            \$this->password = \$this->username;\n        }\n\n        \$provisioningService = app(ProvisioningService::class);\n        try {\n            \$this->validate";
    
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
}
echo "Added logic for login_method.\n";
?>
