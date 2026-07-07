<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    <div class="flex items-center gap-4">
        <a href="{{ route('isp.routers.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-slate-900">{{ $router->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $router->code }} • {{ $router->ip_address }}</p>
        </div>
        <button type="button" wire:click="refreshData" class="inline-flex items-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 focus:outline-none">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex gap-8" aria-label="Tabs">
            @foreach(['overview' => 'Overview', 'interfaces' => 'Interfaces', 'pppoe' => 'PPPoE Active', 'hotspot' => 'Hotspot Active'] as $tabKey => $tabLabel)
                <button type="button" wire:click="setActiveTab('{{ $tabKey }}')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === $tabKey ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    {{ $tabLabel }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="space-y-4">
        @if($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-500">Identity</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $systemInfo['identity'] ?? '-' }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-500">Version</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $systemInfo['version'] ?? '-' }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-500">CPU Load</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $systemInfo['cpu_load'] ?? 0 }}%</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-500">Uptime</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $systemInfo['uptime'] ?? '-' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-500">CPU</p>
                    <p class="mt-1 text-lg text-slate-900">{{ $systemInfo['cpu'] ?? '-' }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <p class="text-sm font-medium text-slate-500">Memory</p>
                    <p class="mt-1 text-lg text-slate-900">
                        {{ number_format(($systemInfo['free_memory'] ?? 0) / 1024 / 1024, 2) }} MB / {{ number_format(($systemInfo['total_memory'] ?? 0) / 1024 / 1024, 2) }} MB
                    </p>
                </div>
            </div>
        @elseif($activeTab === 'interfaces')
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">TX Bytes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">RX Bytes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($interfaces as $interface)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $interface['name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $interface['type'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($interface['status'] ?? '') === 'link-up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $interface['status'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($interface['tx-byte'] ?? 0) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($interface['rx-byte'] ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($activeTab === 'pppoe')
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Uptime</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($pppActive as $session)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $session['name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $session['address'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $session['uptime'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($activeTab === 'hotspot')
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">MAC</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Uptime</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($hotspotActive as $session)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $session['name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $session['ip'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $session['mac'] ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $session['uptime'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
