<div x-data="{ open: false }" @keydown.escape.window="open = false" @open-notifications.window="open = true" @close-notifications.window="open = false" @refresh-notifications.window="$refresh" class="relative">
    <!-- Notification Bell -->
    <button
        @click="open = !open"
        class="relative p-2 rounded-lg hover:bg-slate-100 dark:bg-slate-800 transition-colors"
        title="Notifications"
    >
        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Dropdown -->
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
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-soft-lg overflow-hidden z-50"
        style="display: none;"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Notifications</h3>
            <div class="flex items-center gap-2">
                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="text-xs text-primary-600 hover:text-primary-700">
                        Mark all read
                    </button>
                @endif
            </div>
        </div>

        <div class="max-h-80 overflow-y-auto">
            @if(count($notifications) > 0)
                @foreach($notifications as $notification)
                    <div wire:key="{{ $notification['id'] }}" class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors cursor-pointer {{ !$notification['read'] ? 'bg-primary-50' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $notification['type'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600' : ($notification['type'] === 'success' ? 'bg-green-100 dark:bg-green-900/50 text-green-600' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-600') }}">
                                @if($notification['type'] === 'warning')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                @elseif($notification['type'] === 'success')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                                        {{ $notification['title'] }}
                                    </p>
                                    <span class="text-xs text-slate-400 flex-shrink-0 mt-0.5">
                                        {{ $notification['time'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                    {{ $notification['message'] }}
                                </p>
                            </div>
                        </div>
                        @if(!$notification['read'])
                            <div class="flex items-center justify-end mt-2 gap-2">
                                <button wire:click="markAsRead({{ $notification['id'] }})" class="text-xs text-primary-600 hover:text-primary-700">
                                    Mark as read
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-sm">No notifications</p>
                </div>
            @endif
        </div>

        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <button wire:click="clearAll" class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-300">
                    Clear all
                </button>
                <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    View all notifications
                </a>
            </div>
        </div>
    </div>
</div>
