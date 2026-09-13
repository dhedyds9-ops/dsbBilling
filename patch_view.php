<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/dashboard.blade.php';
$content = file_get_contents($file);

$search = <<<'HTML'
                    <div class="flex gap-3 mt-1 text-xs font-medium">
                        <span class="text-slate-500">PPPoE: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['pppoe_user'] ?? 0 }}</span></span>
                        <span class="text-slate-500">Hotspot: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['hotspot_user'] ?? 0 }}</span></span>
                    </div>
HTML;

$replace = <<<'HTML'
                    <div class="flex flex-col gap-1 mt-1 text-xs font-medium">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">PPPoE: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['pppoe_user'] ?? 0 }}</span></span>
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 text-[9px] font-bold">{{ $mixData['ppp_online'] ?? 0 }} Online</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">Hotspot: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['hotspot_user'] ?? 0 }}</span></span>
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 text-[9px] font-bold">{{ $mixData['hotspot_online'] ?? 0 }} Online</span>
                        </div>
                    </div>
HTML;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added online indicators to dashboard view.\n";
?>
