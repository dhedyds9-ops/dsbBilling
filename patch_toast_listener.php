<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php'
];

$script = <<<EOT
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('toast', (data) => {
            // Livewire 3 returns an array of events, so data[0] or data is the payload
            let detail = Array.isArray(data) ? data[0] : data;
            alert(detail.message || "Terdapat pesan dari sistem.");
        });
    });
</script>
EOT;

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    if (strpos($content, 'Livewire.on(\'toast\'') === false) {
        $content .= "\n" . $script;
        file_put_contents($file, $content);
    }
}
echo "Added toast listener to blades.\n";
?>
