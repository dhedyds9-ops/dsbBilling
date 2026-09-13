<?php
$file = 'D:/dsBilling/resources/views/livewire/isp/hotspot-user/index.blade.php';
$content = file_get_contents($file);

// Replace the fake ID logic with the real customer code
$searchStr = '$dynamicId = $user->customer ? (($user->customer->created_at ? $user->customer->created_at->format(\'Ymd\') : date(\'Ymd\')) . str_pad($user->customer->id % 100, 2, \'0\', STR_PAD_LEFT)) : \'-\';';
$replaceStr = '$dynamicId = $user->customer ? $user->customer->code : \'-\';';

$content = str_replace($searchStr, $replaceStr, $content);
file_put_contents($file, $content);
echo "Fixed fake ID in hotspot blade.\n";
?>
