<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/HsgqOltDriver.php';
$content = file_get_contents($file);

$search = <<<PHP
                \$parseIdx = function(\$idx) {
                    // Di HSGQ, nilai \$idx bisa berupa "16777473" (untuk status/serial) 
                    // atau "16777473.1.1" (untuk RX/TX power).
                    // Bagian PERTAMA dari string tersebut selalu merupakan ONU Index yang utuh!
                    \$parts = explode('.', \$idx);
                    return (int)\$parts[0];
                };
PHP;

$replace = <<<PHP
                \$parseIdx = function(\$idx) {
                    // Bersihkan base OID jika gagal di-strip oleh SnmpClient (misal di localhost/php-snmp)
                    // Hapus awalan .1.3.6.1.4.1.50224.3.12.x.x.x.
                    \$clean = preg_replace('/^(\.?iso|\.?1)\.3\.6\.1\.4\.1\.50224\.3\.12\.\d+\.\d+\.\d+\./i', '', \$idx);
                    
                    // Setelah bersih, nilai \$clean sisa "16777473" atau "16777473.1.1"
                    \$parts = explode('.', \$clean);
                    return (int)\$parts[0];
                };
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed parseIdx regex\n";
