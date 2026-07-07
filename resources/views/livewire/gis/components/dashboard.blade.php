<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
        <!-- Total OLT -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total OLT</p>
                    <p class="text-2xl font-bold">{{ $stats['total_olt'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center text-sm">
                <span class="text-green-300">{{ $stats['active_olt'] ?? 0 }} Active</span>
            </div>
        </div>

        <!-- Total ODP -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total ODP</p>
                    <p class="text-2xl font-bold">{{ $stats['total_odp'] ?? 0 }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center text-sm">
                <span class="text-green-300">{{ $stats['active_odp'] ?? 0 }} Active</span>
            </div>
        </div>

        <!-- Total ONU -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm">Total ONU</p>
                    <p class="text-2xl font-bold">{{ $stats['total_onu'] ?? 0 }}</p>
                </div>
                <div class="bg-amber-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center text-sm">
                <span class="text-amber-300">{{ $stats['online_onu'] ?? 0 }} Online</span>
            </div>
        </div>

        <!-- Active Alarms -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Active Alarms</p>
                    <p class="text-2xl font-bold">{{ $stats['active_alarms'] ?? 0 }}</p>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center text-sm">
                <span class="text-red-300">{{ $stats['critical_alarms'] ?? 0 }} Critical</span>
            </div>
        </div>
    </div>

    <!-- Area Selector -->
    <div class="px-4 pb-4">
        <select 
            wire:model.live="selectedArea"
            class="w-full md:w-auto text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        >
            <option value="all">All Areas</option>
            <option value="jakarta-pusat">Jakarta Pusat</option>
            <option value="jakarta-selatan">Jakarta Selatan</option>
            <option value="jakarta-barat">Jakarta Barat</option>
            <option value="jakarta-timur">Jakarta Timur</option>
            <option value="jakarta-utara">Jakarta Utara</option>
        </select>
    </div>

    <!-- Recent Alarms -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-3 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">Recent Alarms</h3>
        </div>
        <div class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
            @forelse($recentAlarms as $alarm)
            <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer" wire:click="$emit('showNodeDetails', '{{ $alarm['node_id'] ?? '' }}', '{{ $alarm['node_type'] ?? '' }}')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="status-indicator status-{{ $alarm['severity'] ?? 'warning' }}"></span>
                        <span class="font-medium text-gray-900">{{ $alarm['title'] ?? 'Unknown Alarm' }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ $alarm['time_ago'] ?? '' }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1 pl-5">{{ $alarm['location'] ?? '' }}</p>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-sm">No active alarms</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Critical Nodes -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-3 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">Critical Nodes</h3>
        </div>
        <div class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
            @forelse($criticalNodes as $node)
            <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer" wire:click="$emit('showNodeDetails', '{{ $node['id'] ?? '' }}', '{{ $node['type'] ?? '' }}')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-1 text-xs font-medium rounded bg-{{ $node['type'] === 'olt' ? 'blue' : 'green' }}-100 text-{{ $node['type'] === 'olt' ? 'blue' : 'green' }}-800">
                            {{ strtoupper($node['type'] ?? '') }}
                        </span>
                        <span class="font-medium text-gray-900">{{ $node['name'] ?? 'Unknown' }}</span>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded status-{{ $node['status'] ?? 'warning' }}">
                        {{ ucfirst($node['status'] ?? 'unknown') }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1 pl-5">{{ $node['location'] ?? '' }} - {{ $node['utilization'] ?? 0 }}% utilization</p>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-gray-500">
                <p class="text-sm">No critical nodes</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
        <div class="flex items-center space-x-2 text-gray-600">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading...</span>
        </div>
    </div>
    @endif
</div>
