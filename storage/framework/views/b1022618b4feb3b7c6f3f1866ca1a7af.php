<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIS Platform - WiFinan</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <!-- Leaflet Draw -->
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet-draw.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet-draw.css" />
    
    <style>
        [x-cloak] { display: none !important; }
        
        .leaflet-container {
            height: 100%;
            width: 100%;
            z-index: 1;
        }
        
        .gis-sidebar {
            z-index: 1000;
        }
        
        .gis-toolbar {
            z-index: 1001;
        }
        
        .gis-legend {
            z-index: 1000;
        }
        
        .node-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .node-marker.olt {
            background-color: #3B82F6;
            width: 32px;
            height: 32px;
        }
        
        .node-marker.odp {
            background-color: #10B981;
            width: 24px;
            height: 24px;
        }
        
        .node-marker.onu {
            background-color: #F59E0B;
            width: 16px;
            height: 16px;
        }
        
        .node-marker.critical {
            animation: pulse-critical 1.5s infinite;
        }
        
        .node-marker.warning {
            animation: pulse-warning 2s infinite;
        }
        
        @keyframes pulse-critical {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        }
        
        @keyframes pulse-warning {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
            50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-active { background-color: #10B981; }
        .status-warning { background-color: #F59E0B; }
        .status-critical { background-color: #EF4444; }
        .status-inactive { background-color: #6B7280; }
        
        .layer-control {
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .sidebar-panel {
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.98);
        }
        
        .panel-tabs {
            border-bottom: 2px solid #E5E7EB;
        }
        
        .panel-tab {
            padding: 0.75rem 1rem;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }
        
        .panel-tab:hover {
            color: #3B82F6;
        }
        
        .panel-tab.active {
            color: #3B82F6;
            border-bottom-color: #3B82F6;
        }
    </style>
</head>
<body class="h-screen overflow-hidden bg-gray-100">
    <div x-data="gisPlatform()" x-init="init()" class="h-full flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 z-50">
            <div class="flex items-center justify-between px-4 py-2">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        <span class="text-xl font-bold text-gray-800">WiFinan GIS</span>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="hidden lg:flex items-center space-x-6 ml-8">
                        <div class="flex items-center space-x-2">
                            <span class="status-indicator status-active"></span>
                            <span class="text-sm text-gray-600">OLT: <span class="font-semibold">1180</span></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="status-indicator status-warning"></span>
                            <span class="text-sm text-gray-600">ODP: <span class="font-semibold">3245</span></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="status-indicator status-critical"></span>
                            <span class="text-sm text-gray-600">Alarms: <span class="font-semibold text-red-600">3</span></span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Time Range Selector -->
                    <select wire:model.live="timeRange" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                    
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                            <img class="w-8 h-8 rounded-full bg-gray-300" src="https://ui-avatars.com/api/?name=Admin&background=3B82F6&color=fff" alt="User">
                            <span class="text-sm font-medium">Admin</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden relative">
            <!-- Left Sidebar - Layers & Search -->
            <div class="gis-sidebar w-80 flex flex-col border-r border-gray-200 bg-white overflow-hidden">
                <!-- Search -->
                <div class="p-4 border-b border-gray-200">
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live="searchQuery"
                            @input="$wire.search()"
                            placeholder="Search nodes, customers..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        >
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <!-- Search Results -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($searchResults ?? []) > 0): ?>
                    <div class="mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $searchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div 
                            wire:click="selectSearchResult('<?php echo e($result['id']); ?>', '<?php echo e($result['type']); ?>')"
                            class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                        >
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 text-xs font-medium rounded bg-<?php echo e($result['type'] === 'olt' ? 'blue' : 'green'); ?>-100 text-<?php echo e($result['type'] === 'olt' ? 'blue' : 'green'); ?>-800">
                                    <?php echo e(strtoupper($result['type'])); ?>

                                </span>
                                <span class="font-medium text-gray-900"><?php echo e($result['name']); ?></span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1"><?php echo e($result['code']); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <!-- Layer Control -->
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Map Layers</h3>
                    <div class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $layers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $layerName => $visible): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:change="toggleLayer('<?php echo e($layerName); ?>')"
                                <?php if($visible): ?> checked <?php endif; ?>
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <span class="flex items-center space-x-2">
                                <span class="w-3 h-3 rounded-full" style="background-color: <?php echo e($layerColors[$layerName] ?? '#6B7280'); ?>"></span>
                                <span class="text-sm text-gray-700"><?php echo e(ucfirst($layerName)); ?></span>
                            </span>
                        </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            <span>Route</span>
                        </button>
                        <button class="px-3 py-2 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add ODP</span>
                        </button>
                        <button class="px-3 py-2 text-xs font-medium text-yellow-700 bg-yellow-50 rounded-lg hover:bg-yellow-100 flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Report</span>
                        </button>
                        <button class="px-3 py-2 text-xs font-medium text-purple-700 bg-purple-50 rounded-lg hover:bg-purple-100 flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <span>Export</span>
                        </button>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="flex-1 overflow-y-auto p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Recent Activity</h3>
                    <div class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentAlarms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center space-x-2">
                                    <span class="w-2 h-2 rounded-full bg-<?php echo e($alarm['severity'] === 'critical' ? 'red' : 'yellow'); ?>-500"></span>
                                    <span class="text-sm font-medium text-gray-900"><?php echo e($alarm['id']); ?></span>
                                </span>
                                <span class="text-xs text-gray-500"><?php echo e($alarm['timestamp']); ?></span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1"><?php echo e($alarm['message']); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Map Container -->
            <div class="flex-1 relative">
                <div id="gis-map" class="absolute inset-0"></div>
                
                <!-- Toolbar -->
                <div class="gis-toolbar absolute top-4 right-4 flex flex-col space-y-2">
                    <button @click="zoomIn" class="p-2 bg-white rounded-lg shadow-md hover:bg-gray-50" title="Zoom In">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                    <button @click="zoomOut" class="p-2 bg-white rounded-lg shadow-md hover:bg-gray-50" title="Zoom Out">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <button @click="locateMe" class="p-2 bg-white rounded-lg shadow-md hover:bg-gray-50" title="My Location">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                    </button>
                    <button @click="toggleFullscreen" class="p-2 bg-white rounded-lg shadow-md hover:bg-gray-50" title="Fullscreen">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Legend -->
                <div class="gis-legend absolute bottom-4 left-4 bg-white rounded-lg shadow-lg p-4 max-w-xs">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Legend</h4>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <span class="w-4 h-4 rounded-full bg-blue-500"></span>
                            <span class="text-sm text-gray-600">OLT</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-sm text-gray-600">ODP</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                            <span class="text-sm text-gray-600">ONU</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="flex items-center space-x-2">
                                <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                                <span class="text-sm text-gray-600">Critical Alarm</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Zoom Controls -->
                <div class="absolute bottom-4 right-4 bg-white rounded-lg shadow-lg p-4">
                    <div class="text-xs text-gray-500 mb-2">Utilization</div>
                    <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-500 via-yellow-500 to-red-500" style="width: 72%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0%</span>
                        <span>72%</span>
                        <span>100%</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Sidebar - Details Panel -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedNodeId): ?>
            <div class="gis-sidebar w-96 flex flex-col border-l border-gray-200 bg-white">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Node Details</h3>
                    <button wire:click="clearSelection" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($nodeDetails)): ?>
                    <div class="p-4">
                        <!-- Node Header -->
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xl font-semibold text-gray-900"><?php echo e($nodeDetails['name'] ?? 'N/A'); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo e($nodeDetails['code'] ?? ''); ?></p>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="mb-6">
                            <?php
                                $statusClass = match($nodeDetails['status'] ?? 'inactive') {
                                    'active' => 'bg-green-100 text-green-800',
                                    'warning' => 'bg-yellow-100 text-yellow-800',
                                    'critical' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>
                            <span class="px-3 py-1 text-sm font-medium rounded-full <?php echo e($statusClass); ?>">
                                <?php echo e(ucfirst($nodeDetails['status'] ?? 'Unknown')); ?>

                            </span>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="panel-tabs flex space-x-4 mb-4">
                            <button class="panel-tab active" @click="activeTab = 'overview'">Overview</button>
                            <button class="panel-tab" @click="activeTab = 'capacity'">Capacity</button>
                            <button class="panel-tab" @click="activeTab = 'customers'">Customers</button>
                        </div>
                        
                        <!-- Overview Content -->
                        <div x-show="activeTab === 'overview'">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm text-gray-500">Location</label>
                                    <p class="text-gray-900"><?php echo e($nodeDetails['location']['name'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <label class="text-sm text-gray-500">Coordinates</label>
                                    <p class="text-gray-900"><?php echo e($nodeDetails['location']['lat'] ?? 'N/A'); ?>, <?php echo e($nodeDetails['location']['lon'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <label class="text-sm text-gray-500">Type</label>
                                    <p class="text-gray-900"><?php echo e(strtoupper($nodeDetails['type'] ?? 'N/A')); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Capacity Content -->
                        <div x-show="activeTab === 'capacity'">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-500">Port Utilization</span>
                                        <span class="font-medium"><?php echo e($nodeDetails['capacity']['utilization'] ?? 0); ?>%</span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-200 rounded-full">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: <?php echo e($nodeDetails['capacity']['utilization'] ?? 0); ?>%"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <p class="text-sm text-gray-500">Total Ports</p>
                                        <p class="text-xl font-semibold text-gray-900"><?php echo e($nodeDetails['capacity']['total_ports'] ?? 0); ?></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <p class="text-sm text-gray-500">Used Ports</p>
                                        <p class="text-xl font-semibold text-gray-900"><?php echo e($nodeDetails['capacity']['used_ports'] ?? 0); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    
    <script>
        function gisPlatform() {
            return {
                map: null,
                markers: {},
                activeTab: 'overview',
                layers: {},
                
                init() {
                    this.initMap();
                    this.loadMarkers();
                },
                
                initMap() {
                    this.map = L.map('gis-map').setView([-6.2088, 106.8456], 12);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);
                    
                    // Add draw control
                    var drawnItems = new L.FeatureGroup();
                    this.map.addLayer(drawnItems);
                    
                    var drawControl = new L.Control.Draw({
                        draw: {
                            polyline: true,
                            polygon: true,
                            marker: true,
                        },
                        edit: {
                            featureGroup: drawnItems
                        }
                    });
                    
                    this.map.on('zoomend', () => {
                        this.$wire.dispatch('mapMoved', {
                            center: this.map.getCenter(),
                            zoom: this.map.getZoom()
                        });
                    });
                },
                
                loadMarkers() {
                    // Sample OLT markers
                    var oltIcon = L.divIcon({
                        className: 'node-marker olt',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });
                    
                    L.marker([-6.2088, 106.8650], {icon: oltIcon})
                        .addTo(this.map)
                        .bindPopup('<b>OLT Central Jakarta</b><br>Utilization: 85%');
                    
                    // Sample ODP markers
                    var odpIcon = L.divIcon({
                        className: 'node-marker odp',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });
                    
                    L.marker([-6.2415, 106.7812], {icon: odpIcon})
                        .addTo(this.map)
                        .bindPopup('<b>ODP Kebayoran</b><br>Utilization: 72%');
                },
                
                zoomIn() {
                    this.map.zoomIn();
                },
                
                zoomOut() {
                    this.map.zoomOut();
                },
                
                locateMe() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(position => {
                            this.map.setView([position.coords.latitude, position.coords.longitude], 15);
                        });
                    }
                },
                
                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                }
            }
        }
    </script>
</body>
</html>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\gis\index.blade.php ENDPATH**/ ?>