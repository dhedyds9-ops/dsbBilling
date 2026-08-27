<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Legend</h3>
        <button 
            wire:click="$toggle('isCollapsed')"
            class="text-gray-400 hover:text-gray-600"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($isCollapsed ? 'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7'); ?>"></path>
            </svg>
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($isCollapsed)): ?>
    <!-- Tabs -->
    <div class="flex border-b border-gray-200">
        <button 
            wire:click="$set('activeTab', 'layers')"
            class="flex-1 px-4 py-2 text-sm font-medium <?php echo e($activeTab === 'layers' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'); ?>"
        >
            Layers
        </button>
        <button 
            wire:click="$set('activeTab', 'status')"
            class="flex-1 px-4 py-2 text-sm font-medium <?php echo e($activeTab === 'status' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'); ?>"
        >
            Status
        </button>
        <button 
            wire:click="$set('activeTab', 'utilization')"
            class="flex-1 px-4 py-2 text-sm font-medium <?php echo e($activeTab === 'utilization' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'); ?>"
        >
            Utilization
        </button>
    </div>

    <!-- Content -->
    <div class="p-4 max-h-96 overflow-y-auto">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($activeTab):
            case ('layers'): ?>
                <!-- Layer Visibility -->
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $layers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $layer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <label class="flex items-center justify-between cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <div class="flex items-center space-x-3">
                            <input 
                                type="checkbox" 
                                wire:change="onToggleLayer('<?php echo e($name); ?>')"
                                <?php if($layer['visible']): ?> checked <?php endif; ?>
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <span class="w-3 h-3 rounded-full" style="background-color: <?php echo e($layer['color']); ?>"></span>
                            <span class="text-sm text-gray-700"><?php echo e($layer['label']); ?></span>
                        </div>
                        <span class="text-xs text-gray-400"><?php echo e($layer['icon']); ?></span>
                    </label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <div class="mt-4 flex space-x-2">
                    <button 
                        wire:click="showAll"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100"
                    >
                        Show All
                    </button>
                    <button 
                        wire:click="hideAll"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-50 rounded hover:bg-gray-100"
                    >
                        Hide All
                    </button>
                </div>
                <?php break; ?>

            <?php case ('status'): ?>
                <!-- Status Legend -->
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusLegend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 rounded-full" style="background-color: <?php echo e($status['color']); ?>"></span>
                            <span class="text-sm text-gray-700"><?php echo e($status['label']); ?></span>
                        </div>
                        <span class="text-xs text-gray-400 capitalize"><?php echo e($key); ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                <?php break; ?>

            <?php case ('utilization'): ?>
                <!-- Utilization Legend -->
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $utilizationLegend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-4 rounded" style="background-color: <?php echo e($item['color']); ?>"></span>
                            <span class="text-sm text-gray-700"><?php echo e($item['label']); ?></span>
                        </div>
                        <span class="text-xs text-gray-500"><?php echo e($item['range']); ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                <?php break; ?>
        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\gis\components\legend.blade.php ENDPATH**/ ?>