<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$search = <<<JS
          let lat = {{ (float)(\$customer->latitude ?: '-6.2088') }};
          let lng = {{ (float)(\$customer->longitude ?: '106.8456') }};
JS;

$replace = <<<JS
          let lat = {{ json_encode((float)(\$customer->latitude ?: '-6.2088')) }};
          let lng = {{ json_encode((float)(\$customer->longitude ?: '106.8456')) }};
JS;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Used json_encode for JS coordinates to prevent locale comma issues.";
?>
