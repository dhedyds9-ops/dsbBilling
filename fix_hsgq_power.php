<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/HsgqOltDriver.php';
$content = file_get_contents($file);

$search = <<<PHP
                \$parseIdx = function(\$idx) {
                    \$parts = explode('.', \$idx);
                    if (count(\$parts) >= 2) {
                        \$onuId = (int)array_pop(\$parts);
                        \$port = (int)array_pop(\$parts);
                        return \$port + \$onuId;
                    }
                    return (int)\$idx;
                };
PHP;

$replace = <<<PHP
                \$parseIdx = function(\$idx) {
                    // Di HSGQ, nilai \$idx bisa berupa "16777473" (untuk status/serial) 
                    // atau "16777473.1.1" (untuk RX/TX power).
                    // Bagian PERTAMA dari string tersebut selalu merupakan ONU Index yang utuh!
                    \$parts = explode('.', \$idx);
                    return (int)\$parts[0];
                };
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed HSGQ parseIdx\n";
