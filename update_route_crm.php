<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);
$content = str_replace("\App\Livewire\Crm\Customer\Show::class", "\App\Livewire\Crm\Customer\Customer360::class", $content);
file_put_contents($file, $content);
echo "Updated routes/web.php to use Customer360.";
?>
