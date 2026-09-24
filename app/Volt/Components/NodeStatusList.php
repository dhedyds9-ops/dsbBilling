<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;

new class extends Component {
    public array $nodes = [];
    public ?string $selectedNode = null;
    public string $filter = 'all'; // all, online, offline, warning
    
    protected $listeners = ['selectNode' => 'selectNode', 'filterNodes' => 'filterByStatus'];
    
    public function mount()
    {
        $this->loadNodes();
    }
    
    public function loadNodes()
    {
        // Placeholder - dalam implementasi nyata, fetch dari API/cache
        $this->nodes = [
            ['id' => 'olt-01', 'name' => 'OLT-01', 'type' => 'olt', 'status' => 'online', 'cpu' => 45, 'memory' => 62],
            ['id' => 'olt-02', 'name' => 'OLT-02', 'type' => 'olt', 'status' => 'online', 'cpu' => 32, 'memory' => 58],
            ['id' => 'olt-03', 'name' => 'OLT-03', 'type' => 'olt', 'status' => 'warning', 'cpu' => 78, 'memory' => 85],
            ['id' => 'odp-01', 'name' => 'ODP-01', 'type' => 'odp', 'status' => 'online', 'cpu' => null, 'memory' => null],
            ['id' => 'odp-02', 'name' => 'ODP-02', 'type' => 'odp', 'status' => 'online', 'cpu' => null, 'memory' => null],
            ['id' => 'odp-03', 'name' => 'ODP-03', 'type' => 'odp', 'status' => 'offline', 'cpu' => null, 'memory' => null],
        ];
    }
    
    public function selectNode(string $nodeId)
    {
        $this->selectedNode = $nodeId;
        $this->dispatch('nodeSelected', $nodeId)->to('node-details-panel');
    }
    
    public function filterByStatus(string $status)
    {
        $this->filter = $status;
    }
    
    public function getFilteredNodes(): array
    {
        if ($this->filter === 'all') {
            return $this->nodes;
        }
        
        return array_filter($this->nodes, fn($node) => $node['status'] === $this->filter);
    }
    
    public function getStatusColor(string $status): string
    {
        return match($status) {
            'online' => 'success',
            'offline' => 'danger',
            'warning' => 'warning',
            default => 'neutral',
        };
    }
    
    public function render(): mixed
    {
        $nodes = $this->getFilteredNodes();
        
        return <<<'HTML'
        <div class="space-y-3">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
                <button 
                    wire:click="filterByStatus('all')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $filter === 'all' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    All ({{ count($nodes) }})
                </button>
                <button 
                    wire:click="filterByStatus('online')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $filter === 'online' ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    Online
                </button>
                <button 
                    wire:click="filterByStatus('warning')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $filter === 'warning' ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    Warning
                </button>
                <button 
                    wire:click="filterByStatus('offline')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $filter === 'offline' ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    Offline
                </button>
            </div>
            
            <!-- Node List -->
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($nodes as $node)
                <div 
                    wire:click="selectNode('{{ $node['id'] }}')"
                    class="p-3 rounded-lg border cursor-pointer transition-all {{ $selectedNode === $node['id'] ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                @if($node['type'] === 'olt')
                                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $node['name'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase">{{ $node['type'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($node['cpu'])
                            <span class="text-xs text-slate-500">CPU: {{ $node['cpu'] }}%</span>
                            @endif
                            <span class="status-dot status-dot-{{ getStatusColor($node['status']) }}"></span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        HTML;
    }
    
    private function getStatusColor(string $status): string
    {
        return match($status) {
            'online' => 'success',
            'offline' => 'danger',
            'warning' => 'warning',
            default => 'neutral',
        };
    }
};
