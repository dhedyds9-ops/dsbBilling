{{--
Enterprise Layout - Single Layout for All Admin Pages
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $title ?? config('app.name', 'WiFinan Admin'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body
    x-data="{
        sidebarCollapsed: false,
        sidebarMobileOpen: false,
        darkMode: false
    }"
    x-init="
        darkMode = localStorage.getItem('darkMode') === 'true' ||
                   (window.matchMedia('(prefers-color-scheme: dark)').matches && !localStorage.getItem('darkMode'));
        if (darkMode) document.documentElement.classList.add('dark');
        $watch('darkMode', function (value) {
            if (value) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
            }
        });
    "
    :class="darkMode ? 'dark' : ''"
    class="h-full bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 antialiased"
>
    <!-- Mobile Sidebar Overlay -->
    <div
        x-show="sidebarMobileOpen"
        @click="sidebarMobileOpen = false"
        class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
        style="display: none;"
    ></div>

    <!-- Sidebar -->
    <x-admin.sidebar />

    <!-- Main Content Area -->
    <div
        class="lg:pl-72 transition-all duration-300"
        :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'"
    >
        <!-- Topbar -->
        <x-admin.topbar :breadcrumbs="$breadcrumbs ?? []" :user="auth()->user()" />

        <!-- Page Content -->
        <main class="min-h-[calc(100vh-4rem)] p-6">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-4 px-6 text-sm text-slate-500 dark:text-slate-400">
            © 2024 WiFinan. All rights reserved.
        </footer>
    </div>

    <!-- Scripts -->
    @livewireScripts
    @stack('scripts')
</body>
</html>
