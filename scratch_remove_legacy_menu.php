<?php
$file = 'app/Navigation/MenuRegistry.php';
$content = file_get_contents($file);

$content = preg_replace("/\s*\['label'\s*=>\s*'Pengaturan Sistem',\s*'route'\s*=>\s*'admin\.settings\.index',\s*'active'\s*=>\s*'admin\.settings\.\*'\],/", "", $content);
// Also remove settings.billing just in case it exists somewhere
$content = preg_replace("/\s*\['label'\s*=>\s*'.*?',\s*'route'\s*=>\s*'admin\.settings\.billing',\s*'active'\s*=>\s*'.*?'\],/", "", $content);

file_put_contents($file, $content);
echo "Removed legacy settings from MenuRegistry\n";
