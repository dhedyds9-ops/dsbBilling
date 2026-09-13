<div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Route Planner</h3>
    </div>

    <!-- Route Selection -->
    <div class="p-4 space-y-4">
        <!-- Source Node -->
        <div>
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Source Node</label>
            @if($sourceNodeId)
                <div class="flex items-center justify-between p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-100 dark:bg-blue-900/50 text-blue-800">
                            {{ strtoupper($sourceNodeType ?? 'NODE') }}
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $sourceNodeName ?? 'Selected' }}</span>
                    </div>
                    <button 
                        wire:click="$set('sourceNodeId', null)"
                        class="text-gray-400 hover:text-gray-600 dark:text-gray-400"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @else
                <div class="p-3 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-center text-sm text-gray-500 dark:text-gray-400">
                    Click on a node to select source
                </div>
            @endif
        </div>

        <!-- Target Node -->
        <div>
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Target Node</label>
            @if($targetNodeId)
                <div class="flex items-center justify-between p-2 bg-green-50 dark:bg-green-900/30 rounded-lg border border-green-200">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 text-xs font-medium rounded bg-green-100 dark:bg-green-900/50 text-green-800">
                            {{ strtoupper($targetNodeType ?? 'NODE') }}
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $targetNodeName ?? 'Selected' }}</span>
                    </div>
                    <button 
                        wire:click="$set('targetNodeId', null)"
                        class="text-gray-400 hover:text-gray-600 dark:text-gray-400"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @else
                <div class="p-3 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-center text-sm text-gray-500 dark:text-gray-400">
                    Click on a node to select target
                </div>
            @endif
        </div>

        <!-- Optimization Type -->
        <div>
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-2 block">Optimization Type</label>
            <div class="grid grid-cols-3 gap-2">
                <button 
                    wire:click="$set('optimizationType', 'distance')"
                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $optimizationType === 'distance' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}"
                >
                    Distance
                </button>
                <button 
                    wire:click="$set('optimizationType', 'latency')"
                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $optimizationType === 'latency' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}"
                >
                    Latency
                </button>
                <button 
                    wire:click="$set('optimizationType', 'hops')"
                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $optimizationType === 'hops' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}"
                >
                    Hops
                </button>
            </div>
        </div>

        <!-- Calculate Button -->
        <button 
            wire:click="calculateRoute"
            @if(!$sourceNodeId || !$targetNodeId) disabled @endif
            class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
        >
            @if($isCalculating)
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Calculating...</span>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                <span>Calculate Route</span>
            @endif
        </button>

        <!-- Reset Button -->
        @if($sourceNodeId || $targetNodeId || $calculatedRoute)
        <button 
            wire:click="resetRoute"
            class="w-full px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 flex items-center justify-center space-x-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span>Reset</span>
        </button>
        @endif
    </div>

    <!-- Calculated Route -->
    @if($calculatedRoute)
    <div class="border-t border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Route Found</h4>
                <span class="px-2 py-0.5 text-xs font-medium rounded bg-green-100 dark:bg-green-900/50 text-green-800">
                    {{ count($calculatedRoute['segments'] ?? []) }} segments
                </span>
            </div>
        </div>

        <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
            <!-- Route Stats -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-2 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Distance</p>
                    <p class="text-sm font-semibold">{{ $routeStats['distance'] ?? 0 }} km</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-2 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Hops</p>
                    <p class="text-sm font-semibold">{{ $routeStats['hops'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Route Path -->
            <div class="space-y-1">
                @foreach($calculatedRoute['segments'] ?? [] as $index => $segment)
                <div class="flex items-center space-x-2 text-xs">
                    <span class="w-5 h-5 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 font-medium">{{ $index + 1 }}</span>
                    <span class="flex-1 text-gray-700 dark:text-gray-300">{{ $segment['name'] ?? 'Node ' . ($index + 1) }}</span>
                    <span class="text-gray-400">{{ $segment['distance'] ?? 0 }}m</span>
                </div>
                @endforeach
            </div>

            <!-- Show on Map -->
            <button 
                wire:click="showOnMap"
                class="w-full px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center space-x-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Show on Map</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Alternatives Toggle -->
    @if(count($alternativeRoutes) > 0)
    <div class="border-t border-gray-200 dark:border-gray-700">
        <button 
            wire:click="$toggle('showAlternatives')"
            class="w-full px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50 flex items-center justify-between"
        >
            <span>Alternative Routes ({{ count($alternativeRoutes) }})</span>
            <svg class="w-4 h-4 {{ $showAlternatives ? 'rotate-180' : '' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        @if($showAlternatives)
        <div class="p-4 pt-0 space-y-2">
            @foreach($alternativeRoutes as $index => $alt)
            <div 
                wire:click="selectAlternative({{ $index }})"
                class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:bg-gray-900/50 {{ $selectedRouteId === $alt['id'] ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : '' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Route {{ $index + 1 }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $alt['distance'] ?? 0 }} km</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif
</div>
