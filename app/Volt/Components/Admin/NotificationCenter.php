<?php

namespace App\Volt\Components\Admin;

use Livewire\Volt\Component;

new class extends Component {
    public bool $open = false;
    public array $notifications = [];
    public string $filter = 'all';

    public function mount()
    {
        $this->notifications = $this->getNotifications();

        $this->listeners = [
            'open-notification-center' => 'open',
            'close-notification-center' => 'close',
        ];
    }

    public function open()
    {
        $this->open = true;
    }

    public function close()
    {
        $this->open = false;
    }

    public function toggle()
    {
        $this->open = !$this->open;
    }

    public function markAsRead(int $id)
    {
        foreach ($this->notifications as &$notification) {
            if ($notification['id'] === $id) {
                $notification['read'] = true;
                break;
            }
        }
    }

    public function markAllAsRead()
    {
        foreach ($this->notifications as &$notification) {
            $notification['read'] = true;
        }
    }

    public function getUnreadCount(): int
    {
        return count(array_filter($this->notifications, fn($n) => !$n['read']));
    }

    public function getFilteredNotifications(): array
    {
        if ($this->filter === 'all') {
            return $this->notifications;
        }

        return array_filter($this->notifications, fn($n) => $n['type'] === $this->filter);
    }

    public function getNotifications(): array
    {
        return [
            [
                'id' => 1,
                'type' => 'alert',
                'title' => 'Network Alert',
                'message' => 'OLT-01 CPU usage exceeded 90%',
                'time' => '5 minutes ago',
                'read' => false,
                'icon' => 'exclamation-triangle',
                'iconColor' => 'danger',
            ],
            [
                'id' => 2,
                'type' => 'ticket',
                'title' => 'New Ticket',
                'message' => 'Customer #1234 submitted a new ticket',
                'time' => '15 minutes ago',
                'read' => false,
                'icon' => 'ticket',
                'iconColor' => 'info',
            ],
            [
                'id' => 3,
                'type' => 'billing',
                'title' => 'Payment Received',
                'message' => 'Payment of Rp 5,000,000 received',
                'time' => '1 hour ago',
                'read' => true,
                'icon' => 'currency-dollar',
                'iconColor' => 'success',
            ],
            [
                'id' => 4,
                'type' => 'system',
                'title' => 'System Update',
                'message' => 'Scheduled maintenance tonight at 2 AM',
                'time' => '2 hours ago',
                'read' => true,
                'icon' => 'information-circle',
                'iconColor' => 'primary',
            ],
        ];
    }

    public function getTimeAgoAttribute()
    {
        return $this->time;
    }
};

?>

<div
    x-data="{
        open: @entangle('open'),
        notifications: @entangle('notifications'),
        filter: @entangle('filter'),
    }"
    x-init="$watch('open', value => {
        if (value) {
            $dispatch('close-notification-center-other');
        }
    });"
    @open-notification-center.window="open = true"
    @close-notification-center.window="open = false"
    @close-notification-center-other.window="open = false"
    class="relative"
>
    {{-- Bell Button --}}
    <button
        @click="open = !open"
        class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
    >
        <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        @if($this->getUnreadCount() > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-danger-500 rounded-full">
                {{ $this->getUnreadCount() > 9 ? '9+' : $this->getUnreadCount() }}
            </span>
        @endif
    </button>

    {{-- Notification Panel --}}
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
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-soft-lg overflow-hidden"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-semibold text-slate-900 dark:text-white">Notifications</h3>
            @if($this->getUnreadCount() > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-primary-600 hover:text-primary-700 font-medium"
                >
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Filter Tabs --}}
        <div class="flex border-b border-slate-200 dark:border-slate-800">
            <button
                wire:click="$set('filter', 'all')"
                :class="filter === 'all' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="flex-1 px-4 py-2 text-sm font-medium border-b-2 transition-colors"
            >
                All
            </button>
            <button
                wire:click="$set('filter', 'alert')"
                :class="filter === 'alert' ? 'border-danger-600 text-danger-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="flex-1 px-4 py-2 text-sm font-medium border-b-2 transition-colors"
            >
                Alerts
            </button>
            <button
                wire:click="$set('filter', 'ticket')"
                :class="filter === 'ticket' ? 'border-info-600 text-info-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="flex-1 px-4 py-2 text-sm font-medium border-b-2 transition-colors"
            >
                Tickets
            </button>
        </div>

        {{-- Notifications List --}}
        <div class="max-h-80 overflow-y-auto">
            @forelse($this->getFilteredNotifications() as $notification)
                <div
                    wire:click="markAsRead({{ $notification['id'] }})"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors {{ !$notification['read'] ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}"
                >
                    <div class="flex-shrink-0 mt-0.5">
                        @if($notification['iconColor'] === 'danger')
                            <div class="w-8 h-8 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
                                <x-icon :name="$notification['icon']" class="w-4 h-4 text-danger-600" />
                            </div>
                        @elseif($notification['iconColor'] === 'success')
                            <div class="w-8 h-8 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                                <x-icon :name="$notification['icon']" class="w-4 h-4 text-success-600" />
                            </div>
                        @elseif($notification['iconColor'] === 'info')
                            <div class="w-8 h-8 rounded-full bg-info-100 dark:bg-info-900/30 flex items-center justify-center">
                                <x-icon :name="$notification['icon']" class="w-4 h-4 text-info-600" />
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                <x-icon :name="$notification['icon']" class="w-4 h-4 text-primary-600" />
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $notification['title'] }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{{ $notification['message'] }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notification['time'] }}</p>
                    </div>

                    @if(!$notification['read'])
                        <div class="flex-shrink-0">
                            <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-slate-500 dark:text-slate-400">No notifications</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
            <a href="/notifications" class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                View all notifications
            </a>
        </div>
    </div>
</div>
