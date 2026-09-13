<div class="h-full flex flex-col space-y-4">
    <!-- Header Page Title for NOC -->
    <div class="flex items-center gap-2 mb-4 px-2">
        <span class="material-symbols-outlined notranslate text-blue-400" translate="no" style="font-size:24px">space_dashboard</span>
        <span class="text-lg font-bold tracking-wider text-gray-200 uppercase noc-mono">GIS Dashboard</span>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 px-2">
        <!-- Total OLT -->
        <div class="relative overflow-hidden rounded border noc-border noc-panel-bg shadow-md group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 to-cyan-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-cyan-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">dns</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-cyan-400 uppercase tracking-widest mb-2 noc-mono">Total OLT</h3>
                <div class="text-4xl font-black text-gray-100 mb-3">{{ $stats['total_olt'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 noc-mono"><span class="text-green-400">{{ $stats['active_olt'] ?? 0 }}</span> OLT Aktif</div>
            </div>
        </div>

        <!-- Total ODP -->
        <div class="relative overflow-hidden rounded border noc-border noc-panel-bg shadow-md group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-600 to-teal-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">share</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-2 noc-mono">Total ODP</h3>
                <div class="text-4xl font-black text-gray-100 mb-3">{{ $stats['total_odp'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 noc-mono"><span class="text-emerald-400">{{ $stats['active_odp'] ?? 0 }}</span> ODP Aktif</div>
            </div>
        </div>

        <!-- Total ONU -->
        <div class="relative overflow-hidden rounded border noc-border noc-panel-bg shadow-md group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-600 to-fuchsia-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-purple-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">router</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-purple-400 uppercase tracking-widest mb-2 noc-mono">Total ONU</h3>
                <div class="text-4xl font-black text-gray-100 mb-3">{{ $stats['total_onu'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 noc-mono"><span class="text-purple-400">{{ $stats['online_onu'] ?? 0 }}</span> Online</div>
            </div>
        </div>

        <!-- Active Alarms -->
        <div class="relative overflow-hidden rounded border noc-border noc-panel-bg shadow-md group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-600 to-rose-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-red-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">warning</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-red-400 uppercase tracking-widest mb-2 noc-mono">Active Alarms</h3>
                <div class="text-4xl font-black text-red-400 mb-3">{{ $stats['active_alarms'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 noc-mono"><span class="text-red-500">{{ $stats['critical_alarms'] ?? 0 }}</span> Kritis</div>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="px-4 py-3 border-t noc-border flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex space-x-2">
            <button wire:click="$set('timeRange', '24h')" class="px-3 py-1 text-sm rounded {{ $timeRange === '24h' ? 'bg-blue-900 text-blue-300' : 'text-gray-400 hover:bg-gray-800' }}">24h</button>
            <button wire:click="$set('timeRange', '7d')" class="px-3 py-1 text-sm rounded {{ $timeRange === '7d' ? 'bg-blue-900 text-blue-300' : 'text-gray-400 hover:bg-gray-800' }}">7d</button>
            <button wire:click="$set('timeRange', '30d')" class="px-3 py-1 text-sm rounded {{ $timeRange === '30d' ? 'bg-blue-900 text-blue-300' : 'text-gray-400 hover:bg-gray-800' }}">30d</button>
        </div>
        <select 
            wire:model.live="selectedArea"
            class="w-full md:w-auto text-sm noc-input rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100"
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
    <div class="border-t noc-border">
        <div class="px-4 py-3" style="background:#0d1326;">
            <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-widest noc-mono">Recent Alarms</h3>
        </div>
        <div class="divide-y divide-gray-800 max-h-64 overflow-y-auto noc-scroll">
            @forelse($recentAlarms as $alarm)
            <div class="px-4 py-3 hover:bg-gray-800 cursor-pointer transition-colors" wire:click="$emit('showNodeDetails', '{{ $alarm['node_id'] ?? '' }}', '{{ $alarm['node_type'] ?? '' }}')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="status-indicator status-{{ $alarm['severity'] ?? 'warning' }}"></span>
                        <span class="font-medium text-gray-200">{{ $alarm['title'] ?? 'Unknown Alarm' }}</span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 noc-mono">{{ $alarm['time_ago'] ?? '' }}</span>
                </div>
                <p class="text-sm text-gray-400 mt-1 pl-5">{{ $alarm['location'] ?? '' }}</p>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-sm noc-mono">No active alarms</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Critical Nodes -->
    <div class="border-t noc-border">
        <div class="px-4 py-3" style="background:#0d1326;">
            <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-widest noc-mono">Critical Nodes</h3>
        </div>
        <div class="divide-y divide-gray-800 max-h-64 overflow-y-auto noc-scroll">
            @forelse($criticalNodes as $node)
            <div class="px-4 py-3 hover:bg-gray-800 cursor-pointer transition-colors" wire:click="$emit('showNodeDetails', '{{ $node['id'] ?? '' }}', '{{ $node['type'] ?? '' }}')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-0.5 text-xs font-medium rounded border border-blue-500/30 text-blue-400 bg-blue-900/20 noc-mono">
                            {{ strtoupper($node['type'] ?? '') }}
                        </span>
                        <span class="font-medium text-gray-200">{{ $node['name'] ?? 'Unknown' }}</span>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-medium rounded border noc-mono {{ ($node['status'] ?? '') == 'critical' ? 'bg-red-900/20 text-red-400 border-red-500/30' : 'bg-yellow-900/20 text-yellow-400 border-yellow-500/30' }}">
                        {{ ucfirst($node['status'] ?? 'unknown') }}
                    </span>
                </div>
                <p class="text-sm text-gray-400 mt-1 pl-5">{{ $node['location'] ?? '' }} - {{ $node['utilization'] ?? 0 }}% utilization</p>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                <p class="text-sm noc-mono">No critical nodes</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Loading State -->
    @if($isLoading)
    <div class="absolute inset-0 bg-[#0a0e1a]/80 backdrop-blur-sm flex items-center justify-center z-10">
        <div class="flex flex-col items-center space-y-4 text-blue-400">
            <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-semibold tracking-widest uppercase noc-mono text-cyan-400">Loading GIS Data...</span>
        </div>
    </div>
    @endif
</div>
