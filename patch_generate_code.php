<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php'
];

$method = <<<EOT

    private function generateCustomerCode()
    {
        \$prefix = date('ymd');
        \$lastCodeUser = \App\Models\User::where('customer_code', 'like', \$prefix . '%')->max('customer_code');
        \$lastCodeCust = \App\Models\CRM\Customer::where('code', 'like', \$prefix . '%')->max('code');
        \$lastCode = max(\$lastCodeUser, \$lastCodeCust);
        
        \$nextSequence = 1;
        if (\$lastCode) {
            \$lastSequence = (int) substr(\$lastCode, -4);
            \$nextSequence = \$lastSequence + 1;
        }

        do {
            \$candidate = \$prefix . str_pad((string) \$nextSequence, 4, '0', STR_PAD_LEFT);
            \$exists = \App\Models\User::where('customer_code', \$candidate)->exists() || \App\Models\CRM\Customer::where('code', \$candidate)->exists();
            \$nextSequence++;
        } while (\$exists);
        
        return \$candidate;
    }
EOT;

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace Customer::generateCustomerCode() with $this->generateCustomerCode()
    $content = str_replace('Customer::generateCustomerCode()', '$this->generateCustomerCode()', $content);
    
    // Insert the private method before the last closing brace
    $pos = strrpos($content, '}');
    if ($pos !== false) {
        $content = substr_replace($content, $method . "\n}", $pos, 1);
    }
    
    file_put_contents($file, $content);
}
echo "Replaced generateCustomerCode.\n";
?>
