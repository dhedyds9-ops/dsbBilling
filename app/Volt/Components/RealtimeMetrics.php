<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public array $metrics = [];
    public bool $isLoading = false;
    public int $refreshInterval = 30; // seconds
    
    protected $listeners = ['refreshMetrics' => 'loadMetrics'];
    
    public function mount()
    {
        $this->loadMetrics();
    }
    
    public function loadMetrics()
    {
        $this->isLoading = true;
        
        $this->metrics = Cache::remember('realtime_metrics', $this->refreshInterval, function () {
            return $this->fetchMetrics();
        });
        
        $this->isLoading = false;
    }
    
    protected function fetchMetrics(): array
    {
        // Placeholder - dalam implementasi nyata, fetch dari database/cache
        return [
            'total_customers' => [
                'value' => 1234,
                'change' => 5.2,
                'trend' => 'up',
            ],
            'active_sessions' => [
                'value' => 892,
                'change' => 2.1,
                'trend' => 'up',
            ],
            'network_uptime' => [
                'value' => 99.8,
                'change' => 0.1,
                'trend' => 'up',
            ],
            'bandwidth_usage' => [
                'value' => 68.5,
                'change' => -3.2,
                'trend' => 'down',
            ],
            'active_tickets' => [
                'value' => 45,
                'change' => -8.3,
                'trend' => 'down',
            ],
            'pending_invoices' => [
                'value' => 23,
                'change' => 12.5,
                'trend' => 'up',
            ],
        ];
    }
    
    public function render(): mixed
    {
        return <<<'HTML'
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($metrics as $key => $metric)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                        {{ str_replace('_', ' ', $key) }}
                    </span>
                    @if($isLoading)
                    <div class="w-4 h-4 spinner spinner-sm text-primary-500"></div>
                    @endif
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                        @if(str_contains($key, 'uptime') || str_contains($key, 'usage'))
                            {{ $metric['value'] }}%
                        @elseif(str_contains($key, 'customers') || str_contains($key, 'sessions') || str_contains($key, 'tickets') || str_contains($key, 'invoices'))
                            {{ number_format($metric['value']) }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </p>
                    <div class="flex items-center gap-1 mt-1">
                        @if($metric['trend'] === 'up')
                        <svg class="w-3 h-3 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        <span class="text-xs text-success-500">{{ abs($metric['change']) }}%</span>
                        @else
                        <svg class="w-3 h-3 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        <span class="text-xs text-danger-500">{{ abs($metric['change']) }}%</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        HTML;
    }
};
