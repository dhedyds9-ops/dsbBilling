<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);
$content = preg_replace("/\\\$parameterValues\[\] = \['name' => \\\$path, 'value' => \\\$value, 'type' => \\\$type\];/", "\$parameterValues[] = [\$path, \$value, \$type];", $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated: fixed payload format to array of arrays.\n";
