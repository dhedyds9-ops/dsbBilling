<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php';
$content = file_get_contents($file);

// Strip out foreach($resellers) and foreach($branches) blocks
$content = preg_replace('/@foreach\(\$resellers.*?\@endforeach/s', '', $content);
$content = preg_replace('/@foreach\(\$branches.*?\@endforeach/s', '', $content);

file_put_contents($file, $content);
echo "Cleaned create blade.\n";
?>
