<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php';
$content = file_get_contents($file);

// Replace required with nullable
$content = str_replace("'router_id'          => 'required|exists:routers,id',", "'router_id'          => 'nullable|exists:routers,id',", $content);

file_put_contents($file, $content);
echo "Made router_id nullable.\n";
?>
