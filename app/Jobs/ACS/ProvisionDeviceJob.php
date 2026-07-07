<?php

namespace App\Jobs\ACS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ACS\ProvisionQueue;

class ProvisionDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $provisionQueueId)
    {
    }

    public function handle(): void
    {
        $queue = ProvisionQueue::find($this->provisionQueueId);
        if (!$queue) {
            return;
        }

        $queue->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        // Actual provisioning logic via GenieACS would go here
        sleep(2);

        $queue->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $queue = ProvisionQueue::find($this->provisionQueueId);
        if ($queue) {
            $queue->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
