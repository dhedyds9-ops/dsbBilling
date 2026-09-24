<?php
echo '<pre>';
$cmd = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.100.2 .1.3.6.1.4.1.34592.1.3.100.9.2.1.13 2>&1';
echo "Test CData ONUs:\n";
echo shell_exec($cmd) . "\n\n";

$cmd2 = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.80.3 .1.3.6.1.4.1.50224.3.2.1.1.2 2>&1';
echo "Test HSGQ PON Ports Name:\n";
echo shell_exec($cmd2) . "\n\n";

$cmd3 = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.80.3 .1.3.6.1.4.1.50224.3.2.1.1.6 2>&1';
echo "Test HSGQ PON Ports Status:\n";
echo shell_exec($cmd3) . "\n\n";

$cmd4 = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.80.3 .1.3.6.1.4.1.50224.3.12.2.1.15 2>&1';
echo "Test HSGQ ONU Serials:\n";
echo shell_exec($cmd4) . "\n\n";

$cmd5 = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.80.3 .1.3.6.1.4.1.50224.3.12.2.1.22 2>&1';
echo "Test HSGQ ONU Status:\n";
echo shell_exec($cmd5) . "\n\n";

$cmd6 = 'snmpwalk -O n -v 2c -c MstoreRead2026 192.168.100.2 .1.3.6.1.4.1.34592.1.3.100.9.2.1.1 2>&1';
echo "Test CData RX Power? (No, that's not RX). We need to see if CData old branch RX OIDs work.\n";

echo '</pre>';