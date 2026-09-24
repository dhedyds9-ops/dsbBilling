<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class NotificationCenter extends Component
{
    public bool $isOpen = false;
    public array $notifications = [];
    public int $unreadCount = 0;

    protected $listeners = ['openNotifications' => 'open', 'closeNotifications' => 'close', 'refreshNotifications' => 'refresh'];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function refresh(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $this->notifications = $this->fetchNotifications();
        $this->unreadCount = count(array_filter($this->notifications, fn($n) => !$n['read']));
    }

    protected function fetchNotifications(): array
    {
        if (!auth()->check()) {
            return [];
        }

        return \App\Models\Notification\Notification::where('recipient_id', auth()->id())
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($notif) {
                $data = is_array($notif->data) ? $notif->data : [];
                return [
                    'id' => $notif->id, // it's integer here
                    'type' => $notif->type ?? 'in_app',
                    'title' => $notif->title ?? 'Notifikasi',
                    'message' => $notif->message ?? '',
                    'url' => $data['url'] ?? '#',
                    'icon' => $data['icon'] ?? 'notifications',
                    'color' => $data['color'] ?? 'blue',
                    'read' => $notif->status === 'read',
                    'time' => $notif->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    public function markAsRead(int $notificationId): void
    {
        $userId = auth()->id();
        
        $notif = \App\Models\Notification\Notification::where('recipient_id', $userId)->find($notificationId);
        if ($notif) {
            $notif->update(['status' => 'read']);
        }

        foreach ($this->notifications as &$notification) {
            if ($notification['id'] === $notificationId) {
                $notification['read'] = true;
                break;
            }
        }

        $this->unreadCount = count(array_filter($this->notifications, fn($n) => !$n['read']));
    }

    public function markAllAsRead(): void
    {
        $userId = auth()->id();
        \App\Models\Notification\Notification::where('recipient_id', $userId)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        foreach ($this->notifications as &$notification) {
            $notification['read'] = true;
        }

        $this->unreadCount = 0;
    }

    public function clearAll(): void
    {
        $userId = auth()->id();
        \App\Models\Notification\Notification::where('recipient_id', $userId)->delete();

        $this->notifications = [];
        $this->unreadCount = 0;
    }

    public function render()
    {
        return view('livewire.notification-center');
    }
}
