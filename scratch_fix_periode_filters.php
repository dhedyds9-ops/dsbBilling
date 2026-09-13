<?php
$file = 'app/Services/Billing/PeriodeTagihanService.php';
$content = file_get_contents($file);

$content = str_replace(
    "if (!empty(\$filters['status'])) {",
    "if (!empty(\$filters['status']) && \$filters['status'] !== 'all') {",
    $content
);

$content = str_replace(
    "if (!empty(\$filters['reseller_id'])) {",
    "if (!empty(\$filters['reseller_id']) && \$filters['reseller_id'] !== 'all') {",
    $content
);

file_put_contents($file, $content);
echo "Fixed filters in PeriodeTagihanService\n";
