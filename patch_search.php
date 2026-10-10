<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

$pppSearchHtml = <<<'EOT'
                      <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                          <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                              <span class="material-symbols-outlined notranslate text-indigo-500 text-[20px]" translate="no">dialpad</span>
                              Koneksi PPPoE Aktif
                          </h3>
                          <div class="relative">
                              <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]" translate="no">search</span>
                              <input type="text" wire:model.live="searchPpp" placeholder="Cari username atau IP..." class="pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 w-64">
                          </div>
                      </div>
                      @php
                           = collect( ?? [])->filter(function() {
                              if (empty()) return true;
                              return stripos(['name'] ?? '', ) !== false || stripos(['address'] ?? '', ) !== false;
                          })->all();
                      @endphp
EOT;

$hotspotSearchHtml = <<<'EOT'
                      <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                          <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                              <span class="material-symbols-outlined notranslate text-indigo-500 text-[20px]" translate="no">wifi</span>
                              Koneksi Hotspot Aktif
                          </h3>
                          <div class="relative">
                              <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]" translate="no">search</span>
                              <input type="text" wire:model.live="searchHotspot" placeholder="Cari username atau IP..." class="pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 w-64">
                          </div>
                      </div>
                      @php
                           = collect( ?? [])->filter(function() {
                              if (empty()) return true;
                               = ;
                              return stripos(['user'] ?? '', ) !== false || stripos(['address'] ?? '', ) !== false || stripos(['mac-address'] ?? '', ) !== false;
                          })->all();
                      @endphp
EOT;

// Replace PPP
$content = preg_replace('/<div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50\/50 dark:bg-slate-800\/50 flex justify-between items-center">\s*<h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">\s*<span class="material-symbols-outlined notranslate text-indigo-500 text-\[20px\]" translate="no">dialpad<\/span>\s*Koneksi PPPoE Aktif\s*<\/h3>\s*<\/div>/', $pppSearchHtml, $content);
$content = str_replace('@forelse( as )', '@forelse( as )', $content);

// Replace Hotspot
$content = preg_replace('/<div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50\/50 dark:bg-slate-800\/50 flex justify-between items-center">\s*<h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">\s*<span class="material-symbols-outlined notranslate text-indigo-500 text-\[20px\]" translate="no">wifi<\/span>\s*Koneksi Hotspot Aktif\s*<\/h3>\s*<\/div>/', $hotspotSearchHtml, $content);
$content = str_replace('@forelse( as )', '@forelse( as )', $content);

file_put_contents($file, $content);
