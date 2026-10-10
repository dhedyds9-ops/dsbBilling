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
            <button wire:click="setActiveTab('overview')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'overview' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">dashboard</span>
                <span class="truncate" x-show="!sidebarCollapsed">Ringkasan Router</span>
            </button>
            
            <button wire:click="setActiveTab('interfaces')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'interfaces' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">settings_ethernet</span>
                <span class="truncate" x-show="!sidebarCollapsed">Interface</span>
            </button>
            
            <button wire:click="setActiveTab('ppp')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'ppp' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">account_tree</span>
                <span class="truncate" x-show="!sidebarCollapsed">PPPoE Active</span>
            </button>
            
            <button wire:click="setActiveTab('hotspot')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'hotspot' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">wifi</span>
                <span class="truncate" x-show="!sidebarCollapsed">Hotspot Active</span>
            </button>
            
            <button wire:click="setActiveTab('logs')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'logs' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">list_alt</span>
                <span class="truncate" x-show="!sidebarCollapsed">Log Router</span>
            </button>

            <button wire:click="setActiveTab('terminal')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $activeTab === 'terminal' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined notranslate text-[20px]" translate="no">terminal</span>
                <span class="truncate" x-show="!sidebarCollapsed">Web Terminal</span>
            </button>

            <!-- Unimplemented menus -->
            @php
                $unimplemented = [
                    ['label' => 'DHCP Server', 'icon' => 'lan'],
                    ['label' => 'Firewall', 'icon' => 'security'],
                    ['label' => 'Routing', 'icon' => 'route'],
                    ['label' => 'Traffic Monitoring', 'icon' => 'monitoring'],
                ];
            @endphp

            @foreach($unimplemented as $item)
                <button type="button" disabled class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-slate-400 dark:text-slate-500 opacity-60 cursor-not-allowed mt-2">
                    <span class="material-symbols-outlined notranslate text-[20px]" translate="no">{{ $item['icon'] }}</span>
                    <span class="truncate" x-show="!sidebarCollapsed">{{ $item['label'] }}</span>
                </button>
            @endforeach
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

