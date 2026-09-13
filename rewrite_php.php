<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

$tabsPattern = '/protected \$tabs = \[[\s\S]*?\];/';
$newTabs = <<<PHP
protected \$tabs = [
        'profile' => 'Profil & Lokasi',
        'finance' => 'Keuangan & Tagihan',
        'device'  => 'Perangkat & Jaringan',
        'support' => 'Support & Teknis',
        'history' => 'Riwayat & Log',
    ];
PHP;
$content = preg_replace($tabsPattern, $newTabs, $content);
file_put_contents($file, $content);
echo "Updated tabs array in PHP.";
?>
