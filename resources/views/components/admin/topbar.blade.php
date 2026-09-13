{{--
/**
 * Admin Topbar Component (Material 3 inspired)
 *
 * Area:
 * ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ Mobile menu toggle
 * ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ Breadcrumbs
 * ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ (spacer)
 * ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ Global Search trigger (ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬â„¢Ãƒâ€¹Ã…â€œK)
 * ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ Notification bell (Livewire)
 * ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ Theme toggle
 * ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ Profile menu
 */
--}}

@props([
    'breadcrumbs' => [],
    'user' => null,
])

<header
    class="sticky top-0 z-30 h-16 shrink-0
           bg-white/90 dark:bg-[#111c36]/90 backdrop-blur-xl supports-[backdrop-filter]:bg-white/75 dark:bg-[#111c36]/75
           border-b border-slate-200 dark:border-slate-700/50"
>
    <div class="h-full px-4 sm:px-6 flex items-center gap-3 sm:gap-4">
        {{-- Menu Toggles --}}
        <!-- Mobile Toggle -->
        <button
            type="button"
            @click="sidebarMobileOpen = true"
            class="lg:hidden p-2 rounded-xl
                   text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100
                   hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
            aria-label="Buka menu"
        >
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Desktop Toggle -->
        <button
            type="button"
            @click="sidebarCollapsed = !sidebarCollapsed"
            class="hidden lg:flex p-2 rounded-xl
                   text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100
                   hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
            aria-label="Toggle menu"
        >
            <span class="material-symbols-outlined transition-transform duration-300" :class="sidebarCollapsed ? '' : ''">menu</span>
        </button>

        {{-- Page Title --}}
        @hasSection('page_title')
            <div class="hidden sm:flex items-center gap-2 font-bold text-slate-800 dark:text-slate-100">
                @yield('page_title')
            </div>
        @endif

        {{-- Breadcrumbs --}}
        <x-admin.breadcrumbs :breadcrumbs="$breadcrumbs" />

        {{-- Spacer --}}
        <div class="flex-1 min-w-0"></div>

        {{-- Right cluster --}}
        <div class="flex items-center gap-1 sm:gap-2">
            {{-- Global Search Component --}}
            <div class="hidden md:block">
                @livewire('global-search')
            </div>

            {{-- Notifications --}}
            @if (class_exists(\App\Livewire\NotificationCenter::class))
            <livewire:notification-center />
            @endif

            {{-- Theme toggle --}}
            <button
                type="button"
                @click="darkMode = !darkMode"
                class="relative p-2 rounded-xl
                       text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100
                       hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
                :title="darkMode ? 'Mode Terang' : 'Mode Gelap'"
                aria-label="Toggle theme"
            >
                <span
                    x-show="!darkMode"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -rotate-45 scale-75"
                    x-transition:enter-end="opacity-100 rotate-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 rotate-0 scale-100"
                    x-transition:leave-end="opacity-0 rotate-45 scale-75"
                    class="material-symbols-outlined fill ms-22"
                    style="display: none;"
                >dark_mode</span>
                <span
                    x-show="darkMode"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 rotate-45 scale-75"
                    x-transition:enter-end="opacity-100 rotate-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 rotate-0 scale-100"
                    x-transition:leave-end="opacity-0 -rotate-45 scale-75"
                    class="material-symbols-outlined fill ms-22"
                    style="display: none;"
                >light_mode</span>
            </button>

            {{-- Profile Menu --}}
            <x-admin.profile-menu :user="$user" />
        </div>
    </div>
</header>
