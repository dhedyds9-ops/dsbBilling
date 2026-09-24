<?php

use Illuminate\Support\Facades\Cache;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

state([
    'nodes' => [],
    'connections' => [],
    'refreshInterval' => 5000,
    'isConnected' => false,
    'lastSync' => null,
]);

computed(fn () => $this->loadNetworkGraph());

$loadNetworkGraph = function () {
    $this->nodes = Cache::remember('gis_network_nodes', 30, function () {
        return $this->fetchNetworkNodes();
    });
    
    $this->connections = Cache::remember('gis_network_connections', 30, function () {
        return $this->fetchConnections();
    });
    
    $this->lastSync = now()->toIso8601String();
    $this->isConnected = true;
};

$fetchNetworkNodes = function () {
    return [
        ['id' => 'OLT-001', 'type' => 'olt', 'name' => 'OLT Central', 'lat' => -6.2088, 'lng' => 106.8456, 'status' => 'active'],
        ['id' => 'OLT-002', 'type' => 'olt', 'name' => 'OLT South', 'lat' => -6.2544, 'lng' => 106.8456, 'status' => 'active'],
        ['id' => 'ODP-001', 'type' => 'odp', 'name' => 'ODP Kebayoran', 'lat' => -6.2350, 'lng' => 106.8550, 'status' => 'active'],
        ['id' => 'ODP-002', 'type' => 'odp', 'name' => 'ODP Menteng', 'lat' => -6.1950, 'lng' => 106.8350, 'status' => 'warning'],
        ['id' => 'ODP-003', 'type' => 'odp', 'name' => 'ODP Senayan', 'lat' => -6.2250, 'lng' => 106.8650, 'status' => 'active'],
    ];
};

$fetchConnections = function () {
    return [
        ['from' => 'OLT-001', 'to' => 'ODP-001', 'type' => 'fiber', 'capacity' => 100, 'utilization' => 45],
        ['from' => 'OLT-001', 'to' => 'ODP-002', 'type' => 'fiber', 'capacity' => 100, 'utilization' => 78],
        ['from' => 'OLT-002', 'to' => 'ODP-003', 'type' => 'fiber', 'capacity' => 100, 'utilization' => 32],
        ['from' => 'ODP-001', 'to' => 'OLT-001', 'type' => 'fiber', 'capacity' => 48, 'utilization' => 65],
    ];
};

$startSync = function () {
    $this->isConnected = true;
};

$stopSync = function () {
    $this->isConnected = false;
};

$refresh = function () {
    Cache::forget('gis_network_nodes');
    Cache::forget('gis_network_connections');
    $this->loadNetworkGraph();
};

$simulateUpdate = function () {
    // Simulate real-time network updates
    foreach ($this->nodes as &$node) {
        $node['utilization'] = rand(20, 95);
        $node['latency'] = rand(1, 50);
    }
    
    foreach ($this->connections as &$conn) {
        $conn['utilization'] = rand(10, 90);
        $conn['latency'] = rand(1, 20);
    }
    
    $this->lastSync = now()->toIso8601String();
};
?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <h3 class="text-sm font-semibold text-gray-700">Network Graph</h3>
            <span 
                class="w-2 h-2 rounded-full {{ $isConnected ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"
                title="{{ $isConnected ? 'Connected' : 'Disconnected' }}"
            ></span>
        </div>
        
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-400">
                Last sync: {{ $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Never' }}
            </span>
            <button 
                wire:click="refresh"
                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Network Visualization -->
    <div class="p-4">
        <div 
            class="w-full h-64 bg-gray-50 rounded-lg relative overflow-hidden"
            wire:ignore
            x-data="{
                canvas: null,
                ctx: null,
                nodes: @json($nodes),
                connections: @json($connections),
                
                init() {
                    this.initCanvas();
                    this.draw();
                },
                
                initCanvas() {
                    this.canvas = this.$refs.networkCanvas;
                    this.ctx = this.canvas.getContext('2d');
                    this.canvas.width = this.canvas.offsetWidth;
                    this.canvas.height = this.canvas.offsetHeight;
                },
                
                draw() {
                    if (!this.ctx) return;
                    
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    
                    // Draw connections
                    this.connections.forEach(conn => {
                        const fromNode = this.nodes.find(n => n.id === conn.from);
                        const toNode = this.nodes.find(n => n.id === conn.to);
                        
                        if (fromNode && toNode) {
                            const x1 = (fromNode.lng + 180) / 360 * this.canvas.width;
                            const y1 = (90 - fromNode.lat) / 180 * this.canvas.height;
                            const x2 = (toNode.lng + 180) / 360 * this.canvas.width;
                            const y2 = (90 - toNode.lat) / 180 * this.canvas.height;
                            
                            // Color based on utilization
                            const color = conn.utilization > 80 ? '#EF4444' : 
                                         conn.utilization > 60 ? '#F59E0B' : '#10B981';
                            
                            this.ctx.beginPath();
                            this.ctx.moveTo(x1, y1);
                            this.ctx.lineTo(x2, y2);
                            this.ctx.strokeStyle = color;
                            this.ctx.lineWidth = 2;
                            this.ctx.stroke();
                        }
                    });
                    
                    // Draw nodes
                    this.nodes.forEach(node => {
                        const x = (node.lng + 180) / 360 * this.canvas.width;
                        const y = (90 - node.lat) / 180 * this.canvas.height;
                        
                        const color = node.status === 'critical' ? '#EF4444' :
                                     node.status === 'warning' ? '#F59E0B' :
                                     node.status === 'inactive' ? '#6B7280' : '#10B981';
                        
                        // Node circle
                        this.ctx.beginPath();
                        this.ctx.arc(x, y, node.type === 'olt' ? 12 : 8, 0, Math.PI * 2);
                        this.ctx.fillStyle = color;
                        this.ctx.fill();
                        this.ctx.strokeStyle = 'white';
                        this.ctx.lineWidth = 2;
                        this.ctx.stroke();
                        
                        // Node label
                        this.ctx.fillStyle = '#374151';
                        this.ctx.font = '10px sans-serif';
                        this.ctx.textAlign = 'center';
                        this.ctx.fillText(node.id, x, y + 24);
                    });
                }
            }"
        >
            <canvas x-ref="networkCanvas" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Node List -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-2 bg-gray-50">
            <h4 class="text-xs font-semibold text-gray-700">Active Nodes ({{ count($nodes) }})</h4>
        </div>
        <div class="max-h-40 overflow-y-auto divide-y divide-gray-200">
            @foreach($nodes as $node)
            <div class="px-4 py-2 flex items-center justify-between hover:bg-gray-50">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-{{ $node['status'] === 'active' ? 'green' : ($node['status'] === 'warning' ? 'amber' : 'gray') }}-500"></span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-{{ $node['type'] === 'olt' ? 'blue' : 'green' }}-100 text-{{ $node['type'] === 'olt' ? 'blue' : 'green' }}-800">
                        {{ strtoupper($node['type']) }}
                    </span>
                    <span class="text-sm text-gray-900">{{ $node['name'] }}</span>
                </div>
                <span class="text-xs text-gray-500">{{ $node['id'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
