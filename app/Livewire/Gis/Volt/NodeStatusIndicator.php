<?php

use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use Illuminate\Support\Facades\Cache;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

state([
    'nodeType' => 'olt',
    'nodeId' => null,
    'status' => 'active',
    'utilization' => 0,
    'latency' => 0,
    'lastUpdate' => null,
]);

computed(fn () => $this->fetchNodeStatus());

$fetchNodeStatus = function () {
    $cacheKey = "node_status_{$this->nodeType}_{$this->nodeId}";
    
    $data = Cache::remember($cacheKey, 10, function () {
        return match($this->nodeType) {
            'olt' => $this->getOltStatus(),
            'odp' => $this->getOdpStatus(),
            'onu' => $this->getOnuStatus(),
            'fiber' => $this->getFiberStatus(),
            default => null,
        };
    });
    
    if ($data) {
        $this->status = $data['status'] ?? 'unknown';
        $this->utilization = $data['utilization'] ?? 0;
        $this->latency = $data['latency'] ?? 0;
        $this->lastUpdate = now()->toIso8601String();
    }
};

$getOltStatus = function () {
    $olt = Olt::find($this->nodeId);
    if (!$olt) return null;
    
    return [
        'status' => $olt->status ?? 'active',
        'utilization' => $olt->utilization ?? 0,
        'latency' => rand(1, 10), // Placeholder
    ];
};

$getOdpStatus = function () {
    $odp = Odp::find($this->nodeId);
    if (!$odp) return null;
    
    return [
        'status' => $odp->status ?? 'active',
        'utilization' => $odp->utilization ?? 0,
        'latency' => rand(1, 15),
    ];
};

$getOnuStatus = function () {
    return [
        'status' => 'online',
        'utilization' => rand(30, 80),
        'latency' => rand(5, 20),
    ];
};

$getFiberStatus = function () {
    return [
        'status' => 'active',
        'utilization' => rand(20, 60),
        'latency' => rand(1, 5),
    ];
};

$refresh = function () {
    Cache::forget("node_status_{$this->nodeType}_{$this->nodeId}");
    $this->fetchNodeStatus();
};
?>

<div class="flex items-center space-x-3 p-3 bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Status Indicator -->
    <div class="relative">
        <span class="status-indicator status-{{ $status }}"></span>
        @if($status === 'critical')
            <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
        @endif
    </div>

    <!-- Node Info -->
    <div class="flex-1">
        <div class="flex items-center space-x-2">
            <span class="px-2 py-0.5 text-xs font-medium rounded bg-{{ $nodeType === 'olt' ? 'blue' : 'green' }}-100 text-{{ $nodeType === 'olt' ? 'blue' : 'green' }}-800">
                {{ strtoupper($nodeType) }}
            </span>
            <span class="text-sm font-medium text-gray-900">{{ $nodeId ?? 'N/A' }}</span>
        </div>
        <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500">
            <span>Util: {{ $utilization }}%</span>
            <span>Latency: {{ $latency }}ms</span>
        </div>
    </div>

    <!-- Last Update -->
    <div class="text-xs text-gray-400">
        @if($lastUpdate)
            {{ \Carbon\Carbon::parse($lastUpdate)->diffForHumans() }}
        @endif
    </div>

    <!-- Refresh Button -->
    <button 
        wire:click="$refresh"
        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
    </button>
</div>
