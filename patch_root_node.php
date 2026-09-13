<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/self-service/speed-test.blade.php';
$content = file_get_contents($file);

// Remove the section from the top
$content = preg_replace('/@section\(\'header_title\', \'Speedtest\'\)\s*/', '', $content);

// Add it just inside the root div
$search = '<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] flex flex-col items-center pb-24"';
$replace = $search . "\n" . '     @section(\'header_title\', \'Speedtest\')';
$content = str_replace($search, $replace, $content);

file_put_contents($file, $content);
echo "Moved @section inside root element.\n";
?>
