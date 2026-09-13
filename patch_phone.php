<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace nullable with required for phone
    $content = str_replace("'phone'              => 'nullable|string|max:20',", "'phone'              => 'required|string|max:20',", $content);
    
    file_put_contents($file, $content);
}

$blades = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php'
];

foreach ($blades as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add asterisk to phone label
    $content = str_replace('<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">No. HP / WhatsApp</label>', '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">No. HP / WhatsApp <span class="text-red-500">*</span></label>', $content);
    
    file_put_contents($file, $content);
}

echo "Made phone required.\n";
?>
