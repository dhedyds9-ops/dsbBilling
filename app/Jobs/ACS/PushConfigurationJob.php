<?php

namespace App\Jobs\ACS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;

class PushConfigurationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $deviceId, public array $config, public ?int $taskId = null)
    {
    }

    public function handle(GenieACSDriver $driver): void
    {
        $device = ACSDevice::find($this->deviceId);
        if (!$device) {
            return;
        }

        if ($this->taskId) {
            $task = DeviceTask::find($this->taskId);
            if ($task) {
                $task->update([
                    'status' => 'running',
                    'started_at' => now(),
                ]);
            }
        }

        try {
            $acsId = $device->uuid ?: $device->serial_number;
            $driver->setParameterValues($acsId, $this->config);

            if ($this->taskId && isset($task)) {
                $task->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        } catch (Exception $e) {
            if ($this->taskId && isset($task)) {
                $task->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            }
            throw $e;
        }
    }
}
