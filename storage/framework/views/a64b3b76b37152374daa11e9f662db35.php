<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-700">Network Heatmap</h3>
    </div>

    <!-- Heatmap Layer Selector -->
    <div class="p-4 border-b border-gray-200">
        <label class="text-xs text-gray-500 mb-2 block">Heatmap Layer</label>
        <div class="grid grid-cols-2 gap-2">
            <button 
                wire:click="$set('activeLayer', 'capacity')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors <?php echo e($activeLayer === 'capacity' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Capacity
            </button>
            <button 
                wire:click="$set('activeLayer', 'traffic')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors <?php echo e($activeLayer === 'traffic' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Traffic
            </button>
            <button 
                wire:click="$set('activeLayer', 'customers')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors <?php echo e($activeLayer === 'customers' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Customers
            </button>
            <button 
                wire:click="$set('activeLayer', 'density')"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors <?php echo e($activeLayer === 'density' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>"
            >
                Density
            </button>
        </div>
    </div>

    <!-- Area Filter -->
    <div class="p-4 border-b border-gray-200">
        <label class="text-xs text-gray-500 mb-2 block">Area Filter</label>
        <select 
            wire:model.live="areaFilter"
            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        >
            <option value="all">All Areas</option>
            <option value="jakarta-pusat">Jakarta Pusat</option>
            <option value="jakarta-selatan">Jakarta Selatan</option>
            <option value="jakarta-barat">Jakarta Barat</option>
            <option value="jakarta-timur">Jakarta Timur</option>
            <option value="jakarta-utara">Jakarta Utara</option>
        </select>
    </div>

    <!-- Heatmap Parameters -->
    <div class="p-4 border-b border-gray-200 space-y-4">
        <!-- Radius -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs text-gray-500">Radius (km)</label>
                <span class="text-xs font-medium text-gray-700"><?php echo e($radiusKm); ?></span>
            </div>
            <input 
                type="range" 
                min="1" 
                max="50" 
                step="0.5"
                wire:model.live="radiusKm"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            >
        </div>

        <!-- Grid Size -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs text-gray-500">Grid Size (km)</label>
                <span class="text-xs font-medium text-gray-700"><?php echo e($gridSize); ?></span>
            </div>
            <input 
                type="range" 
                min="0.1" 
                max="5" 
                step="0.1"
                wire:model.live="gridSize"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            >
        </div>
    </div>

    <!-- Heatmap Legend -->
    <div class="p-4">
        <label class="text-xs text-gray-500 mb-2 block">Intensity</label>
        <div class="flex items-center space-x-1">
            <div class="flex-1 h-4 rounded-l" style="background: linear-gradient(to right, #10B981, #84CC16, #F59E0B, #EF4444);"></div>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-xs text-gray-400">Low</span>
            <span class="text-xs text-gray-400">High</span>
        </div>
    </div>

    <!-- Loading State -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isLoading): ?>
    <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
        <div class="flex items-center space-x-2 text-gray-600">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm">Generating heatmap...</span>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\gis\components\network-heatmap.blade.php ENDPATH**/ ?>