{{--
/**
 * Admin Topbar Component
 *
 * Contains:
 * - Mobile menu toggle
 * - Breadcrumbs
 * - Global search
 * - Notifications
 * - Messages
 * - Profile menu
 * - Theme toggle
 */
--}}

@props([
    'breadcrumbs' => [],
    'notifications' => [],
    'user' => null,
])

<header class="sticky top-0 z-30 h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
    <div class="flex items-center h-full px-4 gap-4">
        {{-- Mobile Menu Toggle --}}
        <button
            @click="sidebarMobileOpen = true"
            class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        >
            <svg class="w-6 h-6 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Breadcrumbs --}}
        <x-admin.breadcrumbs :breadcrumbs="$breadcrumbs" />

        <div class="flex-1"></div>

        {{-- Right Side Actions --}}
        <div class="flex items-center gap-2">
            {{-- Quick Search (Mobile) --}}
            <button
                class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
            >
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>

            {{-- Global Search (Desktop) --}}
            <button
                class="hidden lg:flex items-center gap-3 px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
            >
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="text-sm text-slate-500 dark:text-slate-300">Search...</span>
                <kbd class="hidden lg:flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">
                    <span>⌘</span><span>K</span>
                </kbd>
            </button>

            {{-- Notifications --}}
            <livewire:notification-center />

            {{-- Theme Toggle --}}
            <button
                @click="darkMode = !darkMode"
                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                title="Toggle theme"
            >
                {{-- Sun Icon (for dark mode) --}}
                <svg x-show="!darkMode"
                     class="w-5 h-5 text-slate-600 dark:text-slate-300"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>

                {{-- Moon Icon (for light mode) --}}
                <svg x-show="darkMode"
                     class="w-5 h-5 text-slate-600 dark:text-slate-300"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- Profile Menu --}}
            <x-admin.profile-menu :user="$user" />
        </div>
    </div>
</header>
