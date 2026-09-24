<?php
$file = 'resources/views/livewire/acs/device/index.blade.php';
$content = file_get_contents($file);

$search1 = <<<'HTML'
                                @if($device->signal)
                                    @php
                                        $sigVal = (float) $device->signal;
                                        $sigColor = "text-slate-500 dark:text-slate-400";
                                        if ($sigVal < -27) $sigColor = "text-rose-600 dark:text-rose-400 font-bold";
                                        elseif ($sigVal < -24) $sigColor = "text-amber-600 dark:text-amber-400 font-bold";
                                        elseif ($sigVal <= -8 && $sigVal != 0) $sigColor = "text-emerald-600 dark:text-emerald-400 font-bold";
                                    @endphp
                                    <span class="{{ $sigColor }}">{{ $device->signal }} dBm</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
HTML;

$replace1 = <<<'HTML'
                                @if($device->signal)
                                    @php
                                        $sigVal = (float) $device->signal;
                                        $sigBg = "bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300";
                                        if ($sigVal < -26) {
                                            $sigBg = "bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800"; // Buruk
                                        } elseif ($sigVal < -23) {
                                            $sigBg = "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800"; // Sedang
                                        } elseif ($sigVal <= -8) {
                                            $sigBg = "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"; // Bagus/Baik
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sigBg }}">
                                        {{ $device->signal }} dBm
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
HTML;

$content = str_replace($search1, $replace1, $content);
file_put_contents($file, $content);
echo "index.blade.php optical power updated!\n";
