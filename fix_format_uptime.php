<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/BaseOltDriver.php';
$content = file_get_contents($file);

$search = <<<PHP
        if (!is_numeric(\$trimmed)) {
            if (preg_match('/\((\d+)\)/', \$trimmed, \$m)) {
                \$ticks = (int)\$m[1];
            } else {
                return 'N/A';
            }
        }
PHP;

$replace = <<<PHP
        if (!is_numeric(\$trimmed)) {
            if (preg_match('/\((\d+)\)/', \$trimmed, \$m)) {
                \$ticks = (int)\$m[1];
            } elseif (preg_match('/^(\d+):(\d+):(\d+):(\d+)\.\d+$/', \$trimmed, \$m)) {
                // Parse format D:H:M:S.ms dari snmpget -O qv
                \$days = (int)\$m[1];
                \$hours = (int)\$m[2];
                \$mins = (int)\$m[3];
                \$result = [];
                if (\$days > 0) \$result[] = \$days . ' hari';
                if (\$hours > 0) \$result[] = \$hours . ' jam';
                if (\$mins > 0) \$result[] = \$mins . ' menit';
                return empty(\$result) ? '< 1 menit' : implode(' ', \$result);
            } else {
                return \$trimmed; // Jangan return N/A jika format tidak dikenali, return string aslinya agar tidak dianggap offline
            }
        }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed formatUptime\n";
