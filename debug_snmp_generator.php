<?php
file_put_contents('public/debug_snmp.php', "<?php
echo '<pre>';
echo 'PHP Version: ' . phpversion() . \"\n\";
echo 'snmpget function exists: ' . (function_exists('snmpget') ? 'Yes' : 'No') . \"\n\";
echo 'shell_exec function exists: ' . (function_exists('shell_exec') ? 'Yes' : 'No') . \"\n\";

\$cmd = 'snmpget -O qv -v 2c -c MstoreRead2026 -t 5 -r 2 192.168.100.2 .1.3.6.1.2.1.1.3.0 2>&1';
echo 'Testing CLI fallback: ' . \$cmd . \"\n\";
\$output = shell_exec(\$cmd);
echo 'Output: ';
var_dump(\$output);

\$cmd2 = 'snmpwalk -v 2c -c MstoreRead2026 192.168.100.2 .1.3.6.1.2.1.1.3.0 2>&1';
echo 'Testing snmpwalk: ' . \$cmd2 . \"\n\";
\$output2 = shell_exec(\$cmd2);
echo 'Output2: ';
var_dump(\$output2);

echo '</pre>';
");
