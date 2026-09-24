<?php
$file = 'resources/views/livewire/acs/device/index.blade.php';
$content = file_get_contents($file);

$search = '/<button wire:click="summon\(\'\{\{ \$device->uuid \}\}\'\)" class="([^"]+)" title="Summon \(Refresh Data\)">\s*<span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">sensors<\/span>\s*<\/button>/is';

$replace = <<<HTML
<button wire:click="summon('{{ \$device->uuid }}')" wire:loading.attr="disabled" class="$1" title="Summon (Refresh Data)">
                                          <span wire:loading.remove wire:target="summon('{{ \$device->uuid }}')" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">sensors</span>
                                          <span wire:loading wire:target="summon('{{ \$device->uuid }}')" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">hourglass_empty</span>
                                      </button>
HTML;

$content = preg_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added loading state to summon button\n";
