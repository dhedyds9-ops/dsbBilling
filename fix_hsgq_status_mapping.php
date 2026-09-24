<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/HsgqOltDriver.php';
$content = file_get_contents($file);

$search = <<<PHP
                          'status'           => match (\$statusVal) {
                              1 => 'online',
                              default => 'offline'
                          },
PHP;

$replace = <<<PHP
                          'status'           => (function() use (\$statusVal, \$onuIndex) {
                              // Di HSGQ onuPhaseState: 1=logging/offline, 2=los, 3=sync, 4=online/working
                              \$isOnline = match (\$statusVal) {
                                  3, 4, 10 => true,
                                  default  => false
                              };
                              
                              // Fallback final: jika RX Power tidak ada atau <= -40, pasti offline
                              \$rx = \$this->cachedOnuRx[\$onuIndex] ?? null;
                              if (\$rx === null || \$rx <= -40) {
                                  \$isOnline = false;
                              }
                              
                              // Jika status dari SNMP tidak dikenali (0), tapi RX power bagus, anggap online
                              if (\$statusVal === 0 && \$rx !== null && \$rx > -40) {
                                  \$isOnline = true;
                              }

                              return \$isOnline ? 'online' : 'offline';
                          })(),
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed HSGQ status mapping\n";
