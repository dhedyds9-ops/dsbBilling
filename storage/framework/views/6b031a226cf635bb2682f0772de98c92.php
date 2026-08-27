<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Search Input -->
    <div class="p-4 border-b border-gray-200">
        <div class="relative">
            <input 
                type="text"
                wire:model.live="query"
                placeholder="Search nodes, customers, tickets..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($query): ?>
                <button 
                    wire:click="$set('query', '')"
                    class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Search Type Filter -->
        <div class="mt-3 flex flex-wrap gap-2">
            <button 
                wire:click="$set('searchType', 'all')"
                class="px-3 py-1 text-xs font-medium rounded-full <?php echo e($searchType === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                All
            </button>
            <button 
                wire:click="$set('searchType', 'olt')"
                class="px-3 py-1 text-xs font-medium rounded-full <?php echo e($searchType === 'olt' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                OLT
            </button>
            <button 
                wire:click="$set('searchType', 'odp')"
                class="px-3 py-1 text-xs font-medium rounded-full <?php echo e($searchType === 'odp' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                ODP
            </button>
            <button 
                wire:click="$set('searchType', 'customer')"
                class="px-3 py-1 text-xs font-medium rounded-full <?php echo e($searchType === 'customer' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Customer
            </button>
            <button 
                wire:click="$set('searchType', 'ticket')"
                class="px-3 py-1 text-xs font-medium rounded-full <?php echo e($searchType === 'ticket' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Ticket
            </button>
        </div>
    </div>

    <!-- Search Results -->
    <div class="max-h-96 overflow-y-auto">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSearching): ?>
            <div class="p-4 text-center text-gray-500">
                <svg class="animate-spin h-5 w-5 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-sm">Searching...</p>
            </div>
        <?php elseif(count($results) > 0): ?>
            <ul class="divide-y divide-gray-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <li 
                    wire:click="$emit('selectSearchResult', '<?php echo e($result['id']); ?>', '<?php echo e($result['type']); ?>')"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-1 text-xs font-medium rounded bg-<?php echo e($result['type'] === 'olt' ? 'blue' : ($result['type'] === 'odp' ? 'green' : ($result['type'] === 'customer' ? 'purple' : 'amber'))); ?>-100 text-<?php echo e($result['type'] === 'olt' ? 'blue' : ($result['type'] === 'odp' ? 'green' : ($result['type'] === 'customer' ? 'purple' : 'amber'))); ?>-800">
                                <?php echo e(strtoupper($result['type'])); ?>

                            </span>
                            <div>
                                <p class="font-medium text-gray-900"><?php echo e($result['name']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo e($result['location']); ?></p>
                            </div>
                        </div>
                        <span class="status-indicator status-<?php echo e($result['status'] ?? 'active'); ?>"></span>
                    </div>
                </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        <?php elseif($query && !$isSearching): ?>
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No results found</p>
            </div>
        <?php else: ?>
            <!-- Recent Searches -->
            <div class="p-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Recent Searches</h4>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($recentSearches) > 0): ?>
                    <ul class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentSearches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $search): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li 
                            wire:click="$set('query', '<?php echo e($search); ?>')"
                            class="flex items-center space-x-2 text-sm text-gray-600 hover:text-gray-900 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><?php echo e($search); ?></span>
                        </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-sm text-gray-400">No recent searches</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\gis\components\search-panel.blade.php ENDPATH**/ ?>