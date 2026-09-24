<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

$search = "    public \$wifiSsid = '';\n    public \$wifiPassword = '';";
$replace = "    public \$wifiSsid = '';\n    public \$wifiPassword = '';\n    public \$wifiSecurity = 'WPA2PSK';";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Show.php updated with wifiSecurity property.\n";
