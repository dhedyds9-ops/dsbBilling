@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">analytics</span>
    <span class="text-lg">GIS Analytics</span>
@endsection

<div class="space-y-5 pb-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button wire:click="exportReport" class="p-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-lg hover:bg-indigo-100 dark:bg-indigo-900/50 flex items-center gap-2 text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span>
                Export
            </button>
            <button wire:click="refreshAnalytics" class="p-2 bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 rounded-lg hover:bg-slate-100 dark:bg-slate-800 flex items-center gap-2 text-sm font-semibold transition-colors border border-slate-200 dark:border-slate-700">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">refresh</span>
                Refresh
            </button>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="selectedMetric" class="w-full md:w-auto text-sm border-slate-200 dark:border-slate-700 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-90 dark:bg-slate-900 dark:text-slate-1000">
                <option value="coverage">Coverage</option>
                <option value="capacity">Capacity</option>
                <option value="performance">Performance</option>
            </select>
            <select wire:model.live="timeRange" class="w-full md:w-auto text-sm border-slate-200 dark:border-slate-700 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-90 dark:bg-slate-900 dark:text-slate-1000">
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
            </select>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $current = $summaryMetrics['current'] ?? [];
            $avg = $summaryMetrics['average'] ?? [];
            $peak = $summaryMetrics['peak'] ?? [];
            $lowest = $summaryMetrics['lowest'] ?? [];
        @endphp
        
        <!-- Current -->
        <div class="relative overflow-hidden rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white group transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2">Current</h3>
                <div class="text-3xl font-black text-indigo-700 mb-2">{{ $current['value'] ?? 0 }}%</div>
                <div class="text-xs text-emerald-600 font-semibold">{{ ($current['trend'] ?? '') === 'up' ? 'â†‘' : 'â†“' }} {{ $current['change'] ?? 0 }}%</div>
            </div>
        </div>

        <!-- Average -->
        <div class="relative overflow-hidden rounded-xl border border-emerald-200 shadow-md bg-gradient-to-br from-emerald-50 to-white group transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 uppercase tracking-widest mb-2">Average</h3>
                <div class="text-3xl font-black text-emerald-700 mb-2">{{ $avg['value'] ?? 0 }}%</div>
                <div class="text-xs text-emerald-600 font-semibold">{{ ($avg['trend'] ?? '') === 'up' ? 'â†‘' : 'â†“' }} {{ $avg['change'] ?? 0 }}%</div>
            </div>
        </div>

        <!-- Peak -->
        <div class="relative overflow-hidden rounded-xl border border-amber-200 shadow-md bg-gradient-to-br from-amber-50 to-white group transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-amber-500 uppercase tracking-widest mb-2">Peak</h3>
                <div class="text-3xl font-black text-amber-700 mb-2">{{ $peak['value'] ?? 0 }}%</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ isset($peak['timestamp']) ? \Carbon\Carbon::parse($peak['timestamp'])->diffForHumans() : '-' }}</div>
            </div>
        </div>

        <!-- Lowest -->
        <div class="relative overflow-hidden rounded-xl border border-red-200 shadow-md bg-gradient-to-br from-red-50 to-white group transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-red-500 uppercase tracking-widest mb-2">Lowest</h3>
                <div class="text-3xl font-black text-red-700 mb-2">{{ $lowest['value'] ?? 0 }}%</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ isset($lowest['timestamp']) ? \Carbon\Carbon::parse($lowest['timestamp'])->diffForHumans() : '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Performers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Top Performers</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($topPerformers as $row)
                    <div class="px-4 py-3 flex items-center justify-between hover:bg-slate-50 dark:bg-slate-900/50">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700">{{ $row['type'] ?? 'N/A' }}</span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $row['name'] ?? 'Unknown' }}</span>
                        </div>
                        <span class="text-sm font-bold text-emerald-600">{{ $row['value'] ?? 0 }}%</span>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500 dark:text-slate-400 text-sm">Tidak ada data</div>
                @endforelse
            </div>
        </div>

        <!-- Bottom Performers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Needs Attention</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($bottomPerformers as $row)
                    <div class="px-4 py-3 flex items-center justify-between hover:bg-slate-50 dark:bg-slate-900/50">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md bg-red-100 dark:bg-red-900/50 text-red-700">{{ $row['type'] ?? 'N/A' }}</span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $row['name'] ?? 'Unknown' }}</span>
                        </div>
                        <span class="text-sm font-bold text-red-600">{{ $row['value'] ?? 0 }}%</span>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500 dark:text-slate-400 text-sm">Tidak ada perangkat bermasalah</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
