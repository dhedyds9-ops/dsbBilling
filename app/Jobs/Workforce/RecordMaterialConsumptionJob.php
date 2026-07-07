<?php

namespace App\Jobs\Workforce;

use App\Services\Workforce\MaterialConsumptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RecordMaterialConsumptionJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $taskId,
        public readonly string $inventoryItemId,
        public readonly int $quantity,
        public readonly string $notes = '',
    ) {}

    public function handle(MaterialConsumptionService $service): void {
        $service->recordMaterialConsumption(
            Uuid::fromString($this->taskId),
            Uuid::fromString($this->inventoryItemId),
            $this->quantity,
            $this->notes,
        );
    }
}
