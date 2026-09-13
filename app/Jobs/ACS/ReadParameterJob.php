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

class ReadParameterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $deviceId, public string $parameter, public ?int $taskId = null)
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
            $params = $driver->getDeviceParameters($acsId);

            // Extract the value (usually deeply nested or flat depending on GenieACS API version)
            // The driver handles this gracefully if we traverse the JSON
            $value = null;
            if (isset($params[$this->parameter])) {
                $value = is_array($params[$this->parameter]) ? ($params[$this->parameter]['_value'] ?? null) : $params[$this->parameter];
            } else {
                $parts = explode('.', $this->parameter);
                $node = $params;
                foreach ($parts as $p) {
                    if (!is_array($node) || !array_key_exists($p, $node)) {
                        $node = null;
                        break;
                    }
                    $node = $node[$p];
                }
                if (is_array($node) && isset($node['_value'])) {
                    $value = $node['_value'];
                } elseif (is_scalar($node)) {
                    $value = $node;
                }
            }

            if ($this->taskId && isset($task)) {
                $task->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'result' => ['parameter' => $this->parameter, 'value' => $value],
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
