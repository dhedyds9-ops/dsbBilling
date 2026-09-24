<?php
$file = 'resources/views/livewire/acs/device/show.blade.php';
$content = file_get_contents($file);

$search = <<<'HTML'
                        <select wire:model.live="wlanTarget" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="1">WLAN 1 (Utama - 2.4GHz)</option>
                            <option value="5">WLAN 5 (Utama - 5GHz)</option>
                        </select>
HTML;

$replace = <<<'HTML'
                        <select wire:model.live="wlanTarget" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            @if(!empty($availableWlans))
                                @foreach($availableWlans as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            @else
                                <option value="1">WLAN 1 (Utama - 2.4GHz)</option>
                                <option value="5">WLAN 5 (Utama - 5GHz)</option>
                            @endif
                        </select>
HTML;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Show.blade.php updated with dynamic WLAN loop.\n";
