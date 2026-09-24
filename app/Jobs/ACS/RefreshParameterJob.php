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
use Illuminate\Support\Facades\Http;
use Exception;

class RefreshParameterJob implements ShouldQueue
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
            
            // Reaching out to driver configuration directly since driver doesn't expose refreshObject explicitly
            $baseUrl = rtrim(config('genieacs.base_url', 'http://localhost:7557'), '/');
            $username = config('genieacs.username', 'admin');
            $password = config('genieacs.password', 'admin');
            
            $payload = ['name' => 'refreshObject', 'objectName' => $this->parameter];
            
            $response = Http::withBasicAuth($username, $password)
                ->timeout(30)
                ->asJson()
                ->post("{$baseUrl}/devices/{$acsId}/tasks", $payload);
                
            if (!$response->successful()) {
                throw new Exception("GenieACS refreshObject error: " . $response->body());
            }

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
