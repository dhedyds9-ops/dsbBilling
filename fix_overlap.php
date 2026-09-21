<?php

function fixFile($file) {
    if (!file_exists($file)) { return; }
    $content = file_get_contents($file);
    
    // Fix select inputs: change 'px-4 py-2' to 'pl-3 pr-10 py-2' for selects with filters
    $content = preg_replace(
        '/<select ([^>]*)class="([^"]*)px-4 py-2([^"]*)"/',
        '<select $1class="$2pl-3 pr-10 py-2$3"',
        $content
    );
    
    // Fix search inputs: change 'pl-10' to 'pl-11' (44px padding)
    $content = preg_replace(
        '/<input ([^>]*)class="([^"]*)w-full pl-10 pr-4 py-2([^"]*)"/',
        '<input $1class="$2w-full pl-11 pr-4 py-2$3"',
        $content
    );
    
    // Also if it was still pl-9 for some reason
    $content = preg_replace(
        '/<input ([^>]*)class="([^"]*)w-full pl-9 pr-4 py-2([^"]*)"/',
        '<input $1class="$2w-full pl-11 pr-4 py-2$3"',
        $content
    );

    file_put_contents($file, $content);
}

$files = [
    "resources/views/livewire/inventory/asset-list.blade.php",
    "resources/views/livewire/isp/vendor/index.blade.php",
    "resources/views/livewire/isp/olt/index.blade.php",
    "resources/views/livewire/isp/router/index.blade.php",
    "resources/views/livewire/crm/customer/index.blade.php"
];

foreach ($files as $file) {
    fixFile($file);
}

echo "Fixed overlaps!";
