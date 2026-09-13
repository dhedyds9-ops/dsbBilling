<?php
$file = 'resources/views/livewire/isp/router/show.blade.php';
$content = file_get_contents($file);

// 1. Change TX/RX Bytes to Speed in Mbps
$content = str_replace(
    '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">TX Bytes</th>',
    '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">TX Speed</th>',
    $content
);
$content = str_replace(
    '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">RX Bytes</th>',
    '<th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">RX Speed</th>',
    $content
);
$content = preg_replace(
    '/\{\{\s*number_format\(\(\$interface\[\'tx-byte\'\]\s*\?\?\s*0\)\s*\/\s*1048576,\s*2\)\s*\}\}\s*MB/',
    '<span class="material-symbols-outlined notranslate text-[14px] align-middle mr-1" translate="no">arrow_upward</span>{{ number_format(($interface[\'tx-bps\'] ?? 0) / 1000000, 2) }} Mbps',
    $content
);
$content = preg_replace(
    '/\{\{\s*number_format\(\(\$interface\[\'rx-byte\'\]\s*\?\?\s*0\)\s*\/\s*1048576,\s*2\)\s*\}\}\s*MB/',
    '<span class="material-symbols-outlined notranslate text-[14px] align-middle mr-1" translate="no">arrow_downward</span>{{ number_format(($interface[\'rx-bps\'] ?? 0) / 1000000, 2) }} Mbps',
    $content
);

// 2. Add wire:poll.2s="loadData" to interfaces
$content = str_replace(
    "@elseif(\$activeTab === 'interfaces')\n            <div class=\"bg-white",
    "@elseif(\$activeTab === 'interfaces')\n            <div wire:poll.2s=\"loadData\" class=\"bg-white",
    $content
);

// 3. Fix labels
$content = str_replace(
    "'ppp' => ['label' => 'PPPoE Aktif', 'icon' => 'dialpad']",
    "'ppp' => ['label' => 'PPPoE (Server, Profil, Aktif)', 'icon' => 'dialpad']",
    $content
);
$content = str_replace(
    "'hotspot' => ['label' => 'Hotspot Aktif', 'icon' => 'wifi']",
    "'hotspot' => ['label' => 'Hotspot (Server, Profil, Aktif)', 'icon' => 'wifi']",
    $content
);

// 4. Inject sub-tables for PPPoE and Hotspot
$posPppoe = strpos($content, "@elseif(\$activeTab === 'pppoe')");
$beforePppoe = substr($content, 0, $posPppoe);

$posLogs = strpos($content, "@elseif(\$activeTab === 'logs')");
$afterLogs = substr($content, $posLogs);

$newPppAndHotspot = <<<BLADE
        @elseif(\$activeTab === 'ppp')
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
                                @forelse(\$pppServers ?? [] as \$server)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \$server['service-name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$server['interface'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$server['default-profile'] ?? '-' }}</td>
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
                                @forelse(\$pppProfiles ?? [] as \$profile)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \$profile['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$profile['local-address'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$profile['remote-address'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$profile['rate-limit'] ?? '-' }}</td>
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
                    <div class="overflow-x-auto relative">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">MAC Address</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse(\$pppActive as \$session)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-500">
                                                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">person</span>
                                                </div>
                                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ \$session['name'] ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \$session['address'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \$session['caller-id'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ \$session['uptime'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 mb-3">
                                                <span class="material-symbols-outlined notranslate text-slate-400" translate="no">dialpad</span>
                                            </div>
                                            <p>Tidak ada koneksi PPPoE aktif.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif(\$activeTab === 'hotspot')
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
                                @forelse(\$hotspotServers ?? [] as \$server)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \$server['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$server['interface'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$server['profile'] ?? '-' }}</td>
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
                                @forelse(\$hotspotProfiles ?? [] as \$profile)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ \$profile['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$profile['shared-users'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \$profile['rate-limit'] ?? '-' }}</td>
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
                    <div class="overflow-x-auto relative">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">MAC Address</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse(\$hotspotActive as \$session)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-500">
                                                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">smartphone</span>
                                                </div>
                                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ \$session['user'] ?? \$session['name'] ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \$session['address'] ?? \$session['ip'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ \$session['mac-address'] ?? \$session['mac'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ \$session['uptime'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 mb-3">
                                                <span class="material-symbols-outlined notranslate text-slate-400" translate="no">wifi</span>
                                            </div>
                                            <p>Tidak ada koneksi Hotspot aktif.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
BLADE;

$content = $beforePppoe . "\n" . $newPppAndHotspot . "\n" . $afterLogs;
file_put_contents($file, $content);
echo "Successfully fixed all blade issues\n";
?>
