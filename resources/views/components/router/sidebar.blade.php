@props(['activeTab' => 'overview'])
@php
    $routerId = request()->route('id') ?? request()->route('router');
    $currentRoute = request()->route()->getName();
@endphp

<aside
    x-bind:class="[
        sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-20' : 'w-64',
        'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-[#111c36] border-r border-slate-200 dark:border-slate-700/50 transition-all duration-300 shadow-sm'
    ]"
>
    <!-- Logo & Brand -->
    <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-700/50 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full">
            <x-application-logo class="h-10 w-auto" />
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-2">
        <div>
            <div class="px-3 mb-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-show="!sidebarCollapsed">
                Menu Router
            </div>

            <!-- Dashboard/Ringkasan -->
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'overview']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'overview' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">dashboard</span>
                <span class="truncate" x-show="!sidebarCollapsed">Ringkasan Router</span>
            </a>
            
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'interfaces']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'interfaces' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">settings_ethernet</span>
                <span class="truncate" x-show="!sidebarCollapsed">Interface</span>
            </a>
            
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'ppp']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'ppp' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">account_tree</span>
                <span class="truncate" x-show="!sidebarCollapsed">PPP</span>
            </a>
            
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'hotspot']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'hotspot' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">wifi</span>
                <span class="truncate" x-show="!sidebarCollapsed">Hotspot</span>
            </a>
            
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'logs']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'logs' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">list_alt</span>
                <span class="truncate" x-show="!sidebarCollapsed">Log Router</span>
            </a>

            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'terminal']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'terminal' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">terminal</span>
                <span class="truncate" x-show="!sidebarCollapsed">Web Terminal</span>
            </a>

            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'dhcp']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'dhcp' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">lan</span>
                <span class="truncate" x-show="!sidebarCollapsed">DHCP Server</span>
            </a>
            
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'firewall']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'firewall' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">security</span>
                <span class="truncate" x-show="!sidebarCollapsed">Firewall</span>
            </a>
            
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'routing']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'routing' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">route</span>
                <span class="truncate" x-show="!sidebarCollapsed">Routing</span>
            </a>
            
            <a href="{{ route('isp.routers.show', ['id' => $routerId, 'tab' => 'traffic']) }}" wire:navigate class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'traffic' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">monitoring</span>
                <span class="truncate" x-show="!sidebarCollapsed">Traffic Monitoring</span>
            </a>
        </div>

        <div class="pt-6">
            <div class="px-3 mb-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-show="!sidebarCollapsed">
                Navigasi
            </div>
            
            <a href="{{ route('isp.routers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">arrow_back</span>
                <span class="truncate" x-show="!sidebarCollapsed">Kembali ke Daftar</span>
            </a>
        </div>
    </nav>
</aside>


