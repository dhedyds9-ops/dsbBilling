<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-slate-50 dark:bg-slate-900" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $title ?? config('app.name', 'dsBilling Admin'))</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CDN Config for Light Theme -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = { darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .apexcharts-canvas, .apexcharts-svg { background: transparent !important; }
        .apexcharts-tooltip { background: transparent !important; }

        /* Fix Material Symbols icons always rendering as icons (not text) */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none !important;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr !important;
            -webkit-font-feature-settings: 'liga';
            font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
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
        class="lg:pl-64 print:pl-0 transition-all duration-300 relative flex flex-col min-h-screen"
        :class="sidebarCollapsed ? 'lg:pl-20 print:pl-0' : 'lg:pl-64 print:pl-0'"
    >
        <!-- Topbar -->
        <div class="print:hidden">
            <x-admin.topbar :breadcrumbs="$breadcrumbs ?? []" :user="auth()->user()" />
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-6 print:p-0 print:m-0">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    @livewireScripts
    @stack('scripts')

    <!-- Protect Material Symbols from Google Translate -->
    <script>
        function protectIcons() {
            document.querySelectorAll('.material-symbols-outlined').forEach(function(el) {
                el.setAttribute('translate', 'no');
                el.classList.add('notranslate');
            });
        }
        // Run on DOM ready
        document.addEventListener('DOMContentLoaded', protectIcons);
        // Run after Livewire updates
        document.addEventListener('livewire:update', protectIcons);
        // Watch for dynamic DOM changes (Google Translate itself)
        new MutationObserver(protectIcons).observe(document.body, { childList: true, subtree: true });
    </script>
@include('components.global-sweetalert')
</body>
</html>