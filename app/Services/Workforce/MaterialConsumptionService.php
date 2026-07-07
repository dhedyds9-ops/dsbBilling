<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\MaterialConsumptionRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Events\MaterialConsumedEvent;
use Src\Domain\Workforce\MaterialConsumption;

readonly class MaterialConsumptionService {
    public function __construct(
        private MaterialConsumptionRepository $consumptionRepository,
    ) {}

    public function recordMaterialConsumption(
        Uuid $taskId,
        Uuid $inventoryItemId,
        int $quantity,
        string $notes = '',
    ): MaterialConsumption {
        $consumption = MaterialConsumption::create(
            $taskId,
            $inventoryItemId,
            $quantity,
            $notes,
        );
        $this->consumptionRepository->save($consumption);

        $event = MaterialConsumedEvent::create(
            $consumption->id,
            $consumption->taskId,
            $consumption->inventoryItemId,
            $consumption->quantity,
        );
        Event::dispatch($event);

        return $consumption;
    }

    public function updateConsumptionQuantity(Uuid $consumptionId, int $quantity): MaterialConsumption {
        $consumption = $this->consumptionRepository->findById($consumptionId);
        if (!$consumption) throw new \InvalidArgumentException("Consumption not found");
        $consumption->updateQuantity($quantity);
        return $this->consumptionRepository->save($consumption);
    }

    public function updateConsumptionNotes(Uuid $consumptionId, string $notes): MaterialConsumption {
        $consumption = $this->consumptionRepository->findById($consumptionId);
        if (!$consumption) throw new \InvalidArgumentException("Consumption not found");
        $consumption->updateNotes($notes);
        return $this->consumptionRepository->save($consumption);
    }

    public function deleteConsumption(Uuid $consumptionId): void {
        $this->consumptionRepository->delete($consumptionId);
    }

    public function findConsumptionsByTaskId(Uuid $taskId): array {
        return $this->consumptionRepository->findByTaskId($taskId);
    }
}
