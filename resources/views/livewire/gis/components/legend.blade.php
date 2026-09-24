<div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Legend</h3>
        <button 
            wire:click="$toggle('isCollapsed')"
            class="text-gray-400 hover:text-gray-600 dark:text-gray-400"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isCollapsed ? 'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7' }}"></path>
            </svg>
        </button>
    </div>

    @unless($isCollapsed)
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button 
            wire:click="$set('activeTab', 'layers')"
            class="flex-1 px-4 py-2 text-sm font-medium {{ $activeTab === 'layers' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-300' }}"
        >
            Layers
        </button>
        <button 
            wire:click="$set('activeTab', 'status')"
            class="flex-1 px-4 py-2 text-sm font-medium {{ $activeTab === 'status' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-300' }}"
        >
            Status
        </button>
        <button 
            wire:click="$set('activeTab', 'utilization')"
            class="flex-1 px-4 py-2 text-sm font-medium {{ $activeTab === 'utilization' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-300' }}"
        >
            Utilization
        </button>
    </div>

    <!-- Content -->
    <div class="p-4 max-h-96 overflow-y-auto">
        @switch($activeTab)
            @case('layers')
                <!-- Layer Visibility -->
                <div class="space-y-2">
                    @foreach($layers as $name => $layer)
                    <label class="flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:bg-gray-900/50 p-2 rounded">
                        <div class="flex items-center space-x-3">
                            <input 
                                type="checkbox" 
                                wire:change="onToggleLayer('{{ $name }}')"
                                @if($layer['visible']) checked @endif
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100"
                            >
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $layer['color'] }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $layer['label'] }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $layer['icon'] }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="mt-4 flex space-x-2">
                    <button 
                        wire:click="showAll"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded hover:bg-blue-100 dark:bg-blue-900/50"
                    >
                        Show All
                    </button>
                    <button 
                        wire:click="hideAll"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded hover:bg-gray-100 dark:bg-gray-800"
                    >
                        Hide All
                    </button>
                </div>
                @break

            @case('status')
                <!-- Status Legend -->
                <div class="space-y-2">
                    @foreach($statusLegend as $key => $status)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 dark:bg-gray-900/50 rounded">
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $status['color'] }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $status['label'] }}</span>
                        </div>
                        <span class="text-xs text-gray-400 capitalize">{{ $key }}</span>
                    </div>
                    @endforeach
                </div>
                @break

            @case('utilization')
                <!-- Utilization Legend -->
                <div class="space-y-3">
                    @foreach($utilizationLegend as $item)
                    <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 dark:bg-gray-900/50 rounded">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-4 rounded" style="background-color: {{ $item['color'] }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item['label'] }}</span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item['range'] }}</span>
                    </div>
                    @endforeach
                </div>
                @break
        @endswitch
    </div>
    @endunless
</div>
