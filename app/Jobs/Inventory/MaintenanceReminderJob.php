<?php

namespace App\Jobs\Inventory;

use App\Models\Inventory\AssetMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MaintenanceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $maintenanceId,
        public string $reminderType // 'upcoming', 'overdue', 'completed'
    ) {}

    public function handle(): void
    {
        $maintenance = AssetMaintenance::with(['asset', 'technician'])->find($this->maintenanceId);

        if (!$maintenance) {
            Log::warning('MaintenanceReminderJob: Maintenance not found', [
                'maintenance_id' => $this->maintenanceId
            ]);
            return;
        }

        $asset = $maintenance->asset;
        $recipients = $this->getRecipients($maintenance);

        foreach ($recipients as $recipient) {
            try {
                $this->sendReminder($recipient, $maintenance, $asset);
            } catch (\Exception $e) {
                Log::error('Failed to send maintenance reminder', [
                    'maintenance_id' => $this->maintenanceId,
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function getRecipients(AssetMaintenance $maintenance): array
    {
        $recipients = [];

        // Asset manager
        // if ($asset->manager_id) {
        //     $recipients[] = User::find($asset->manager_id);
        // }

        // Assigned technician
        if ($maintenance->technician_id) {
            $recipients[] = $maintenance->technician;
        }

        return array_filter($recipients);
    }

    private function sendReminder($recipient, AssetMaintenance $maintenance, $asset): void
    {
        $reminderData = [
            'maintenance_id' => $maintenance->id,
            'asset_code' => $asset->code ?? 'N/A',
            'asset_name' => $asset->name ?? 'Unknown',
            'maintenance_type' => $maintenance->maintenance_type,
            'scheduled_date' => $maintenance->scheduled_date?->format('Y-m-d H:i'),
            'technician_name' => $maintenance->technician?->name ?? 'Unassigned',
            'reminder_type' => $this->reminderType
        ];

        switch ($this->reminderType) {
            case 'upcoming':
                $this->sendUpcomingReminder($recipient, $reminderData);
                break;
            case 'overdue':
                $this->sendOverdueReminder($recipient, $reminderData);
                break;
            case 'completed':
                $this->sendCompletedReminder($recipient, $reminderData);
                break;
        }
    }

    private function sendUpcomingReminder($recipient, array $data): void
    {
        Log::info('Maintenance upcoming reminder sent', $data);
    }

    private function sendOverdueReminder($recipient, array $data): void
    {
        Log::info('Maintenance overdue reminder sent', $data);
    }

    private function sendCompletedReminder($recipient, array $data): void
    {
        Log::info('Maintenance completed reminder sent', $data);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('MaintenanceReminderJob failed', [
            'maintenance_id' => $this->maintenanceId,
            'reminder_type' => $this->reminderType,
            'error' => $exception->getMessage()
        ]);
    }
}
