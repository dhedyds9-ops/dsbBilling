<?php
echo '<pre>';
$cmd = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.100.2 .1.3.6.1.4.1.34592.1.3.100.9.2.1.13 2>&1';
echo "Test CData ONUs:\n";
echo "Command: $cmd\n";
echo shell_exec($cmd) . "\n\n";

$cmd2 = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.99.1 .1.3.6.1.4.1.50224.3.2.1.1.2 2>&1';
echo "Test HSGQ PON Ports:\n";
echo "Command: $cmd2\n";
echo shell_exec($cmd2) . "\n\n";

$cmd3 = 'snmpwalk -O q -v 2c -c MstoreRead2026 192.168.100.2 .1.3.6.1.2.1.1.3.0 2>&1';
echo "Test -O q:\n";
echo shell_exec($cmd3) . "\n\n";

$cmd4 = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.100.2 .1.3.6.1.2.1.1.3.0 2>&1';
echo "Test -O n:\n";
echo shell_exec($cmd4) . "\n\n";

echo '</pre>';
