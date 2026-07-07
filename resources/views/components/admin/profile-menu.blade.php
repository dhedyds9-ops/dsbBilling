{{--
/**
 * Profile Menu Component
 */
--}}

@props([
    'user' => null,
])

@php
$userName = data_get($user, 'name', 'User');
$userEmail = data_get($user, 'email', 'user@example.com');
$userAvatar = data_get($user, 'avatar');
$userRole = data_get($user, 'role', 'Admin');
@endphp

<div x-data="{ open: false }" class="relative">
    <button
        @click="open = !open"
        class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
    >
        <x-display.avatar
            :src="$userAvatar"
            :name="$userName"
            size="sm"
            status="online"
        />

        <div class="hidden md:block text-left">
            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $userName }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $userRole }}</p>
        </div>

        <svg class="hidden md:block w-4 h-4 text-slate-400 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Profile Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        @click.stop
        class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-soft-lg overflow-hidden"
        style="display: none;"
    >
        {{-- User Info --}}
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <x-display.avatar :src="$userAvatar" :name="$userName" size="md" />
                <div>
                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ $userName }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $userEmail }}</p>
                </div>
            </div>
        </div>

        {{-- Menu Items --}}
        <div class="py-2">
            <a href="/profile" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>

            <a href="/settings" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 002.572 1.065c.426 1.756 2.924 1.756 3.35 0a1.724 1.724 0 002.573-1.066c1.543.94 3.31-.826 2.37-2.37a1.724 1.724 0 002.572-1.065c.426 1.756 2.924 1.756-3.35 0a1.724 1.724 0 002.573-1.066c-1.543.94-3.31-.826 2.37-2.37a1.724 1.724 0 002.572-1.065c.426 1.756 2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>

            <a href="/activity-log" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Activity Log
            </a>
        </div>

        {{-- Tenant Switcher (if multi-tenant) --}}
        <div class="py-2 border-t border-slate-200 dark:border-slate-700">
            <p class="px-4 py-1 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase">Switch Tenant</p>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <div class="w-4 h-4 rounded border border-primary-500 bg-primary-500/20"></div>
                ISP Utama
                <span class="ml-auto text-xs text-primary-600 dark:text-primary-400">Active</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <div class="w-4 h-4 rounded border border-slate-300 dark:border-slate-600"></div>
                ISP Backup
            </a>
        </div>

        {{-- Logout --}}
        <div class="py-2 border-t border-slate-200 dark:border-slate-700">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
