<?php

namespace App\Listeners\ISP;

use App\Events\ISP\PPPoEUserStatusChangedEvent;
use App\Integration\Notification\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SendPPPoEUserStatusChangedNotification
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(PPPoEUserStatusChangedEvent $event): void
    {
        try {
            $pppoeUser = $event->pppoeUser;
            $customerService = $pppoeUser->customerService;
            $customer = $customerService?->customer;

            if (!$customer || !$customer->phone) {
                Log::warning('Customer or phone not found for PPPoE user: ' . $pppoeUser->username);
                return;
            }

            $message = $this->getMessage($event);

            // Send WhatsApp notification
            // Note: We need to check how WhatsAppDriver is implemented, this is a placeholder
            $this->sendWhatsAppNotification($customer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send PPPoE user status changed notification', ['exception' => $e]);
        }
    }

    protected function getMessage(PPPoEUserStatusChangedEvent $event): string
    {
        $pppoeUser = $event->pppoeUser;
        $newStatus = $event->newStatus;

        $statusText = match ($newStatus) {
            'active' => 'diaktifkan',
            'suspended' => 'disuspensi / diisolir',
            'terminated' => 'dibatasi',
            default => $newStatus,
        };

        return "Halo, status layanan internet Anda (Username: {$pppoeUser->username}) telah {$statusText}.\n\n" .
            "Jika ada pertanyaan, silakan hubungi layanan pelanggan kami.\n\n" .
            "Terima kasih.";
    }

    protected function sendWhatsAppNotification(string $phone, string $message): void
    {
        // Note: Need to adapt based on actual WhatsAppDriver implementation
        // For now, just log as info
        Log::info('WhatsApp notification queued for: ' . $phone, ['message' => $message]);

        // If NotificationService supports direct WhatsApp sending, we can use that
        // $this->notificationService->sendWhatsApp($phone, $message);
    }
}
