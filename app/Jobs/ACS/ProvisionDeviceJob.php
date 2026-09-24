<?php

namespace App\Jobs\ACS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ACS\ProvisionQueue;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;

class ProvisionDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $provisionQueueId)
    {
    }

    public function handle(GenieACSDriver $driver): void
    {
        $queue = ProvisionQueue::with('device', 'template')->find($this->provisionQueueId);
        if (!$queue || !$queue->device) {
            return;
        }

        $queue->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $acsId = $queue->device->uuid ?: $queue->device->serial_number;
            
            // Push tags based on provision template
            $metadata = [];
            if ($queue->template) {
                $metadata['Tags.' . $queue->template->name] = true;
            } else {
                $metadata['Tags.dsBillingProvisioned'] = true;
            }

            $driver->addDevice($acsId, $metadata);

            $queue->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (Exception $e) {
            $queue->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $queue = ProvisionQueue::find($this->provisionQueueId);
        if ($queue && $queue->status !== 'failed') {
            $queue->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
