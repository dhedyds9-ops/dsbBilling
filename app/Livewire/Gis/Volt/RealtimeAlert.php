<?php

use Illuminate\Support\Facades\Cache;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

state([
    'alerts' => [],
    'isVisible' => true,
    'autoHideDelay' => 5000,
]);

computed(fn () => $this->loadAlerts());

$loadAlerts = function () {
    $this->alerts = Cache::remember('gis_realtime_alerts', 15, function () {
        return $this->fetchAlerts();
    });
};

$fetchAlerts = function () {
    // Placeholder - in production this would fetch from a real-time source
    return [
        [
            'id' => 'ALT-' . time(),
            'type' => 'warning',
            'title' => 'High utilization detected',
            'message' => 'OLT-042 utilization exceeded 85%',
            'timestamp' => now()->toIso8601String(),
        ],
    ];
};

$addAlert = function ($type, $title, $message) {
    $alert = [
        'id' => 'ALT-' . time(),
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'timestamp' => now()->toIso8601String(),
    ];
    
    $this->alerts = array_prepend($this->alerts, $alert);
    
    // Keep only last 10 alerts
    $this->alerts = array_slice($this->alerts, 0, 10);
    
    Cache::put('gis_realtime_alerts', $this->alerts, 300);
};

$dismissAlert = function ($alertId) {
    $this->alerts = array_filter($this->alerts, fn($a) => $a['id'] !== $alertId);
    $this->alerts = array_values($this->alerts);
};

$clearAll = function () {
    $this->alerts = [];
};

$toggle = function () {
    $this->isVisible = !$this->isVisible;
};
?>

<div 
    x-data="{ show: @entangle('isVisible') }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-x-4"
    x-transition:enter-end="opacity-100 transform translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-x-0"
    x-transition:leave-end="opacity-0 transform translate-x-4"
    class="fixed top-4 right-4 z-[9999] w-80 space-y-3"
>
    @foreach($alerts as $alert)
    <div 
        class="bg-white rounded-lg shadow-lg border-l-4 
            @switch($alert['type'])
                @case('critical') border-red-500 @break
                @case('warning') border-amber-500 @break
                @case('success') border-green-500 @break
                @default border-blue-500
            @endswitch
            p-4"
        x-data="{ visible: true }"
        x-show="visible"
    >
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    @switch($alert['type'])
                        @case('critical')
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            @break
                        @case('warning')
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @break
                        @case('success')
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @break
                        @default
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                    @endswitch
                </div>
                
                <!-- Content -->
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $alert['message'] }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        {{ \Carbon\Carbon::parse($alert['timestamp'])->diffForHumans() }}
                    </p>
                </div>
            </div>
            
            <!-- Close Button -->
            <button 
                wire:click="dismissAlert('{{ $alert['id'] }}')"
                class="text-gray-400 hover:text-gray-600"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endforeach

    @if(count($alerts) > 0)
    <div class="text-center">
        <button 
            wire:click="clearAll"
            class="text-xs text-gray-500 hover:text-gray-700"
        >
            Clear All
        </button>
    </div>
    @endif
</div>
