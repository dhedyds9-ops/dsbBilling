<?php

declare(strict_types=1);

namespace App\Listeners\FieldService;

use App\Events\FieldService\InstallationOperationalEvent;
use App\Events\FieldService\MaintenanceOperationalEvent;
use App\Events\FieldService\TicketOperationalEvent;
use App\Events\FieldService\WorkOrderOperationalEvent;
use App\Services\Notifications\FieldServiceNotificationService;
use Illuminate\Support\Facades\Log;

class FieldServiceNotificationListener
{
    public function __construct(
        protected FieldServiceNotificationService $service
    ) {}

    public function handle(object $event): void
    {
        try {
            if ($event instanceof TicketOperationalEvent) {
                $this->service->handleTicketEvent($event);
            } elseif ($event instanceof WorkOrderOperationalEvent) {
                $this->service->handleWorkOrderEvent($event);
            } elseif ($event instanceof InstallationOperationalEvent) {
                $this->service->handleInstallationEvent($event);
            } elseif ($event instanceof MaintenanceOperationalEvent) {
                $this->service->handleMaintenanceEvent($event);
            }
        } catch (\Throwable $e) {
            Log::warning('[FieldServiceNotificationListener] Error: ' . $e->getMessage(), [
                'event_class' => get_class($event),
                'exception' => $e,
            ]);
        }
    }
}
