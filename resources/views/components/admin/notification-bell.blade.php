{{--
/**
 * Notification Bell Component
 */
--}}

@props([
    'count' => 0,
])

<div x-data="{ open: false }" class="relative">
    <button
        @click="open = !open"
        class="relative p-2 rounded-lg hover:bg-slate-100 transition-colors"
    >
        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        @if($count > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-danger-500 rounded-full">
                {{ $count > 9 ? '9+' : $count }}
            </span>
        @endif
    </button>

    {{-- Notification Dropdown --}}
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
        class="absolute right-0 mt-2 w-80 bg-white rounded-xl border border-slate-200 shadow-soft-lg overflow-hidden"
        style="display: none;"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
            <h3 class="font-semibold text-slate-900">Notifications</h3>
            <button class="text-xs text-primary-600 hover:text-primary-700">Mark all read</button>
        </div>

        <div class="max-h-80 overflow-y-auto">
            <div class="px-4 py-6 text-center text-slate-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm">No new notifications</p>
            </div>
        </div>

        <div class="px-4 py-3 border-t border-slate-200">
            <a href="#" class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                View all notifications
            </a>
        </div>
    </div>
</div>
