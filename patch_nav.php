<?php
$file = 'D:/dsBilling/resources/views/layouts/customer-app.blade.php';
$content = file_get_contents($file);

$content = str_replace("route('profile.edit')", "route('customer-portal.profile')", $content);
$content = str_replace("request()->routeIs('profile.edit')", "request()->routeIs('customer-portal.profile')", $content);

file_put_contents($file, $content);
echo "Updated bottom navigation to point to new profile route.\n";
?>
