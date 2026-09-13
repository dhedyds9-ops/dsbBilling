<?php
$file = 'D:/dsBilling/resources/views/livewire/crm/customer/customer360.blade.php';
$content = file_get_contents($file);

$search = <<<HTML
              </div>
              @else
              <div class="h-48 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center">
                  <div class="text-center text-slate-400">
                      <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">location_off</span>
                      <p class="mt-2 text-sm font-semibold text-slate-500">Koordinat lokasi belum diisi</p>
                      <p class="text-xs mt-1">Silahkan edit profil dan isi data latitude/longitude</p>
                  </div>
              </div>
              @endif
          </x-base.card>
HTML;

$replace = <<<HTML
              </div>
          </x-base.card>
HTML;

$content = str_replace($search, $replace, $content);

$search2 = <<<HTML
          <x-base.card>
              <x-slot name="header">
                  <div class="flex justify-between items-center w-full">
                      <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                          <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">map</span>
                          Lokasi Pelanggan
                      </h3>
                  </div>
              </x-slot>
              
              @if(\$customer->latitude && \$customer->longitude)
              <div class="space-y-4">
HTML;

$replace2 = <<<HTML
          <x-base.card>
              <x-slot name="header">
                  <div class="flex justify-between items-center w-full">
                      <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                          <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">map</span>
                          Lokasi Pelanggan
                      </h3>
                  </div>
              </x-slot>
              
              <div class="space-y-4">
                  @if(!(\$customer->latitude && \$customer->longitude))
                  <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2 text-amber-700 text-sm">
                      <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">warning</span>
                      <span>Koordinat lokasi belum diisi. Menampilkan lokasi default. Silahkan edit profil.</span>
                  </div>
                  @endif
HTML;

$content = str_replace($search2, $replace2, $content);
file_put_contents($file, $content);
echo "Always show map, but with a warning if coordinates are missing.";
?>
