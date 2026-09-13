<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

// 1. Rewrite the Tab Navigation
$navPattern = '/<nav class="flex gap-1 whitespace-nowrap" aria-label="Tabs">[\s\S]*?<\/nav>/';
$newNav = <<<HTML
<nav class="flex gap-1 whitespace-nowrap" aria-label="Tabs">
            @foreach([
                ['tab' => 'profile',       'label' => 'Profil & Lokasi',       'icon' => 'person'],
                ['tab' => 'finance',       'label' => 'Keuangan & Tagihan',    'icon' => 'payments'],
                ['tab' => 'device',        'label' => 'Perangkat & Jaringan',  'icon' => 'router'],
                ['tab' => 'support',       'label' => 'Support & Teknis',      'icon' => 'engineering'],
                ['tab' => 'history',       'label' => 'Riwayat & Log',         'icon' => 'history'],
            ] as \$t)
            <button wire:click="setActiveTab('{{ \$t['tab'] }}')"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                        @if(\$activeTab === \$t['tab']) border-indigo-500 text-indigo-600 dark:text-indigo-400
                        @else border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300
                        @endif">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">{{ \$t['icon'] }}</span>
                {{ \$t['label'] }}
            </button>
            @endforeach
        </nav>
HTML;
$content = preg_replace($navPattern, $newNav, $content);

// Function to extract inner content of a tab (without the @if($activeTab...) and @endif)
function extractTabInnerContent($content, $tabName) {
    // Regex to match the block: @if($activeTab === 'tabName') ... @endif
    // We look for the comment, then the @if, up to the @endif that precedes the next comment or Edit Modal
    $pattern = '/\{\{-- ============ ' . strtoupper($tabName) . ' TAB ============ --\}\}\s*@if\(\$activeTab === \'' . $tabName . '\'\)([\s\S]*?)\s*@endif\s*(?=\{\{-- ============ |\{\{-- EDIT MODAL)/';
    if (preg_match($pattern, $content, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

// Extract components
$profileContent = extractTabInnerContent($content, 'profile');
$contractContent = extractTabInnerContent($content, 'contract');
$gisContent = extractTabInnerContent($content, 'gis');

$billingContent = extractTabInnerContent($content, 'billing');
$invoiceContent = extractTabInnerContent($content, 'invoice');
$paymentContent = extractTabInnerContent($content, 'payment');

$deviceContent = extractTabInnerContent($content, 'device');

$ticketContent = extractTabInnerContent($content, 'ticket');
$installationContent = extractTabInnerContent($content, 'installation');

$historyContent = extractTabInnerContent($content, 'history');
$activityContent = extractTabInnerContent($content, 'activity');
$notificationContent = extractTabInnerContent($content, 'notification');

// Build New Tabs
$newProfileTab = <<<HTML
        {{-- ============ PROFILE & LOKASI TAB ============ --}}
        @if(\$activeTab === 'profile')
        <div class="space-y-6">
            {{-- Profile Content (Top) --}}
            $profileContent

            {{-- Contract & GIS Side by Side --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-6">
                    $contractContent
                </div>
                <div class="space-y-6">
                    $gisContent
                </div>
            </div>
        </div>
        @endif
HTML;

$newFinanceTab = <<<HTML
        {{-- ============ KEUANGAN & TAGIHAN TAB ============ --}}
        @if(\$activeTab === 'finance')
        <div class="space-y-6">
            {{-- Billing Info (Top) --}}
            $billingContent

            {{-- Invoice & Payment Side by Side (or stacked if they are wide) --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div>
                    $invoiceContent
                </div>
                <div>
                    $paymentContent
                </div>
            </div>
        </div>
        @endif
HTML;

// Note: deviceContent already has the @if inside it based on how we extracted it? No, extractTabInnerContent strips the @if.
// Wait, my previous script left {{-- ============ MONITORING TAB ============ --}} inside the device block but not as a separate @if.
// So deviceContent includes the merged monitoring stuff!
$newDeviceTab = <<<HTML
        {{-- ============ PERANGKAT & JARINGAN TAB ============ --}}
        @if(\$activeTab === 'device')
            $deviceContent
        @endif
HTML;

$newSupportTab = <<<HTML
        {{-- ============ SUPPORT & TEKNIS TAB ============ --}}
        @if(\$activeTab === 'support')
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="space-y-6">
                $ticketContent
            </div>
            <div class="space-y-6">
                $installationContent
            </div>
        </div>
        @endif
HTML;

$newHistoryTab = <<<HTML
        {{-- ============ RIWAYAT & LOG TAB ============ --}}
        @if(\$activeTab === 'history')
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                $historyContent
            </div>
            <div class="space-y-6">
                $activityContent
                
                $notificationContent
            </div>
        </div>
        @endif
HTML;

$newTabsContainer = <<<HTML
$newProfileTab

$newFinanceTab

$newDeviceTab

$newSupportTab

$newHistoryTab
HTML;

// Replace everything between {{-- Tab Content --}} <div class="mt-2"> and {{-- EDIT MODAL --}}
$content = preg_replace('/\{\{-- ============ PROFILE TAB ============ --\}\}[\s\S]*?(?=\{\{-- EDIT MODAL --\}\})/', $newTabsContainer . "\n\n    </div>\n\n    ", $content);

file_put_contents($file, $content);
echo "Successfully refactored Blade file tabs.";
?>
