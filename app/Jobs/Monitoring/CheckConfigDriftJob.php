<?php

namespace App\Jobs\Monitoring;

use App\Models\ISP\Router;
use App\Services\Adapters\Monitoring\MikroTikDriver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckConfigDriftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Router $router
    ) {}

    public function handle(MikroTikDriver $driver)
    {
        // Don't check if it's disabled or inactive
        if ($this->router->status === Router::STATUS_DISABLED || !$this->router->is_active) {
            return;
        }

        try {
            $isDrifted = $driver->checkConfigDrift($this->router);
            
            // Only update if changed to avoid unnecessary queries
            if ($this->router->has_config_drift !== $isDrifted) {
                $this->router->update([
                    'has_config_drift' => $isDrifted
                ]);
            }
        } catch (\Exception $e) {
            // Log error or ignore
        }
    }
}
