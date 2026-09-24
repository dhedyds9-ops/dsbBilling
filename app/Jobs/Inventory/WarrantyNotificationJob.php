<?php

namespace App\Jobs\Inventory;

use App\Models\Inventory\AssetWarranty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class WarrantyNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $warrantyId,
        public string $notificationType // 'expiring_soon', 'expired', 'warranty_claim'
    ) {}

    public function handle(): void
    {
        $warranty = AssetWarranty::with(['asset', 'vendor'])->find($this->warrantyId);
        
        if (!$warranty) {
            Log::warning('WarrantyNotificationJob: Warranty not found', [
                'warranty_id' => $this->warrantyId
            ]);
            return;
        }

        $asset = $warranty->asset;
        $recipients = $this->getRecipients();

        foreach ($recipients as $recipient) {
            try {
                $this->sendNotification($recipient, $warranty, $asset);
            } catch (\Exception $e) {
                Log::error('Failed to send warranty notification', [
                    'warranty_id' => $this->warrantyId,
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function getRecipients(): array
    {
        // Get relevant recipients: asset owner, inventory manager, etc.
        return [];
    }

    private function sendNotification($recipient, AssetWarranty $warranty, $asset): void
    {
        $notificationData = [
            'warranty_id' => $warranty->id,
            'asset_code' => $asset->code ?? 'N/A',
            'asset_name' => $asset->name ?? 'Unknown',
            'vendor_name' => $warranty->vendor?->name ?? 'Unknown',
            'expires_at' => $warranty->expires_at?->format('Y-m-d'),
            'remaining_days' => $warranty->getRemainingDays(),
            'notification_type' => $this->notificationType
        ];

        switch ($this->notificationType) {
            case 'expiring_soon':
                $this->sendExpiringSoonNotification($recipient, $notificationData);
                break;
            case 'expired':
                $this->sendExpiredNotification($recipient, $notificationData);
                break;
            case 'warranty_claim':
                $this->sendClaimNotification($recipient, $notificationData);
                break;
        }
    }

    private function sendExpiringSoonNotification($recipient, array $data): void
    {
        // Send email/notification that warranty is expiring soon
        Log::info('Warranty expiring soon notification sent', $data);
    }

    private function sendExpiredNotification($recipient, array $data): void
    {
        // Send email/notification that warranty has expired
        Log::info('Warranty expired notification sent', $data);
    }

    private function sendClaimNotification($recipient, array $data): void
    {
        // Send notification about warranty claim status
        Log::info('Warranty claim notification sent', $data);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('WarrantyNotificationJob failed', [
            'warranty_id' => $this->warrantyId,
            'notification_type' => $this->notificationType,
            'error' => $exception->getMessage()
        ]);
    }
}
