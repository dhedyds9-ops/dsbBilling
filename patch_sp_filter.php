<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php';
$content = file_get_contents($file);
$content = str_replace(
    '$serviceProfiles = ServiceProfile::active()->get();',
    '$serviceProfiles = ServiceProfile::active()->whereIn(\'service_type\', [\'pppoe\', \'combined\'])->get();',
    $content
);
file_put_contents($file, $content);

$file2 = 'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php';
$content2 = file_get_contents($file2);
$content2 = str_replace(
    '$serviceProfiles = ServiceProfile::active()->get();',
    '$serviceProfiles = ServiceProfile::active()->whereIn(\'service_type\', [\'hotspot\', \'combined\'])->get();',
    $content2
);
file_put_contents($file2, $content2);

echo "Filtered service profiles.\n";
?>
