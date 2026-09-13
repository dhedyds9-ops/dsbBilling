<div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Node Details</h3>
        <button 
            wire:click="$emit('closeNodeDetails')"
            class="text-gray-400 hover:text-gray-600 dark:text-gray-400"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nodeId && $nodeType): ?>
    <!-- Loading -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isLoading): ?>
        <div class="p-8 text-center">
            <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading...</p>
        </div>
    <?php else: ?>
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button 
            wire:click="$set('activeTab', 'overview')"
            class="flex-1 px-4 py-2 text-sm font-medium <?php echo e($activeTab === 'overview' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-300'); ?>"
        >
            Overview
        </button>
        <button 
            wire:click="$set('activeTab', 'performance')"
            class="flex-1 px-4 py-2 text-sm font-medium <?php echo e($activeTab === 'performance' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-300'); ?>"
        >
            Performance
        </button>
        <button 
            wire:click="$set('activeTab', 'connections')"
            class="flex-1 px-4 py-2 text-sm font-medium <?php echo e($activeTab === 'connections' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-300'); ?>"
        >
            Connections
        </button>
    </div>

    <!-- Content -->
    <div class="p-4 max-h-[60vh] overflow-y-auto">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($activeTab):
            case ('overview'): ?>
                <!-- Node Info -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1.5 text-sm font-medium rounded bg-<?php echo e($nodeType === 'olt' ? 'blue' : 'green'); ?>-100 text-<?php echo e($nodeType === 'olt' ? 'blue' : 'green'); ?>-800">
                                <?php echo e(strtoupper($nodeType)); ?>

                            </span>
                            <span class="status-indicator status-<?php echo e($nodeData['status'] ?? 'active'); ?>"></span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">ID: <?php echo e($nodeId); ?></span>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?php echo e($nodeData['name'] ?? 'Unknown Node'); ?></h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($nodeData['location'] ?? 'No location'); ?></p>
                    </div>

                    <!-- Coordinates -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Coordinates</p>
                        <p class="text-sm font-mono">
                            <?php echo e($nodeData['latitude'] ?? '0.0000'); ?>, <?php echo e($nodeData['longitude'] ?? '0.0000'); ?>

                        </p>
                    </div>

                    <!-- Status Info -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-sm font-medium capitalize"><?php echo e($nodeData['status'] ?? 'unknown'); ?></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Type</p>
                            <p class="text-sm font-medium capitalize"><?php echo e($nodeData['type'] ?? $nodeType); ?></p>
                        </div>
                    </div>

                    <!-- Utilization -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Utilization</p>
                            <p class="text-sm font-medium"><?php echo e($nodeData['utilization'] ?? 0); ?>%</p>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="h-2 rounded-full <?php echo e(($nodeData['utilization'] ?? 0) >= 90 ? 'bg-red-500' : (($nodeData['utilization'] ?? 0) >= 75 ? 'bg-amber-500' : 'bg-green-500')); ?>"
                                style="width: <?php echo e($nodeData['utilization'] ?? 0); ?>%"
                            ></div>
                        </div>
                    </div>
                </div>
                <?php break; ?>

            <?php case ('performance'): ?>
                <!-- Performance Metrics -->
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $performanceMetrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($metric['label'] ?? 'Metric'); ?></span>
                            <span class="text-sm font-semibold <?php echo e(($metric['value'] ?? 0) > ($metric['threshold'] ?? 100) ? 'text-red-600' : 'text-green-600'); ?>">
                                <?php echo e($metric['value'] ?? 0); ?><?php echo e($metric['unit'] ?? ''); ?>

                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div 
                                class="h-1.5 rounded-full <?php echo e(($metric['value'] ?? 0) > ($metric['threshold'] ?? 100) ? 'bg-red-500' : 'bg-green-500'); ?>"
                                style="width: <?php echo e(min(100, (($metric['value'] ?? 0) / ($metric['threshold'] ?? 100)) * 100)); ?>%"
                            ></div>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($performanceMetrics)): ?>
                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                        <p class="text-sm">No performance data available</p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php break; ?>

            <?php case ('connections'): ?>
                <!-- Related Nodes -->
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $relatedNodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div 
                        wire:click="$emit('showNodeDetails', '<?php echo e($related['id']); ?>', '<?php echo e($related['type']); ?>')"
                        class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:bg-gray-900/50 cursor-pointer transition-colors"
                    >
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-1 text-xs font-medium rounded bg-<?php echo e($related['type'] === 'olt' ? 'blue' : 'green'); ?>-100 text-<?php echo e($related['type'] === 'olt' ? 'blue' : 'green'); ?>-800">
                                <?php echo e(strtoupper($related['type'] ?? '')); ?>

                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($related['name'] ?? 'Unknown'); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($related['connection_type'] ?? 'Connected'); ?></p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                        <p class="text-sm">No connected nodes</p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php break; ?>
        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        <div class="flex space-x-2">
            <button 
                wire:click="$emit('showOnMap', $nodeId, $nodeType)"
                class="flex-1 px-3 py-2 text-sm font-medium text-blue-600 bg-white dark:bg-slate-800 border border-blue-600 rounded-lg hover:bg-blue-50 dark:bg-blue-900/30"
            >
                Show on Map
            </button>
            <button 
                wire:click="$emit('createTicket', $nodeId, $nodeType)"
                class="flex-1 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            >
                Create Ticket
            </button>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
    <!-- Empty State -->
    <div class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Select a node to view details</p>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\gis\components\node-details.blade.php ENDPATH**/ ?>