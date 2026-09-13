<?php
$src = 'D:/dsBilling/resources/views/livewire/isp/user-online/index.blade.php';
$dst = 'D:/dsBilling/resources/views/livewire/reseller-portal/customer/user-online.blade.php';

$content = file_get_contents($src);
file_put_contents($dst, $content);
echo "Created UserOnline blade.\n";
?>
