<x-layouts.base
    html-class="h-full dark"
    body-class="noc-root h-screen overflow-hidden flex flex-col"
>
    <x-slot:bodyAttributes>
        style="background:#0a0e1a; color:#e5e7eb; font-family:'Inter',sans-serif;" x-data="nocLayout()" x-init="init()"
    </x-slot:bodyAttributes>
    <x-slot:head>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <style>
            /* NOC-specific overrides — force dark and monospace feel */
            :root {
                --noc-bg: #0a0e1a;
                --noc-panel: #111827;
                --noc-border: #00e5ff; /* NEON CYAN */
                --noc-text: #e5e7eb;
                --noc-muted: #9ca3af;
                --noc-online: #10b981;
                --noc-warning: #f59e0b;
                --noc-critical: #ef4444;
                --noc-info: #3b82f6;
            }
            html.dark, .noc-root {
                background-color: var(--noc-bg) !important;
                color: var(--noc-text) !important;
            }
            /* Utility Classes */
            .noc-panel-bg { background-color: var(--noc-panel) !important; }
            .noc-bg { background-color: var(--noc-bg) !important; }
            .noc-border { 
                border-color: var(--noc-border) !important; 
                box-shadow: 0 0 5px rgba(0, 229, 255, 0.25);
            }
            .noc-text { color: var(--noc-text) !important; }
            .noc-muted { color: var(--noc-muted) !important; }

            /* Form Inputs & Select */
            .noc-input {
                background-color: #0d1326 !important;
                color: var(--noc-text) !important;
                border: 1px solid var(--noc-border) !important;
            }
            .noc-input:focus {
                outline: none !important;
                box-shadow: 0 0 8px rgba(0, 229, 255, 0.6) !important;
            }
            .noc-input option {
                background-color: var(--noc-panel) !important;
                color: var(--noc-text) !important;
            }

            .noc-mono { font-family: 'JetBrains Mono', 'Courier New', monospace; }
            .noc-badge-online  { background: #064e3b; color: #10b981; border: 1px solid #065f46; }
            .noc-badge-warning { background: #451a03; color: #f59e0b; border: 1px solid #78350f; }
            .noc-badge-offline { background: #450a0a; color: #ef4444; border: 1px solid #7f1d1d; }
            .noc-badge-unknown { background: #1f2937; color: #6b7280; border: 1px solid #374151; }
            .noc-badge-los     { background: #4c1d95; color: #a78bfa; border: 1px solid #5b21b6; }
            .noc-pulse { animation: noc-pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }
            @keyframes noc-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
            /* Scrollbars */
            .noc-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
            .noc-scroll::-webkit-scrollbar-track { background: #111827; }
            .noc-scroll::-webkit-scrollbar-thumb { background: #374151; border-radius: 2px; }
        </style>
    </x-slot:head>

    {{-- ============================================================
         NOC TOP BAR
         ============================================================ --}}
    <header class="flex-none h-10 flex items-center px-3 gap-3 border-b" style="background:#111827;border-color:#1f2937;">

        {{-- Logo + Label --}}
        <div class="flex items-center gap-2 flex-none">
            <button @click="navOpen = !navOpen" class="text-gray-400 hover:text-gray-200 transition-colors" title="Navigation">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="text-xs font-semibold tracking-widest text-blue-400 uppercase noc-mono">dsBilling</span>
            <span class="text-gray-600 dark:text-gray-400">|</span>
            <span class="text-xs font-bold tracking-wider text-gray-200 uppercase">NOC</span>
        </div>

        {{-- Nav Links --}}
        <nav class="hidden lg:flex items-center gap-1 flex-1 overflow-x-auto">
            @php
                $nocNav = [];
                if (auth()->check() && auth()->user()->hasRole('administrator')) {
                    $nocNav[] = ['route' => 'dashboard', 'label' => 'Admin Portal', 'icon' => 'arrow-left-circle'];
                }
                $nocNav = array_merge($nocNav, [
                    ['route' => 'noc.overview',         'label' => 'Overview',      'icon' => 'grid'],
                    ['route' => 'noc.routers.index',    'label' => 'Router',        'icon' => 'server'],
                    ['route' => 'noc.olts.index',       'label' => 'OLT',           'icon' => 'server-stack'],
                    ['route' => 'noc.onus.index',       'label' => 'ONU',           'icon' => 'router'],
                    ['route' => 'noc.pppoe.index',      'label' => 'Active Sessions',         'icon' => 'users'],
                    ['route' => 'noc.alarms.index',     'label' => 'Alarms',        'icon' => 'bell'],
                    ['route' => 'noc.provisioning.index','label' => 'Provisioning', 'icon' => 'list-checks'],
                    ['route' => 'noc.topology.index',   'label' => 'Impact Analysis',           'icon' => 'impact'],
                    ['route' => 'gis.map',              'label' => 'Live Mapping',  'icon' => 'map'],
                    ['route' => 'gis.index',            'label' => 'GIS Dashboard', 'icon' => 'globe'],
                ]);
            @endphp
            @foreach($nocNav as $item)
                @if(\Illuminate\Support\Facades\Route::has($item['route']))
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-1 px-2 py-1 rounded text-xs whitespace-nowrap transition-colors
                          {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route'])
                              ? 'bg-blue-900 text-blue-300 font-medium'
                              : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800' }}">
                    <i class="bi bi-{{ $item['icon'] }} text-xs"></i>
                    {{ $item['label'] }}
                </a>
                @endif
            @endforeach
        </nav>

        {{-- Right Controls --}}
        <div class="flex items-center gap-3 flex-none ml-auto">
            {{-- LIVE indicator --}}
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-green-400 noc-pulse" style="display:inline-block;"></span>
                <span class="text-xs text-green-400 font-mono font-semibold">LIVE</span>
            </div>

            {{-- Clock --}}
            <span class="text-xs text-gray-400 noc-mono" x-text="now"></span>

            <!-- Realtime Attendance Check-in -->
            @livewire(\App\Livewire\NOC\AttendanceWidget::class)

            {{-- Refresh --}}
            <button
                @click="$dispatch('refresh-noc')"
                class="text-gray-400 hover:text-gray-200 transition-colors"
                title="Refresh"
            >
                <i class="bi bi-arrow-clockwise text-sm"></i>
            </button>

            {{-- Fullscreen --}}
            <button @click="toggleFullscreen()" class="text-gray-400 hover:text-gray-200 transition-colors" title="Fullscreen">
                <i class="bi bi-fullscreen text-sm"></i>
            </button>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="inline-flex items-center">
                @csrf
                <button type="submit" class="text-gray-500 dark:text-gray-400 hover:text-red-400 transition-colors text-xs flex items-center gap-1" title="Logout">
                    <i class="bi bi-box-arrow-right text-xs"></i>
                    <span class="hidden xl:inline">Logout</span>
                </button>
            </form>
        </div>
    </header>

    {{-- ============================================================
         SLIDE-IN NAVIGATION PANEL (for mobile / compact access)
         ============================================================ --}}
    <div
        x-show="navOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-x-4"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-4"
        class="fixed inset-0 z-50 flex"
        style="display:none;"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60" @click="navOpen = false"></div>

        {{-- Panel --}}
        <nav class="relative w-56 flex flex-col z-10 border-r" style="background:#111827;border-color:#1f2937;">
            <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:#1f2937;">
                <span class="text-xs font-bold text-blue-400 uppercase tracking-widest noc-mono">NOC Navigation</span>
                <button @click="navOpen = false" class="text-gray-500 dark:text-gray-400 hover:text-gray-300">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto py-2">
                @foreach($nocNav ?? [] as $item)
                    @if(\Illuminate\Support\Facades\Route::has($item['route']))
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-2 text-sm transition-colors
                              {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route'])
                                  ? 'bg-blue-900/50 text-blue-300 border-l-2 border-blue-400'
                                  : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800' }}"
                       @click="navOpen = false">
                        <i class="bi bi-{{ $item['icon'] }} text-sm w-4 text-center"></i>
                        {{ $item['label'] }}
                    </a>
                    @endif
                @endforeach

                <div class="border-t mt-2 pt-2" style="border-color:#1f2937;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-red-400 hover:bg-gray-800 transition-colors text-left">
                            <i class="bi bi-box-arrow-right text-sm w-4 text-center"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </div>

    {{-- ============================================================
         MAIN CONTENT AREA
         ============================================================ --}}
    <main class="flex-1 overflow-hidden noc-scroll">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- Toast Notifications --}}
    <x-ui.toast-manager position="top-right" />

    <x-slot:scripts>
        {{-- Flash messages to toast --}}
        <script>
            window.serverFlashes = [
                @if(session('success')) { type: 'success', message: {!! json_encode(session('success')) !!} }, @endif
                @if(session('error'))   { type: 'error',   message: {!! json_encode(session('error')) !!} }, @endif
                @if(session('warning')) { type: 'warning',  message: {!! json_encode(session('warning')) !!} }, @endif
                @if(session('info'))    { type: 'info',     message: {!! json_encode(session('info')) !!} }, @endif
            ];
        </script>
        <script>
            function nocLayout() {
                return {
                    navOpen: false,
                    now: '',

                    init() {
                        this.updateTime();
                        setInterval(() => this.updateTime(), 1000);
                    },

                    updateTime() {
                        const d = new Date();
                        this.now = d.toLocaleTimeString('id-ID', { hour12: false });
                    },

                    toggleFullscreen() {
                        if (!document.fullscreenElement) {
                            document.documentElement.requestFullscreen?.();
                        } else {
                            document.exitFullscreen?.();
                        }
                    },
                };
            }
        </script>
    </x-slot:scripts>
</x-layouts.base>









