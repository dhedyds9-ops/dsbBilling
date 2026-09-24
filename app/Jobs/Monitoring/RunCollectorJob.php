<?php

namespace App\Jobs\Monitoring;

use App\Services\Monitoring\Contracts\CollectorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunCollectorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private string $collectorClass
    ) {
        $this->onQueue($this->getQueueForCollector($collectorClass));
    }
    
    public function handle(): void
    {
        $collector = app($this->collectorClass);
        
        if ($collector instanceof CollectorInterface) {
            $collector->collect();
        }
    }
    
    private function getQueueForCollector(string $collectorClass): string
    {
        $baseName = class_basename($collectorClass);
        
        return match ($baseName) {
            'RouterCollector' => 'monitoring-router',
            'RadiusCollector' => 'monitoring-radius',
            'GenieAcsCollector' => 'monitoring-genieacs',
            'OltCollector' => 'monitoring-olt',
            'OnuCollector' => 'monitoring-onu',
            'PingCollector' => 'monitoring-ping',
            'SnmpCollector' => 'monitoring-snmp',
            'ServiceCollector' => 'monitoring-service',
            'BillingCollector' => 'monitoring-billing',
            'NotificationCollector' => 'monitoring-notification',
            default => 'monitoring-default',
        };
    }
}
