<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Analytics</h3>
        
        <div class="flex items-center space-x-2">
            <!-- Time Range -->
            <select 
                wire:model.live="timeRange"
                class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
            </select>

            <!-- Refresh -->
            <button 
                wire:click="loadAnalytics"
                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Metric Selector -->
    <div class="p-4 border-b border-gray-200">
        <label class="text-xs text-gray-500 mb-2 block">Selected Metric</label>
        <div class="grid grid-cols-3 gap-2">
            <button 
                wire:click="$set('selectedMetric', 'coverage')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors <?php echo e($selectedMetric === 'coverage' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Coverage
            </button>
            <button 
                wire:click="$set('selectedMetric', 'capacity')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors <?php echo e($selectedMetric === 'capacity' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Capacity
            </button>
            <button 
                wire:click="$set('selectedMetric', 'performance')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors <?php echo e($selectedMetric === 'performance' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Performance
            </button>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="p-4 border-b border-gray-200">
        <div class="grid grid-cols-2 gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $summaryMetrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500"><?php echo e($metric['label'] ?? 'Metric'); ?></p>
                <p class="text-lg font-semibold text-gray-900"><?php echo e($metric['value'] ?? 0); ?><?php echo e($metric['unit'] ?? ''); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($metric['change'])): ?>
                    <p class="text-xs <?php echo e(($metric['change'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                        <?php echo e(($metric['change'] ?? 0) >= 0 ? '+' : ''); ?><?php echo e($metric['change'] ?? 0); ?>%
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    <!-- Chart -->
    <div class="p-4 border-b border-gray-200">
        <h4 class="text-xs font-semibold text-gray-700 mb-3">Trend</h4>
        <div class="h-40">
            <canvas id="analytics-chart" wire:ignore></canvas>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="p-4 border-b border-gray-200">
        <h4 class="text-xs font-semibold text-gray-700 mb-3">Top Performers</h4>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topPerformers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $performer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-sm text-gray-700"><?php echo e($performer['name'] ?? 'Unknown'); ?></span>
                </div>
                <span class="text-sm font-medium text-green-600"><?php echo e($performer['value'] ?? 0); ?>%</span>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <p class="text-xs text-gray-400 text-center py-2">No data available</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Bottom Performers -->
    <div class="p-4">
        <h4 class="text-xs font-semibold text-gray-700 mb-3">Needs Attention</h4>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bottomPerformers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $performer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex items-center justify-between p-2 bg-red-50 rounded">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="text-sm text-gray-700"><?php echo e($performer['name'] ?? 'Unknown'); ?></span>
                </div>
                <span class="text-sm font-medium text-red-600"><?php echo e($performer['value'] ?? 0); ?>%</span>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <p class="text-xs text-gray-400 text-center py-2">No issues detected</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        // Chart.js initialization
        document.addEventListener('livewire:load', function() {
            const ctx = document.getElementById('analytics-chart');
            if (ctx) {
                window.analyticsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($chartData['labels'] ?? []); ?>,
                        datasets: [{
                            label: 'Coverage %',
                            data: <?php echo json_encode($chartData['values'] ?? []); ?>,
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
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\gis\components\analytics.blade.php ENDPATH**/ ?>