<?php
$file = 'D:/dsBilling/app/Navigation/MenuRegistry.php';
$content = file_get_contents($file);
$content = preg_replace(
    '/\$prefix = request\(\)->route\(\) \? request\(\)->route\(\)->getPrefix\(\) : \'\';/',
    '$prefix = request()->route() ? ltrim(request()->route()->getPrefix() ?? "", "/") : "";',
    $content
);
file_put_contents($file, $content);
echo "Patched MenuRegistry.php with regex\n";
?>
