<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-700">Map Layers</h3>
    </div>

    <!-- Layer List -->
    <div class="p-4 space-y-3">
        @foreach($layers as $name => $visible)
        <label class="flex items-center justify-between cursor-pointer group">
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    wire:change="toggleLayer('{{ $name }}')"
                    @if($visible) checked @endif
                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300"
                >
                <span class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-layer-{{ $name }}"></span>
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ ucfirst($name) }}</span>
                </span>
            </div>
            @if($visible)
                <span class="text-xs text-green-600">Visible</span>
            @else
                <span class="text-xs text-gray-400">Hidden</span>
            @endif
        </label>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
        <div class="flex space-x-2">
            <button 
                wire:click="showAll"
                class="flex-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition-colors"
            >
                Show All
            </button>
            <button 
                wire:click="hideAll"
                class="flex-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors"
            >
                Hide All
            </button>
        </div>
    </div>

    <!-- Layer Opacity (for supported layers) -->
    <div class="px-4 py-3 border-t border-gray-200">
        <label class="text-xs text-gray-500 mb-2 block">Global Opacity</label>
        <input 
            type="range" 
            min="0" 
            max="100" 
            value="100"
            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
        >
    </div>
</div>

@push('styles')
<style>
    .bg-layer-olt { background-color: #3B82F6; }
    .bg-layer-odp { background-color: #10B981; }
    .bg-layer-onu { background-color: #F59E0B; }
    .bg-layer-fiber { background-color: #6366F1; }
    .bg-layer-customer { background-color: #EC4899; }
    .bg-layer-heatmap { background-color: #EF4444; }
    .bg-layer-alarm { background-color: #EF4444; }
</style>
@endpush
