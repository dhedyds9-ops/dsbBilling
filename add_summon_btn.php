<?php
$file = 'resources/views/livewire/acs/device/index.blade.php';
$content = file_get_contents($file);

$search = <<<HTML
                                      <a href="{{ route('acs.devices.show', \$device->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors" title="Detail">
                                          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
                                      </a>
HTML;

$replace = <<<HTML
                                      <button wire:click="summon('{{ \$device->uuid }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-600 dark:text-amber-400 transition-colors" title="Summon (Refresh Data)">
                                          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">sensors</span>
                                      </button>
                                      <a href="{{ route('acs.devices.show', \$device->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors" title="Detail">
                                          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
                                      </a>
HTML;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added summon button to index\n";
