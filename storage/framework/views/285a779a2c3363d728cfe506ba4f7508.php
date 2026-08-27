<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <h3 class="text-sm font-semibold text-gray-700">Alarms</h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
                <?php echo e($alarms->total()); ?>

            </span>
        </div>
        
        <div class="flex items-center space-x-2">
            <!-- Auto Refresh Toggle -->
            <label class="flex items-center space-x-2 cursor-pointer">
                <input 
                    type="checkbox" 
                    wire:model.live="isAutoRefresh"
                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                >
                <span class="text-xs text-gray-500">Auto-refresh</span>
            </label>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-4 border-b border-gray-200 space-y-3">
        <!-- Search -->
        <input 
            type="text"
            wire:model.live="searchQuery"
            placeholder="Search alarms..."
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
        >

        <div class="flex flex-wrap gap-2">
            <!-- Severity Filter -->
            <select 
                wire:model.live="filterSeverity"
                class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="all">All Severity</option>
                <option value="critical">Critical</option>
                <option value="warning">Warning</option>
                <option value="info">Info</option>
            </select>

            <!-- Status Filter -->
            <select 
                wire:model.live="filterStatus"
                class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="acknowledged">Acknowledged</option>
                <option value="resolved">Resolved</option>
            </select>

            <!-- Time Range -->
            <select 
                wire:model.live="timeRange"
                class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="1h">Last 1 Hour</option>
                <option value="6h">Last 6 Hours</option>
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
            </select>
        </div>
    </div>

    <!-- Alarm List -->
    <div class="max-h-96 overflow-y-auto divide-y divide-gray-200">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $alarms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div 
            wire:click="$emit('showNodeDetails', '<?php echo e($alarm['node_id'] ?? ''); ?>', '<?php echo e($alarm['node_type'] ?? ''); ?>')"
            class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                    <span class="status-indicator status-<?php echo e($alarm['severity'] ?? 'warning'); ?> mt-1.5"></span>
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($alarm['title'] ?? 'Unknown Alarm'); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo e($alarm['description'] ?? ''); ?></p>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-<?php echo e($alarm['node_type'] === 'olt' ? 'blue' : 'green'); ?>-100 text-<?php echo e($alarm['node_type'] === 'olt' ? 'blue' : 'green'); ?>-800">
                                <?php echo e(strtoupper($alarm['node_type'] ?? 'NODE')); ?>

                            </span>
                            <span class="text-xs text-gray-400"><?php echo e($alarm['location'] ?? ''); ?></span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs text-gray-400"><?php echo e($alarm['time_ago'] ?? ''); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alarm['is_critical'] ?? false): ?>
                        <span class="block mt-1 px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800 animate-pulse">
                            CRITICAL
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500">No active alarms</p>
            <p class="text-xs text-gray-400 mt-1">All systems are operating normally</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alarms->hasPages()): ?>
    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
        <?php echo e($alarms->links()); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\gis\components\alarm-panel.blade.php ENDPATH**/ ?>