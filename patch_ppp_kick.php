<?php

$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

// Add Kick button to PPPoE
$content = str_replace(
    '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th>',
    '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th>' . "\n" . '                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Aksi</th>',
    $content
);

$content = str_replace(
    '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $session[''uptime''] ?? ''-'' }}</td>',
    '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $session[''uptime''] ?? ''-'' }}</td>' . "\n" . '                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">' . "\n" . '                                            <button wire:click="disconnectPpp(\'{{ $session[\'name\'] }}\')" wire:confirm="Yakin ingin kick user ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Kick"><span class="material-symbols-outlined notranslate text-[18px]" translate="no">power_settings_new</span></button>' . "\n" . '                                        </td>',
    $content
);
$content = str_replace(
    '<td colspan="4"',
    '<td colspan="5"',
    $content
);

file_put_contents($file, $content);
