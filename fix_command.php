<?php
$file = 'app/Console/Commands/ISP/ReapDeadRadiusSessionsCommand.php';
$content = file_get_contents($file);

$content = str_replace("TIMESTAMPDIFF(SECOND, created_at, NOW())", "TIMESTAMPDIFF(SECOND, received_at, NOW())", $content);
$content = str_replace("'created_at'", "'received_at'", $content);

file_put_contents($file, $content);
echo "Fixed ReapDeadRadiusSessionsCommand.php\n";
