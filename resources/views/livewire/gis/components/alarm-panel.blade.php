<div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Alarms</h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/50 text-red-800">
                {{ $alarms->total() }}
            </span>
        </div>
        
        <div class="flex items-center space-x-2">
            <!-- Auto Refresh Toggle -->
            <label class="flex items-center space-x-2 cursor-pointer">
                <input 
                    type="checkbox" 
                    wire:model.live="isAutoRefresh"
                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100"
                >
                <span class="text-xs text-gray-500 dark:text-gray-400">Auto-refresh</span>
            </label>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 space-y-3">
        <!-- Search -->
        <input 
            type="text"
            wire:model.live="searchQuery"
            placeholder="Search alarms..."
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
        >

        <div class="flex flex-wrap gap-2">
            <!-- Severity Filter -->
            <select 
                wire:model.live="filterSeverity"
                class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
                <option value="all">All Severity</option>
                <option value="critical">Critical</option>
                <option value="warning">Warning</option>
                <option value="info">Info</option>
            </select>

            <!-- Status Filter -->
            <select 
                wire:model.live="filterStatus"
                class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="acknowledged">Acknowledged</option>
                <option value="resolved">Resolved</option>
            </select>

            <!-- Time Range -->
            <select 
                wire:model.live="timeRange"
                class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
                <option value="1h">Last 1 Hour</option>
                <option value="6h">Last 6 Hours</option>
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
            </select>
        </div>
    </div>

    <!-- Alarm List -->
    <div class="max-h-96 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($alarms as $alarm)
        <div 
            wire:click="$emit('showNodeDetails', '{{ $alarm['node_id'] ?? '' }}', '{{ $alarm['node_type'] ?? '' }}')"
            class="px-4 py-3 hover:bg-gray-50 dark:bg-gray-900/50 cursor-pointer transition-colors"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                    <span class="status-indicator status-{{ $alarm['severity'] ?? 'warning' }} mt-1.5"></span>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $alarm['title'] ?? 'Unknown Alarm' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $alarm['description'] ?? '' }}</p>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-{{ $alarm['node_type'] === 'olt' ? 'blue' : 'green' }}-100 text-{{ $alarm['node_type'] === 'olt' ? 'blue' : 'green' }}-800">
                                {{ strtoupper($alarm['node_type'] ?? 'NODE') }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $alarm['location'] ?? '' }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs text-gray-400">{{ $alarm['time_ago'] ?? '' }}</span>
                    @if($alarm['is_critical'] ?? false)
                        <span class="block mt-1 px-2 py-0.5 text-xs font-medium rounded bg-red-100 dark:bg-red-900/50 text-red-800 animate-pulse">
                            CRITICAL
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No active alarms</p>
            <p class="text-xs text-gray-400 mt-1">All systems are operating normally</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($alarms->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        {{ $alarms->links() }}
    </div>
    @endif
</div>
