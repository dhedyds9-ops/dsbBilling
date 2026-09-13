<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$startPos = strpos($content, '{{-- ============ MONITORING TAB ============ --}}');
$endPos = strpos($content, '{{-- ============ HISTORY TAB ============ --}}');

if ($startPos !== false && $endPos !== false) {
    $monitoringStr = substr($content, $startPos, $endPos - $startPos);
    
    // Convert `@if($activeTab === 'monitoring')` to be unconditionally shown (or we can just remove the if entirely)
    $cleanMonitoring = preg_replace('/@if\(\$activeTab === \'monitoring\'\)/', '', $monitoringStr);
    // Remove the matching @endif. The last @endif in this block before HISTORY TAB.
    $cleanMonitoring = preg_replace('/@endif\s*$/', '', rtrim($cleanMonitoring));
    
    // Remove monitoring from original position
    $content = str_replace($monitoringStr, '', $content);
    
    // Insert into device tab
    $deviceEndMarker = "        @endif\n\n        {{-- ============ INSTALLATION TAB";
    
    $replacement = <<<HTML
            <div class="mt-8">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">monitor_heart</span>
                    Status Koneksi
                </h3>
$cleanMonitoring
            </div>
        </div>
        @endif

        {{-- ============ INSTALLATION TAB
HTML;

    $content = str_replace($deviceEndMarker, $replacement, $content);
    file_put_contents($file, $content);
    echo "Successfully moved monitoring tab to device tab.";
} else {
    echo "Could not find start or end positions.";
}
?>
