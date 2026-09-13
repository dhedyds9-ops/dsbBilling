<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

$newPppContent = <<<BLADE
        @elseif(\ === 'ppp')
            <div class="space-y-6">
                <!-- PPPoE Servers -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500 text-[20px]" translate="no">dns</span>
                            PPPoE Servers
                        </h3>
                    </div>
                    <div class="overflow-x-auto relative">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Service Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Interface</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Default Profile</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800 font-mono text-[13px]">
                                @forelse(\ ?? [] as \)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \['service-name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \['interface'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \['default-profile'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-6 text-center text-slate-500">Tidak ada PPPoE Server.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PPPoE Profiles -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500 text-[20px]" translate="no">tune</span>
                            PPPoE Profiles
                        </h3>
                    </div>
                    <div class="overflow-x-auto relative">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Local Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Remote Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rate Limit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800 font-mono text-[13px]">
                                @forelse(\ ?? [] as \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1['local-address'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1['remote-address'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1['rate-limit'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-6 text-center text-slate-500">Tidak ada PPPoE Profile.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PPPoE Active -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500 text-[20px]" translate="no">dialpad</span>
                            Koneksi PPPoE Aktif
                        </h3>
                    </div>
BLADE;

$content = preg_replace('/@elseif\(\ === \'pppoe\'\)\s*<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">/', $newPppContent, $content);

$newHotspotContent = <<<BLADE
        @elseif(\ === 'hotspot')
            <div class="space-y-6">
                <!-- Hotspot Servers -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-orange-500 text-[20px]" translate="no">dns</span>
                            Hotspot Servers
                        </h3>
                    </div>
                    <div class="overflow-x-auto relative">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Interface</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Profile</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800 font-mono text-[13px]">
                                @forelse(\ ?? [] as \)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \['interface'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \['profile'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-6 text-center text-slate-500">Tidak ada Hotspot Server.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Hotspot Profiles -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-orange-500 text-[20px]" translate="no">tune</span>
                            Hotspot Profiles
                        </h3>
                    </div>
                    <div class="overflow-x-auto relative">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Shared Users</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rate Limit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800 font-mono text-[13px]">
                                @forelse(\ ?? [] as \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1['shared-users'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \C:\Users\Lenovo\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1['rate-limit'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-6 text-center text-slate-500">Tidak ada Hotspot Profile.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Hotspot Active -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-orange-500 text-[20px]" translate="no">wifi</span>
                            Koneksi Hotspot Aktif
                        </h3>
                    </div>
BLADE;

$content = preg_replace('/@elseif\(\ === \'hotspot\'\)\s*<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">/', $newHotspotContent, $content);

// Close the extra div for space-y-6 for both tabs
$content = str_replace('                    </table>
                </div>
            </div>
        @elseif( === \'logs\')', '                    </table>
                </div>
            </div>
            </div>
        @elseif( === \'logs\')', $content);

$content = str_replace('                    </table>
                </div>
            </div>
        @elseif( === \'hotspot\')', '                    </table>
                </div>
            </div>
            </div>
        @elseif( === \'hotspot\')', $content);

file_put_contents($file, $content);
echo "Blade updated\n";
?>
