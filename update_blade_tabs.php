<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// Extract the monitoring tab content
if (preg_match('/\{\{-- ============ MONITORING TAB ============ --\}\}\s*@if\(\$activeTab === \'monitoring\'\)([\s\S]*?)@endif\s*\{\{-- ============ TICKET TAB ============ --\}\}/', $content, $matches)) {
    $monitoringContent = $matches[1];
    
    // Remove the monitoring tab section completely
    $content = str_replace($matches[0], '{{-- ============ TICKET TAB ============ --}}', $content);
    
    // Insert the monitoring content into the device tab
    // We'll append it just before the closing @endif of the device tab
    $deviceEndMarker = "        </div>\n        @endif\n\n        {{-- ============ INSTALLATION TAB";
    if (strpos($content, $deviceEndMarker) !== false) {
        // Build the new monitoring section
        $newMonitoring = <<<HTML
            {{-- STATUS KONEKSI & ONU MONITORING --}}
            <div class="mt-6">
$monitoringContent
            </div>
        </div>
        @endif

        {{-- ============ INSTALLATION TAB
HTML;
        $content = str_replace($deviceEndMarker, $newMonitoring, $content);
        file_put_contents($file, $content);
        echo "Merged monitoring into device tab.";
    } else {
        echo "Could not find device tab end marker.";
    }
} else {
    echo "Could not find monitoring tab.";
}
?>
