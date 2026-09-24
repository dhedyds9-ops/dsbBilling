<?php

namespace App\Jobs\Workforce;

use App\Services\Workforce\MaintenanceTaskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CreateMaintenanceTaskJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $workOrderId,
        public readonly string $assignmentId,
        public readonly string $customerId,
        public readonly string $serviceId,
        public readonly ?string $notes = null,
    ) {}

    public function handle(MaintenanceTaskService $service): void {
        $service->createMaintenanceTask(
            Uuid::fromString($this->workOrderId),
            Uuid::fromString($this->assignmentId),
            Uuid::fromString($this->customerId),
            Uuid::fromString($this->serviceId),
            $this->notes,
        );
    }
}
