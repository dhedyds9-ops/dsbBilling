<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public array $alerts = [];
    public int $unreadCount = 0;
    public int $refreshInterval = 15; // seconds
    
    protected $listeners = ['refreshAlerts' => 'loadAlerts', 'markAsRead' => 'markAlertAsRead'];
    
    public function mount()
    {
        $this->loadAlerts();
    }
    
    public function loadAlerts()
    {
        $this->alerts = Cache::remember('noc_alerts_realtime', $this->refreshInterval, function () {
            return $this->fetchAlerts();
        });
        
        $this->unreadCount = count(array_filter($this->alerts, fn($a) => !$a['read']));
    }
    
    protected function fetchAlerts(): array
    {
        // Placeholder - dalam implementasi nyata, fetch dari database
        return [
            [
                'id' => 1,
                'severity' => 'critical',
                'title' => 'OLT-01 CPU High',
                'message' => 'CPU usage at 92%',
                'time' => now()->diffForHumans(),
                'read' => false,
            ],
            [
                'id' => 2,
                'severity' => 'warning',
                'title' => 'Network Latency',
                'message' => 'Average latency 250ms',
                'time' => now()->subMinutes(15)->diffForHumans(),
                'read' => false,
            ],
            [
                'id' => 3,
                'severity' => 'info',
                'title' => 'Scheduled Maintenance',
                'message' => 'OLT-03 maintenance',
                'time' => now()->subHour()->diffForHumans(),
                'read' => true,
            ],
        ];
    }
    
    public function markAlertAsRead(int $alertId)
    {
        foreach ($this->alerts as &$alert) {
            if ($alert['id'] === $alertId) {
                $alert['read'] = true;
                break;
            }
        }
        
        $this->unreadCount = count(array_filter($this->alerts, fn($a) => !$a['read']));
    }
    
    public function getSeverityColor(string $severity): string
    {
        return match($severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            'info' => 'primary',
            default => 'neutral',
        };
    }
    
    public function render(): mixed
    {
        return <<<'HTML'
        <div class="space-y-3">
            @foreach($alerts as $alert)
            <div 
                wire:click="markAlertAsRead({{ $alert['id'] }})"
                class="p-4 rounded-xl border transition-all cursor-pointer {{ !$alert['read'] ? 'bg-' . getSeverityColor($alert['severity']) . '-50 dark:bg-' . getSeverityColor($alert['severity']) . '-900/10 border-' . getSeverityColor($alert['severity']) . '-200 dark:border-' . getSeverityColor($alert['severity']) . '-800' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-primary-300' }}"
            >
                <div class="flex items-start gap-3">
                    <div class="mt-0.5">
                        @if($alert['severity'] === 'critical')
                        <div class="w-2 h-2 rounded-full bg-danger-500 animate-pulse"></div>
                        @elseif($alert['severity'] === 'warning')
                        <div class="w-2 h-2 rounded-full bg-warning-500"></div>
                        @else
                        <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $alert['title'] }}</p>
                            <span class="text-xs text-slate-400">{{ $alert['time'] }}</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $alert['message'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
            
            @if($unreadCount > 0)
            <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                <button 
                    wire:click="markAllAsRead"
                    class="text-xs text-primary-600 hover:text-primary-700"
                >
                    Mark all as read
                </button>
            </div>
            @endif
        </div>
        HTML;
    }
    
    private function getSeverityColor(string $severity): string
    {
        return $this->getSeverityColor($severity);
    }
};
