<?php

namespace App\Jobs\ACS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ACS\ACSDevice;
use App\Models\ACS\Firmware;
use App\Models\ACS\DeviceTask;

class UpgradeFirmwareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $deviceId, public int $firmwareId, public ?int $taskId = null)
    {
    }

    public function handle(): void
    {
        $device = ACSDevice::find($this->deviceId);
        $firmware = Firmware::find($this->firmwareId);
        if (!$device || !$firmware) {
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

        // Actual firmware upgrade via GenieACS would go here

        if ($this->taskId && isset($task)) {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}
