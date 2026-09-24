<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

$search = "                  \$parameterValues[] = ['name' => \$path, 'value' => \$value, 'type' => \$type];";
$replace = "                  \$parameterValues[] = [\$path, \$value, \$type];";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated: fixed payload format to array of arrays.\n";
