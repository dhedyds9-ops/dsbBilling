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
        $userId = auth()->id();

        $this->notifications = Cache::remember("notifications:{$userId}", 60, function () {
            return $this->fetchNotifications();
        });

        $this->unreadCount = count(array_filter($this->notifications, fn($n) => !$n['read']));
    }

    protected function fetchNotifications(): array
    {
        // Placeholder - dalam implementasi nyata, fetch dari database
        return [
            [
                'id' => 1,
                'type' => 'warning',
                'title' => 'Server Load High',
                'message' => 'OLT-01 CPU usage at 85%',
                'time' => now()->subMinutes(5)->diffForHumans(),
                'read' => false,
                'url' => '/noc/alerts/1',
            ],
            [
                'id' => 2,
                'type' => 'success',
                'title' => 'Payment Received',
                'message' => 'Payment of Rp 5,000,000 from John Doe',
                'time' => now()->subMinutes(15)->diffForHumans(),
                'read' => false,
                'url' => '/billing/payments/1',
            ],
            [
                'id' => 3,
                'type' => 'info',
                'title' => 'New Ticket',
                'message' => 'Customer reported connection issue',
                'time' => now()->subMinutes(30)->diffForHumans(),
                'read' => true,
                'url' => '/tickets/1',
            ],
        ];
    }

    public function markAsRead(int $notificationId): void
    {
        $userId = auth()->id();

        foreach ($this->notifications as &$notification) {
            if ($notification['id'] === $notificationId) {
                $notification['read'] = true;
                break;
            }
        }

        $this->unreadCount = count(array_filter($this->notifications, fn($n) => !$n['read']));

        Cache::put("notifications:{$userId}", $this->notifications, 3600);
    }

    public function markAllAsRead(): void
    {
        foreach ($this->notifications as &$notification) {
            $notification['read'] = true;
        }

        $this->unreadCount = 0;

        $userId = auth()->id();
        Cache::put("notifications:{$userId}", $this->notifications, 3600);
    }

    public function clearAll(): void
    {
        $this->notifications = [];
        $this->unreadCount = 0;

        $userId = auth()->id();
        Cache::forget("notifications:{$userId}");
    }

    public function render()
    {
        return view('livewire.notification-center');
    }
}
