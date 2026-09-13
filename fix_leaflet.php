<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Replace empty coordinate handling
$search = <<<JS
          let lat = {{ \$customer->latitude ?? 0 }};
          let lng = {{ \$customer->longitude ?? 0 }};
JS;

$replace = <<<JS
          let lat = {{ (float)(\$customer->latitude ?: '-6.2088') }};
          let lng = {{ (float)(\$customer->longitude ?: '106.8456') }};
JS;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed Leaflet coordinates syntax error.";
?>
