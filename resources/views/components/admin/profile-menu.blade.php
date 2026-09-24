{{--
/**
 * Profile Menu Component
 */
--}}

@props([
    'user' => null,
])

@php
$userName = $user ? $user->name : 'Administrator';
$userEmail = $user ? $user->email : 'admin@example.com';
$userAvatar = $user ? $user->avatar : null;
$userRole = 'Karyawan';
if ($user) {
    if ($user->hasRole('administrator')) {
        $userRole = 'Administrator';
    } elseif ($user->hasRole('reseller')) {
        $userRole = 'Reseller';
    } elseif ($user->hasRole('customer')) {
        $userRole = 'Pelanggan';
    } elseif ($user->hasRole('manager')) {
        $userRole = $user->job_function ?: 'Manager';
    } else {
        $userRole = $user->job_function ?: 'Karyawan';
    }
}
$userRole = str_replace('_', ' ', $userRole);
$userRole = ucwords(strtolower($userRole));
@endphp

<div x-data="{ open: false }" class="relative">
    <button
        @click="open = !open"
        class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
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

        <svg class="hidden md:block w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        class="absolute right-0 mt-2 w-56 bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-700/50 shadow-soft-lg overflow-hidden"
        style="display: none;"
    >
        {{-- User Info --}}
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700/50">
            <div class="flex items-center gap-3">
                <x-display.avatar :src="$userAvatar" :name="$userName" size="md" />
                <div class="overflow-hidden">
                    <p class="font-medium text-slate-900 dark:text-slate-100 truncate">{{ $userName }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $userEmail }}</p>
                </div>
            </div>
        </div>

        {{-- Menu Items --}}
        <div class="py-1">
            <a href="/profile" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:16px">manage_accounts</span>
                Profil Saya
            </a>

            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:16px">home</span>
                <span class="flex-1">Landing Page</span>
                <span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px">open_in_new</span>
            </a>

        </div>

        {{-- Logout --}}
        <div class="py-1 border-t border-slate-200 dark:border-slate-700/50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/30 dark:hover:bg-red-900/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </div>
</div>
