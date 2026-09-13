<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace text: detail.message with html: detail.message
    $content = str_replace("text: detail.message,", "html: detail.message,", $content);
    
    file_put_contents($file, $content);
}
echo "Updated swal to use html.\n";
?>
