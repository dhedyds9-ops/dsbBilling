<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-slate-50 dark:bg-slate-900" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || localStorage.getItem('dsb_dark_mode') === '1' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); localStorage.setItem('dsb_dark_mode', val ? '1' : '0'); document.documentElement.classList.toggle('dark', val); })" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function() {
            try {
                var isDark = localStorage.getItem('darkMode') === 'true' || localStorage.getItem('dsb_dark_mode') === '1';
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('darkMode', 'true');
                    localStorage.setItem('dsb_dark_mode', '1');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    <title>@yield('title', $title ?? config('app.name', 'dsBilling Admin'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Round|Material+Icons+Sharp&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-icons/3.0.1/iconfont/material-icons.min.css" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite Pre-compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .apexcharts-canvas, .apexcharts-svg { background: transparent !important; }
        .apexcharts-tooltip { background: transparent !important; }

        /* Fix Material Symbols & Icons always rendering as icons (not text) */
        .material-symbols-outlined,
        .material-icons-outlined,
        .material-icons,
        .material-icons-round,
        .material-icons-sharp {
            font-family: 'Material Symbols Outlined', 'Material Icons Outlined', 'Material Icons' !important;
            font-weight: normal !important;
            font-style: normal !important;
            font-size: 24px;
            line-height: 1 !important;
            letter-spacing: normal !important;
            text-transform: none !important;
            display: inline-block;
            white-space: nowrap !important;
            word-wrap: normal !important;
            direction: ltr !important;
            -webkit-font-feature-settings: 'liga' 1 !important;
            font-feature-settings: 'liga' 1 !important;
            font-variant-ligatures: contextual common-ligatures !important;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .notranslate {
            translate: no !important;
        }

        /* ====================================================
           GLOBAL TABLE SINGLE-LINE (NOWRAP) & HORIZONTAL SCROLL
           Prevents table text from wrapping downwards (multi-line).
           Keeps row heights uniform and text flowing to the right.
           ==================================================== */
        table th,
        table td {
            white-space: nowrap !important;
            vertical-align: middle;
        }

        /* Opt-out escape hatch for specific cells if needed */
        table th.whitespace-normal,
        table td.whitespace-normal,
        table .whitespace-normal,
        table th.break-words,
        table td.break-words {
            white-space: normal !important;
        }

        /* Smooth horizontal scrollbar for tables */
        .overflow-x-auto,
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .overflow-x-auto::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .overflow-x-auto::-webkit-scrollbar-track,
        .table-responsive::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.04);
            border-radius: 4px;
        }
        .dark .overflow-x-auto::-webkit-scrollbar-track,
        .dark .table-responsive::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        .overflow-x-auto::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.5);
            border-radius: 4px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover,
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.8);
        }

        /* ====================================================
           GLOBAL PRINT RESET
           Ensures the dark-mode root background (dark:bg-slate-900
           on <html>) never bleeds into the printed page.
           ==================================================== */
        @media print {
            html, body {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #111827 !important;
            }
        }
    </style>

    <!-- Styles -->
    @livewireStyles
    @stack('styles')
    
    <!-- Leaflet (Loaded on GIS/Map routes or pushed on demand) -->
    @if(request()->routeIs('*gis*', '*map*', '*odp*'))
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endif
</head>

<body
    x-data="{
        sidebarCollapsed: false,
        sidebarMobileOpen: false
    }"
    class="min-h-screen font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-50 dark:bg-slate-900 print:bg-white print:text-black"
>
    <!-- Mobile Sidebar Overlay -->
    <div
        x-show="sidebarMobileOpen"
        @click="sidebarMobileOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm lg:hidden"
        style="display: none;"
    ></div>

    <!-- Sidebar -->
    <div class="print:hidden">
        <x-admin.sidebar />
    </div>

    <!-- Main Content Area -->
    <div
        class="lg:pl-64 print:pl-0 transition-all duration-300 relative flex flex-col min-h-screen min-w-0 max-w-full overflow-x-hidden"
        :class="sidebarCollapsed ? 'lg:pl-20 print:pl-0' : 'lg:pl-64 print:pl-0'"
    >
        <!-- Topbar -->
        <div class="print:hidden">
            <x-admin.topbar :breadcrumbs="$breadcrumbs ?? []" :user="auth()->user()" />
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-4 md:p-6 min-w-0 max-w-full print:p-0 print:m-0">
            @auth
                <livewire:workforce.attendance-prompt />
            @endauth

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    @livewireScripts
    @stack('scripts')

    <!-- Session Keep-Alive & Auto-Refresh Token to Prevent 419 Page Expired -->
    <script>
        (function() {
            function pingSession() {
                fetch('{{ route('session.ping') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data && data.csrf) {
                        var meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.setAttribute('content', data.csrf);
                    }
                })
                .catch(function(err) {
                    // Silent fail if network momentarily disconnected
                });
            }

            // Ping session every 5 minutes while tab is open
            setInterval(pingSession, 5 * 60 * 1000);

            // Ping immediately when user switches back to this tab
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    pingSession();
                }
            });

            // Graceful 419 Handling for Livewire
            document.addEventListener('livewire:init', function() {
                if (window.Livewire && window.Livewire.hook) {
                    window.Livewire.hook('request', function(ctx) {
                        if (ctx && ctx.fail) {
                            ctx.fail(function(payload) {
                                if (payload && payload.status === 419) {
                                    // Refresh token immediately
                                    pingSession();
                                }
                            });
                        }
                    });
                }
            });
        })();
    </script>

    <!-- Protect Material Symbols & Icons from Auto-Translation Ligature Corruption -->
    <script>
        function protectIcons() {
            document.querySelectorAll('.material-symbols-outlined, .material-icons-outlined, .material-icons, .material-icons-round, .material-icons-sharp').forEach(function(el) {
                el.setAttribute('translate', 'no');
                el.classList.add('notranslate');
            });
        }
        document.addEventListener('DOMContentLoaded', protectIcons);
        document.addEventListener('livewire:navigated', protectIcons);
        document.addEventListener('livewire:update', protectIcons);
        if (window.MutationObserver) {
            new MutationObserver(protectIcons).observe(document.documentElement, { childList: true, subtree: true });
        }
    </script>
@include('components.global-sweetalert')
</body>
</html>