<div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Analytics</h3>
        
        <div class="flex items-center space-x-2">
            <!-- Time Range -->
            <select 
                wire:model.live="timeRange"
                class="text-xs border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
            </select>

            <!-- Refresh -->
            <button 
                wire:click="loadAnalytics"
                class="p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:bg-gray-800 rounded"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Metric Selector -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <label class="text-xs text-gray-500 dark:text-gray-400 mb-2 block">Selected Metric</label>
        <div class="grid grid-cols-3 gap-2">
            <button 
                wire:click="$set('selectedMetric', 'coverage')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $selectedMetric === 'coverage' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}"
            >
                Coverage
            </button>
            <button 
                wire:click="$set('selectedMetric', 'capacity')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $selectedMetric === 'capacity' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}"
            >
                Capacity
            </button>
            <button 
                wire:click="$set('selectedMetric', 'performance')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $selectedMetric === 'performance' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}"
            >
                Performance
            </button>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-2 gap-3">
            @foreach($summaryMetrics as $metric)
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $metric['label'] ?? 'Metric' }}</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $metric['value'] ?? 0 }}{{ $metric['unit'] ?? '' }}</p>
                @if(isset($metric['change']))
                    <p class="text-xs {{ ($metric['change'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ ($metric['change'] ?? 0) >= 0 ? '+' : '' }}{{ $metric['change'] ?? 0 }}%
                    </p>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Chart -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">Trend</h4>
        <div class="h-40">
            <canvas id="analytics-chart" wire:ignore></canvas>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">Top Performers</h4>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            @forelse($topPerformers as $performer)
            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-900/50 rounded">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $performer['name'] ?? 'Unknown' }}</span>
                </div>
                <span class="text-sm font-medium text-green-600">{{ $performer['value'] ?? 0 }}%</span>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-2">No data available</p>
            @endforelse
        </div>
    </div>

    <!-- Bottom Performers -->
    <div class="p-4">
        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">Needs Attention</h4>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            @forelse($bottomPerformers as $performer)
            <div class="flex items-center justify-between p-2 bg-red-50 dark:bg-red-900/30 rounded">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $performer['name'] ?? 'Unknown' }}</span>
                </div>
                <span class="text-sm font-medium text-red-600">{{ $performer['value'] ?? 0 }}%</span>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-2">No issues detected</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script>
        // Chart.js initialization
        document.addEventListener('livewire:load', function() {
            const ctx = document.getElementById('analytics-chart');
            if (ctx) {
                window.analyticsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartData['labels'] ?? []) !!},
                        datasets: [{
                            label: 'Coverage %',
                            data: {!! json_encode($chartData['values'] ?? []) !!},
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                min: 0,
                                max: 100
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>
