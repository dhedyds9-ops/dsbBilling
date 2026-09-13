<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/dashboard.blade.php';
$content = file_get_contents($file);

$searchStr = "<!-- 6. Histori Koneksi -->";
$insertStr = <<<HTML
            <!-- 7. Speedtest -->
            <a href="{{ route('customer-portal.self-service.speed-test') }}" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">speed</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Speedtest</span>
            </a>

HTML;

$content = str_replace($searchStr, $insertStr . "            " . $searchStr, $content);
file_put_contents($file, $content);
echo "Added Speedtest to Grid Menu.\n";
?>
