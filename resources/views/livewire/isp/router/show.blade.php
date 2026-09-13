@section('page_title', 'Detail MikroTik (Nas)')

<div class="space-y-6">
    

    <!-- TopBar / Header Section -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.routers.index') }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Kembali">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $router->name }}</h1>
                    <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full {{ $router->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400' }}">
                        {{ $router->status === 'active' ? 'Beroperasi' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="flex items-center gap-4 mt-2 text-sm text-slate-500 dark:text-slate-400 font-mono">
                    <span class="flex items-center gap-1">
                        <span class="text-slate-400">#</span> {{ str_pad($router->id, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">lan</span> 
                        {{ $router->ip_address }} : {{ $router->api_port }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="refreshData" wire:loading.attr="disabled" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white hover:bg-slate-900 dark:hover:bg-slate-600 rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]" wire:loading.class="animate-spin" translate="no">refresh</span>
                Segarkan Data
            </button>
            <a href="{{ route('isp.routers.edit', $router->id) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">edit</span>
                Edit
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-700">
        <nav class="-mb-px flex gap-8 overflow-x-auto hide-scrollbar" aria-label="Tabs">
            @foreach([
                'overview' => ['label' => 'Ringkasan', 'icon' => 'dashboard'],
                'interfaces' => ['label' => 'Interface', 'icon' => 'settings_ethernet'],
                'ppp' => ['label' => 'PPPoE (Server, Profil, Aktif)', 'icon' => 'dialpad'],
                'hotspot' => ['label' => 'Hotspot (Server, Profil, Aktif)', 'icon' => 'wifi'],
                'logs' => ['label' => 'Sistem Log', 'icon' => 'list_alt'],
                'terminal' => ['label' => 'Web Terminal', 'icon' => 'terminal']
            ] as $tabKey => $tabData)
                <button type="button" wire:click="setActiveTab('{{ $tabKey }}')"
                        class="py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 whitespace-nowrap {{ $activeTab === $tabKey ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 hover:border-slate-300 dark:border-slate-600 dark:hover:border-slate-600' }}">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">{{ $tabData['icon'] }}</span>
                    {{ $tabData['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Tab Contents -->
    <div class="mt-6">
        @if($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-center">
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">fingerprint</span> Identity
                    </p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $systemInfo['identity'] ?? '-' }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-center">
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">verified</span> Version
                    </p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $systemInfo['version'] ?? '-' }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-center">
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">memory</span> CPU Load
                    </p>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl font-bold {{ ($systemInfo['cpu_load'] ?? 0) > 80 ? 'text-red-500' : 'text-slate-900 dark:text-white' }}">{{ $systemInfo['cpu_load'] ?? 0 }}%</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-center">
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">schedule</span> Uptime
                    </p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $systemInfo['uptime'] ?? '-' }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- System Hardware Info -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined notranslate text-blue-500 text-[20px]" translate="no">developer_board</span>
                        Model Board
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-slate-500 dark:text-slate-400">Board Name</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $systemInfo['board_name'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-slate-500 dark:text-slate-400">Architecture</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $systemInfo['architecture_name'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-slate-500 dark:text-slate-400">CPU</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $systemInfo['cpu'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Storage & Memory -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined notranslate text-emerald-500 text-[20px]" translate="no">storage</span>
                        Memory (RAM)
                    </h3>
                    @php
                        $totalMem = ($systemInfo['total_memory'] ?? 0) / 1048576;
                        $freeMem = ($systemInfo['free_memory'] ?? 0) / 1048576;
                        $usedMem = $totalMem - $freeMem;
                        $memPercent = $totalMem > 0 ? ($usedMem / $totalMem) * 100 : 0;
                    @endphp
                    <div class="mb-2 flex justify-between items-end">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($usedMem, 2) }} MB</span>
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">/ {{ number_format($totalMem, 2) }} MB</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5 mb-6">
                        <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $memPercent }}%"></div>
                    </div>

                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-6 mt-8">
                        <span class="material-symbols-outlined notranslate text-indigo-500 text-[20px]" translate="no">hard_drive</span>
                        HDD / Storage Space
                    </h3>
                    @php
                        $totalHdd = ($systemInfo['total_hdd_space'] ?? 0) / 1048576;
                        $freeHdd = ($systemInfo['free_hdd_space'] ?? 0) / 1048576;
                        $usedHdd = $totalHdd - $freeHdd;
                        $hddPercent = $totalHdd > 0 ? ($usedHdd / $totalHdd) * 100 : 0;
                    @endphp
                    <div class="mb-2 flex justify-between items-end">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($usedHdd, 2) }} MB</span>
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">/ {{ number_format($totalHdd, 2) }} MB</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5">
                        <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ $hddPercent }}%"></div>
                    </div>
                </div>
            </div>

        @elseif($activeTab === 'interfaces')
            <div wire:poll.2s="loadData" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto relative">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">TX Speed</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">RX Speed</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($interfaces as $interface)
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">settings_ethernet</span>
                                            </div>
                                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $interface['name'] ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $interface['type'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(($interface['running'] ?? 'false') === 'true' || ($interface['status'] ?? '') === 'link-up')
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                                Connected
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                Disconnected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 dark:text-blue-400 font-mono">
                                        <span class="material-symbols-outlined notranslate text-[14px] align-middle mr-1" translate="no">arrow_upward</span>{{ number_format(($interface['tx-bps'] ?? 0) / 1000000, 2) }} Mbps
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                        <span class="material-symbols-outlined notranslate text-[14px] align-middle mr-1" translate="no">arrow_downward</span>{{ number_format(($interface['rx-bps'] ?? 0) / 1000000, 2) }} Mbps
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Tidak ada interface.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($activeTab === 'ppp')
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
                                @forelse($pppServers ?? [] as $server)
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ $server['service-name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $server['interface'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $server['default-profile'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-6 text-center text-slate-500 dark:text-slate-400">Tidak ada PPPoE Server.</td></tr>
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
                                @forelse($pppProfiles ?? [] as $profile)
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ $profile['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $profile['local-address'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $profile['remote-address'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $profile['rate-limit'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-6 text-center text-slate-500 dark:text-slate-400">Tidak ada PPPoE Profile.</td></tr>
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
                                @forelse($pppActive as $session)
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-500">
                                                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">person</span>
                                                </div>
                                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $session['name'] ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $session['address'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $session['caller-id'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $session['uptime'] ?? '-' }}</td>
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

        @elseif($activeTab === 'hotspot')
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
                                @forelse($hotspotServers ?? [] as $server)
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ $server['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $server['interface'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $server['profile'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-6 text-center text-slate-500 dark:text-slate-400">Tidak ada Hotspot Server.</td></tr>
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
                                @forelse($hotspotProfiles ?? [] as $profile)
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                        <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-bold">{{ $profile['name'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $profile['shared-users'] ?? '-' }}</td>
                                        <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $profile['rate-limit'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-6 text-center text-slate-500 dark:text-slate-400">Tidak ada Hotspot Profile.</td></tr>
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
                                @forelse($hotspotActive as $session)
                                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-500">
                                                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">smartphone</span>
                                                </div>
                                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $session['user'] ?? $session['name'] ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $session['address'] ?? $session['ip'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $session['mac-address'] ?? $session['mac'] ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $session['uptime'] ?? '-' }}</td>
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

        @elseif($activeTab === 'logs')
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-slate-500 dark:text-slate-400 text-[20px]" translate="no">list_alt</span>
                        Sistem Log (100 Terakhir)
                    </h3>
                </div>
                <div class="overflow-x-auto relative">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-48">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Topik</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pesan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800 font-mono text-[13px]">
                            @forelse($logs ?? [] as $log)
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                    <td class="px-6 py-3 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $log['time'] ?? '-' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs font-semibold rounded bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            {{ $log['topics'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-slate-700 dark:text-slate-300">{{ $log['message'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">Tidak ada log tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($activeTab === 'terminal')
            <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-700 overflow-hidden flex flex-col" style="height: 600px;">
                <div class="px-4 py-3 border-b border-slate-800 bg-black/40 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                        </div>
                        <span class="text-xs font-mono text-slate-400 font-semibold tracking-wider">admin@{{ $router->ip_address }}</span>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 font-mono text-[13px] text-slate-300 leading-relaxed bg-[#0a0f18] custom-scrollbar" id="terminal-output">
                    <div class="mb-4 text-emerald-400/90 font-semibold">
                        # RouterOS Web Terminal<br>
                        # Terkoneksi via API ke {{ $router->name }}
                    </div>
                    
                    @foreach($terminalOutput as $output)
                        @if($output['type'] === 'input')
                            <div class="flex items-start gap-2 mb-1">
                                <span class="text-blue-400 select-none">] ></span>
                                <span class="break-all text-white font-semibold">{{ str_replace('> ', '', $output['text']) }}</span>
                            </div>
                        @elseif($output['type'] === 'error')
                            <div class="mb-3 text-red-400 whitespace-pre-wrap pl-5">{{ $output['text'] }}</div>
                        @else
                            <div class="mb-3 text-slate-300 whitespace-pre-wrap pl-5">{{ $output['text'] }}</div>
                        @endif
                    @endforeach
                    
                    @if($isTerminalRunning)
                        <div class="flex items-start gap-2 mb-1 animate-pulse">
                            <span class="text-blue-400">] ></span>
                            <span class="text-slate-500 dark:text-slate-400">Menjalankan perintah...</span>
                        </div>
                    @endif
                </div>

                <div class="p-3 bg-black/40 border-t border-slate-800">
                    <form wire:submit.prevent="executeTerminalCommand" class="flex items-center gap-2">
                        <span class="text-blue-400 font-mono pl-2 font-bold select-none">] ></span>
                        <input type="text" wire:model="terminalInput" 
                               class="flex-1 bg-transparent border-none text-white font-mono text-[13px] focus:ring-0 px-2 py-1 placeholder-slate-600 dark:bg-slate-900 dark:text-slate-100" 
                               placeholder="Ketik perintah (contoh: /ip/address/print) lalu tekan Enter..."
                               autocomplete="off"
                               autofocus
                               @if($isTerminalRunning) disabled @endif>
                    </form>
                </div>
            </div>
            <script>
                document.addEventListener('livewire:initialized', () => {
                    Livewire.hook('morph.updated', (el, component) => {
                        const term = document.getElementById('terminal-output');
                        if (term) term.scrollTop = term.scrollHeight;
                    });
                });
            </script>
        @endif
    </div>
</div>
